<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class SimpleTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $factory = new \Phlexy\LexerFactory\Stateless\Simple;
        return $factory->createLexer($regexToTokenMap);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}