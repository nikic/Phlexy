<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class SimpleTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexerFactory() {
        return new \Phlexy\LexerFactory\Stateless\Simple;
    }

    public function provideTestLexing() {
        return $this->getTestsWithCapturingGroups();
    }
}