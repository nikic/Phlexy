<?php

namespace Phlexy\Lexer\Stateless;

require_once __DIR__ . '/../TestAbstract.php';

class WithCapturingGroupsTest extends \Phlexy\Lexer\TestAbstract {
    public function createLexerFactory() {
        return new \Phlexy\LexerFactory\Stateless\WithCapturingGroups(
            new \Phlexy\LexerDataGenerator,
            false
        );
    }

    public function provideTestLexing() {
        return $this->getTestsWithCapturingGroups();
    }

    public function testNamedGroups() {
        $lexerFactory = new \Phlexy\LexerFactory\Stateless\WithCapturingGroups(
            new \Phlexy\LexerDataGenerator,
            true
        );

        $lexer = $lexerFactory->createLexer(
            array(
                '\s+'          => 0,
                '\$(?<varName>\w+)'      => 1,
                '(?<intPart>\d+)\.(?<fractionalPart>\d+)' => 2
            )
        );

        $this->assertEquals(array(
            array(1, 1, '$foo', array('varName' => 'foo')),
            array(0, 1, ' '),
            array(2, 1, '3.141', array('intPart' => '3', 'fractionalPart' => '141')),
            array(0, 1, ' '),
            array(1, 1, '$bar', array('varName' => 'bar')),
        ), $lexer->lex('$foo 3.141 $bar'));
    }
}
