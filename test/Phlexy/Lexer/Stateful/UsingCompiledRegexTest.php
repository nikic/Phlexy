<?php

namespace Phlexy\Lexer\Stateful;

use Phlexy\Lexer\Stateful;

require_once __DIR__ . '/../TestAbstract.php';

class UsingCompiledRegexTest extends \Phlexy\Lexer\TestAbstract{
    public function createLexer(array $lexerDefinition) {
        $dataGen = new \Phlexy\LexerDataGenerator;

        $stateData = array();
        foreach ($lexerDefinition as $state => $regexToTokenMap) {
            list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
                = $dataGen->getDataFromRegexToTokenMap($regexToTokenMap);

            $stateData[$state] = array(
                'compiledRegex'     => $compiledRegex,
                'offsetToActionMap' => $offsetToTokenMap,
                'offsetToLengthMap' => $offsetToLengthMap,
            );
        }

        return new UsingCompiledRegex('INITIAL', $stateData);
    }

    public function provideTestLexing() {
        return $this->getStatefulTests();
    }

    public function provideTestLexingException() {
        $tests = parent::provideTestLexingException();
        foreach ($tests as &$test) {
            $test[0] = array('INITIAL' => $test[0]);
        }
        return $tests;
    }
}