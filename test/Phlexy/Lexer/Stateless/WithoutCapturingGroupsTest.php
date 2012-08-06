<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class WithoutCapturingGroupsTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $dataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap)
            = $dataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        return new WithoutCapturingGroups($compiledRegex, $offsetToTokenMap);
    }

    public function provideTestLexing() {
        return $this->getTestsWithoutCapturingGroups();
    }
}