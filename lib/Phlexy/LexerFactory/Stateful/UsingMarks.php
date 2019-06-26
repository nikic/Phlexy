<?php

namespace Phlexy\LexerFactory\Stateful;

use Phlexy\Lexer;

class UsingMarks implements \Phlexy\LexerFactory {
    protected $dataGen;

    public function __construct(\Phlexy\LexerDataGenerator $dataGen) {
        $this->dataGen = $dataGen;
    }

    public function createLexer(array $lexerDefinition, string $additionalModifiers = ''): Lexer {
        $initialState = key($lexerDefinition);

        $stateData = array();
        foreach ($lexerDefinition as $state => $regexToActionMap) {
            $regexes = array_keys($regexToActionMap);
            $marks = $this->dataGen->getMarks(count($regexes));

            $compiledRegex = $this->dataGen->getCompiledRegexWithMarks($regexes, $marks, $additionalModifiers);
            $markToActionMap = array_combine($marks, $regexToActionMap);

            $stateData[$state] = array(
                'compiledRegex'   => $compiledRegex,
                'markToActionMap' => $markToActionMap,
            );
        }

        return new \Phlexy\Lexer\Stateful\UsingMarks($initialState, $stateData);
    }
}