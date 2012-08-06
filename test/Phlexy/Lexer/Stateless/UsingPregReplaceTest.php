<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class UsingPregReplaceTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        $dataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
            = $dataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        return new UsingPregReplace($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}