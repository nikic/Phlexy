<?php

namespace Phlexy\LexerFactory\Stateful;

class UsingCompiledRegex implements \Phlexy\LexerFactory {
    protected $dataGen;

    public function __construct(\Phlexy\LexerDataGenerator $dataGen) {
        $this->dataGen = $dataGen;
    }

    public function createLexer(array $lexerDefinition, $additionalModifiers = '') {
        $initialState = key($lexerDefinition);

        $stateData = array();
        foreach ($lexerDefinition as $state => $regexToActionMap) {
            $regexes = array_keys($regexToActionMap);

            $compiledRegex = $this->dataGen->getCompiledRegex($regexes, $additionalModifiers);
            $offsetToLengthMap = $this->dataGen->getOffsetToLengthMap($regexes);
            $offsetToActionMap = array_combine(array_keys($offsetToLengthMap), $regexToActionMap);

            $stateData[$state] = array(
                'compiledRegex'     => $compiledRegex,
                'offsetToActionMap' => $offsetToActionMap,
                'offsetToLengthMap' => $offsetToLengthMap,
            );
        }

        return new \Phlexy\Lexer\Stateful\UsingCompiledRegex($initialState, $stateData);
    }
}