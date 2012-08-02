<?php

namespace Phlexy\Lexer;

require_once __DIR__ . '/../LexerTestAbstract.php';

class WithoutCapturingGroupsTest extends \Phlexy\LexerTestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $dataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
            = $dataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        return new \Phlexy\Lexer\WithoutCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);
    }

    public function provideTestLexing() {
        return $this->getTestsWithoutCapturingGroups();
    }
}