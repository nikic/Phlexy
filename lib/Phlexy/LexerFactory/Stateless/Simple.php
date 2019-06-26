<?php

namespace Phlexy\LexerFactory\Stateless;

use Phlexy\Lexer;

class Simple implements \Phlexy\LexerFactory {
    public function createLexer(array $lexerDefinition, string $additionalModifiers = ''): Lexer {
        return new \Phlexy\Lexer\Stateless\Simple($lexerDefinition, $additionalModifiers);
    }
}