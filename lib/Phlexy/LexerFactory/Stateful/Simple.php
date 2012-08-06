<?php

namespace Phlexy\LexerFactory\Stateful;

class Simple implements \Phlexy\LexerFactory {
    public function createLexer(array $lexerDefinition, $additionalModifiers = '') {
        $initialState = key($lexerDefinition);

        return new \Phlexy\Lexer\Stateful\Simple($initialState, $lexerDefinition, $additionalModifiers);
    }
}