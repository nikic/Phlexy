<?php

namespace Phlexy;

interface LexerFactory {
    /**
     * @param array $lexerDefinition
     * @param string $additionalModifiers
     * @return \Phlexy\Lexer
     */
    public function createLexer(array $lexerDefinition, $additionalModifiers = '');
}
