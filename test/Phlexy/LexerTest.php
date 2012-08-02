<?php

namespace Phlexy;

use Phlexy\Lexer;

class LexerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @dataProvider provideTestLexing
     */
    public function testLexing($regexToTokenMap, $inputsToExpectedOutputsMap) {
        $lexerDataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
            = $lexerDataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        $lexer = new Lexer\WithCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);

        foreach ($inputsToExpectedOutputsMap as $input => $expectedOutput) {
            $this->assertEquals($expectedOutput, $lexer->lex($input));
        }
    }

    /**
     * @dataProvider provideTestLexingException
     */
    public function testLexingException($regexToTokenMap, $input, $expectedExceptionMessage) {
        $this->setExpectedException('Phlexy\\LexingException', $expectedExceptionMessage);

        $lexerDataGenerator = new \Phlexy\LexerDataGenerator;

        list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
            = $lexerDataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

        $lexer = new Lexer\WithCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);

        $lexer->lex($input);
    }

    public function provideTestLexing() {
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
            // Make sure delimiter is escaped properly
            array(
                array(
                    '~' => 0,
                ),
                array(
                    '~' => array(
                        array(0, 1, '~')
                    ),
                )
            ),
            array(
                array(
                    'x[\](]'    => -16,
                    'x[(]'      => -15,
                    'x(*FAIL)'  => -14,
                    'x(?(1)n)'  => -13,
                    "x(?'l'm)"  => -12,
                    'x(?P<j>k)' => -11,
                    'x(?<h>i)'  => -10,
                    'x(?P>j)'   => -9,
                    'x(?&h)'    => -8,
                    'x(?#g)'    => -7,
                    'x(?>f)'    => -6,
                    '(?<=e)x'   => -5,
                    '(?<!d)x'   => -4,
                    'x(?=c)'    => -3,
                    'x(?!b)'    => -2,
                    'x(?:a)'    => -1,
                    '(foo)'     => 0,
                    'bar(baz)'  => 1,
                ),
                array(
                    'foobarbaz' => array(
                        array(0, 1, 'foo', 'foo'),
                        array(1, 1, 'barbaz', 'baz'),
                    ),
                ),
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
                'Unexpected character "b"'
            ),
        );
    }
}