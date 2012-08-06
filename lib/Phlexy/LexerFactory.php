<?php

namespace Phlexy;

use Phlexy\Lexer;

class LexerFactory {
    protected $dataGenerator;

    public function __construct(\Phlexy\LexerDataGenerator $dataGenerator) {
        $this->dataGenerator = $dataGenerator;
    }

    public function createBestLexer(array $regexToTokenMap) {
        $regexes = array_keys($regexToTokenMap);
        $tokens = array_values($regexToTokenMap);

        $compiledRegex = $this->dataGenerator->getCompiledRegex($regexes);
        $offsetToLengthMap = $this->dataGenerator->getOffsetToLengthMap($regexes);
        $offsetToTokenMap = array_combine(array_keys($offsetToLengthMap), $tokens);

        if ($this->allRegexesWithoutCapturingGroups($offsetToLengthMap)) {
            return new Lexer\WithoutCapturingGroups($compiledRegex, $offsetToTokenMap);
        } else {
            return new Lexer\WithCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);
        }
    }

    protected function allRegexesWithoutCapturingGroups(array $offsetToLengthMap) {
        foreach ($offsetToLengthMap as $length) {
            if ($length !== 1) {
                return false;
            }
        }

        return true;
    }
}