<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class WithCapturingGroupsTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexerFactory() {
        return new \Phlexy\LexerFactory\Stateless\WithCapturingGroups(
            new \Phlexy\LexerDataGenerator
        );
    }

    public function provideTestLexing() {
        return $this->getTestsWithCapturingGroups();
    }
}