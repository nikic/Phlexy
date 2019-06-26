<?php

namespace Phlexy\LexerFactory\Stateful;

use Phlexy\Lexer;

class Simple implements \Phlexy\LexerFactory {
    public function createLexer(array $lexerDefinition, string $additionalModifiers = ''): Lexer {
        $initialState = key($lexerDefinition);

        return new \Phlexy\Lexer\Stateful\Simple($initialState, $lexerDefinition, $additionalModifiers);
    }
}