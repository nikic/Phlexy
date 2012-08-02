<?php

namespace Phlexy;

abstract class LexerTestAbstract extends \PHPUnit_Framework_TestCase {
    /** @return Lexer */
    abstract function createLexer(array $regexToTokenMap);

    /** @return array */
    abstract function provideTestLexing();

    /** @dataProvider provideTestLexing */
    public function testLexing(array $regexToTokenMap, array $inputsToExpectedOutputsMap) {
        $lexer = $this->createLexer($regexToTokenMap);

        foreach ($inputsToExpectedOutputsMap as $input => $expectedOutput) {
            $this->assertEquals($expectedOutput, $lexer->lex($input));
        }
    }

    /** @dataProvider provideTestLexingException */
    public function testLexingException(array $regexToTokenMap, $input, $expectedExceptionMessage) {
        $this->setExpectedException('Phlexy\\LexingException', $expectedExceptionMessage);

        $lexer = $this->createLexer($regexToTokenMap);

        $lexer->lex($input);
    }

    public function getTestsWithoutCapturingGroups() {
        return array(
            array(
                array(
                    '[^",\r\n]+'                     => 0,
                    '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"' => 1,
                    ','                              => 2,
                    '\r?\n'                          => 3,
                ),
                array(
                    'Field,Another Field,"comma -> , <- comma","quote -> \" <- quote"' => array(
                        array(0, 1, 'Field'),
                        array(2, 1, ','),
                        array(0, 1, 'Another Field'),
                        array(2, 1, ','),
                        array(1, 1, '"comma -> , <- comma"'),
                        array(2, 1, ','),
                        array(1, 1, '"quote -> \" <- quote"'),
                    ),
                    "Field1.1,Field1.2\nField2.1,Field2.2" => array(
                        array(0, 1, 'Field1.1'),
                        array(2, 1, ','),
                        array(0, 1, 'Field1.2'),
                        array(3, 1, "\n"),
                        array(0, 2, 'Field2.1'),
                        array(2, 2, ','),
                        array(0, 2, 'Field2.2'),
                    ),
                )
            ),
        );
    }

    public function getTestsWithCapturingGroups() {
        return array(
            array(
                array(
                    '\s+'          => 0,
                    '\$(\w+)'      => 1,
                    '(\d+)\.(\d+)' => 2
                ),
                array(
                    '$foo 3.141 $bar' => array(
                        array(1, 1, '$foo', 'foo'),
                        array(0, 1, ' '),
                        array(2, 1, '3.141', '3', '141'),
                        array(0, 1, ' '),
                        array(1, 1, '$bar', 'bar'),
                    ),
                )
            ),
        );
    }

    public function provideTestLexingException() {
        return array(
            array(
                array(
                    'foo' => 0,
                    'bar' => 1,
                ),
                'baz',
                'Unexpected character "b" on line 1'
            ),
        );
    }
}