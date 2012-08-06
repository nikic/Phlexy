<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class WithoutCapturingGroupsTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexer(array $lexerDefinition, $additionalModifiers) {
        $factory = new \Phlexy\LexerFactory\Stateless\WithoutCapturingGroups(
            new \Phlexy\LexerDataGenerator
        );

        return $factory->createLexer($lexerDefinition, $additionalModifiers);
    }

    public function provideTestLexing() {
        return $this->getTestsWithoutCapturingGroups();
    }
}