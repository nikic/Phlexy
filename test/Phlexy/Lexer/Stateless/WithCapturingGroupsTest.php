<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class WithCapturingGroupsTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexer(array $lexerDefinition, $additionalModifiers) {
        $factory = new \Phlexy\LexerFactory\Stateless\WithCapturingGroups(
            new \Phlexy\LexerDataGenerator
        );

        return $factory->createLexer($lexerDefinition, $additionalModifiers);
    }

    public function provideTestLexing() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            $this->getTestsWithCapturingGroups()
        );
    }
}