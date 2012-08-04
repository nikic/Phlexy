<?php

namespace Phlexy\Lexer;

require_once __DIR__ . '/../TestAbstract.php';

class WithCapturingGroupsTest extends TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $dataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
            = $dataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        return new Stateless\WithCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}