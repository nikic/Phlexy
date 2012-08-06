<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class WithoutCapturingGroupsTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexerFactory() {
        return new \Phlexy\LexerFactory\Stateless\WithoutCapturingGroups(
            new \Phlexy\LexerDataGenerator
        );
    }

    public function provideTestLexing() {
        return $this->getTestsWithoutCapturingGroups();
    }
}