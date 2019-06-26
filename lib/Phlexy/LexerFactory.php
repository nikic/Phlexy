<?php

namespace Phlexy;

interface LexerFactory {
    public function createLexer(array $lexerDefinition, string $additionalModifiers = ''): Lexer;
}