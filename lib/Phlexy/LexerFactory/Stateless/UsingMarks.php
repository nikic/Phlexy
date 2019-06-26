<?php

namespace Phlexy\LexerFactory\Stateless;

use Phlexy\Lexer;

class UsingMarks implements \Phlexy\LexerFactory {
    protected $dataGen;

    public function __construct(\Phlexy\LexerDataGenerator $dataGen) {
        $this->dataGen = $dataGen;
    }

    public function createLexer(array $lexerDefinition, string $additionalModifiers = ''): Lexer {
        $regexes = array_keys($lexerDefinition);
        $marks = $this->dataGen->getMarks(count($regexes));
        $compiledRegex = $this->dataGen->getCompiledRegexWithMarks($regexes, $marks, $additionalModifiers);
        $markToTokenMap = array_combine($marks, $lexerDefinition);

        return new \Phlexy\Lexer\Stateless\UsingMarks(
            $compiledRegex, $markToTokenMap
        );
    }
}