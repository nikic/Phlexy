<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class UsingPregReplaceTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexerFactory() {
        return new \Phlexy\LexerFactory\Stateless\UsingPregReplace(
            new \Phlexy\LexerDataGenerator
        );
    }

    public function provideTestLexing() {
        return $this->getTestsWithCapturingGroups();
    }
}