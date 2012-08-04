<?php

namespace Phlexy\Lexer;

require_once __DIR__ . '/../TestAbstract.php';

class WithoutCapturingGroupsTest extends TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $dataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
            = $dataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        return new Stateless\WithoutCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);
    }

    public function provideTestLexing() {
        return $this->getTestsWithoutCapturingGroups();
    }
}