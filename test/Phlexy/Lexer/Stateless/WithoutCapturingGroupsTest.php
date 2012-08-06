<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class WithoutCapturingGroupsTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $factory = new \Phlexy\LexerFactory\Stateless\WithoutCapturingGroups(
            new \Phlexy\LexerDataGenerator
        );

        return $factory->createLexer($regexToTokenMap);
    }

    public function provideTestLexing() {
        return $this->getTestsWithoutCapturingGroups();
    }
}