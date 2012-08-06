<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class UsingPregReplaceTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $factory = new \Phlexy\LexerFactory\Stateless\UsingPregReplace(
            new \Phlexy\LexerDataGenerator
        );

        return $factory->createLexer($regexToTokenMap);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}