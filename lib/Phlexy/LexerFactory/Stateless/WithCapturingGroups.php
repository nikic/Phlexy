<?php

namespace Phlexy\LexerFactory\Stateless;

class WithCapturingGroups implements \Phlexy\LexerFactory {
    protected $dataGen;
    private $preferNamedGroups;

    /**
     * @param \Phlexy\LexerDataGenerator $dataGen
     * @param bool $preferNamedGroups
     */
    public function __construct(\Phlexy\LexerDataGenerator $dataGen, $preferNamedGroups = false) {
        $this->dataGen = $dataGen;
        $this->preferNamedGroups = $preferNamedGroups;
    }

    public function createLexer(array $lexerDefinition, $additionalModifiers = '') {
        $regexes = array_keys($lexerDefinition);

        $compiledRegex = $this->dataGen->getCompiledRegex($regexes, $additionalModifiers);
        $offsetToLengthMap = $this->dataGen->getOffsetToLengthMap($regexes);
        $offsetToTokenMap = array_combine(array_keys($offsetToLengthMap), $lexerDefinition);

        return new \Phlexy\Lexer\Stateless\WithCapturingGroups(
            $compiledRegex, $offsetToTokenMap, $offsetToLengthMap, $this->preferNamedGroups
        );
    }
}
