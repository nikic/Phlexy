<?php

namespace Phlexy\Lexer;

require_once __DIR__ . '/../LexerTestAbstract.php';

class SimpleTest extends \Phlexy\LexerTestAbstract {
    public function createLexer(array $regexToTokenMap) {
        return new \Phlexy\Lexer\Simple($regexToTokenMap);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}