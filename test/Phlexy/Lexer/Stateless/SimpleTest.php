<?php

namespace Phlexy\Lexer;

require_once __DIR__ . '/../TestAbstract.php';

class SimpleTest extends TestAbstract {
    public function createLexer(array $regexToTokenMap) {
        return new Stateless\Simple($regexToTokenMap);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}