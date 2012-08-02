<?php

namespace Phlexy\Lexer;

require_once __DIR__ . '/../LexerTestAbstract.php';

class WithCapturingGroupsTest extends \Phlexy\LexerTestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $dataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
            = $dataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        return new \Phlexy\Lexer\WithCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}