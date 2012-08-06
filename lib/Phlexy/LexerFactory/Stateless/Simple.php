<?php

namespace Phlexy\LexerFactory\Stateless;

class Simple implements \Phlexy\LexerFactory {
    public function createLexer(array $lexerDefinition) {
        return new \Phlexy\Lexer\Stateless\Simple($lexerDefinition);
    }
}