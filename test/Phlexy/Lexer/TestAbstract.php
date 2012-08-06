<?php

namespace Phlexy\Lexer;

use Phlexy\RestartException;

abstract class TestAbstract extends \PHPUnit_Framework_TestCase {
    /** @return \Phlexy\LexerFactory */
    abstract function createLexerFactory();

    /** @return array */
    abstract function provideTestLexing();

    /** @dataProvider provideTestLexing */
    public function testLexing(array $lexerDefinition, $additionalModifiers, array $inputsToExpectedOutputsMap) {
        $lexer = $this->createLexerFactory()->createLexer($lexerDefinition, $additionalModifiers);

        foreach ($inputsToExpectedOutputsMap as $input => $expectedOutput) {
            $this->assertEquals($expectedOutput, $lexer->lex($input));
        }
    }

    /** @dataProvider provideTestLexingException */
    public function testLexingException(array $lexerDefinition, $input, $expectedExceptionMessage) {
        $this->setExpectedException('Phlexy\\LexingException', $expectedExceptionMessage);

        $lexer =  $this->createLexerFactory()->createLexer($lexerDefinition, '');

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
                '', // no additional modifiers
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
            array(
                array(
                    'foo' => 0,
                ),
                'i',
                array(
                    'foo' => array(
                        array(0, 1, 'foo'),
                    ),
                    'FOO' => array(
                        array(0, 1, 'FOO'),
                    ),
                    'fOo' => array(
                        array(0, 1, 'fOo'),
                    ),
                )
            ),
        );
    }

    public function getTestsWithCapturingGroups() {
        return array_merge(
            $this->getTestsWithoutCapturingGroups(),
            array(
                array(
                    array(
                        '\s+'          => 0,
                        '\$(\w+)'      => 1,
                        '(\d+)\.(\d+)' => 2
                    ),
                    '', // no additional modifiers
                    array(
                        '$foo 3.141 $bar' => array(
                            array(1, 1, '$foo', array(1 => 'foo')),
                            array(0, 1, ' '),
                            array(2, 1, '3.141', array(1 => '3', 2 => '141')),
                            array(0, 1, ' '),
                            array(1, 1, '$bar', array(1 => 'bar')),
                        ),
                    )
                ),
                array(
                    array(
                        'x'      => 0,
                        'a(y)?b' => 1,
                    ),
                    '', // no additional modifiers
                    array(
                        'xayb' => array(
                            array(0, 1, 'x'),
                            array(1, 1, 'ayb', array(1 => 'y')),
                        ),
                        'xab' => array(
                            array(0, 1, 'x'),
                            array(1, 1, 'ab'),
                        ),
                    )
                ),
            )
        );
    }

    public function getStatefulTests() {
        return array_merge(
            $this->statelessTestsToStateful($this->getTestsWithoutCapturingGroups()),
            $this->statelessTestsToStateful($this->getTestsWithCapturingGroups()),
            array(
                array(
                    array(
                        'INITIAL' => array(
                            // a keyword
                            'as\b' => T_AS,

                            // some tokens
                            '\$\w+' => T_VARIABLE,
                            '\w+'  => T_STRING,
                            '\s+'  => T_WHITESPACE,
                            '\{'   => '{',
                            '\}'   => '}',

                            // go into a new state where keywords are ignored
                            '->' => function(Stateful $lexer) {
                                $lexer->pushState('LOOKING_FOR_PROPETY');

                                return T_OBJECT_OPERATOR;
                            },
                        ),
                        'LOOKING_FOR_PROPETY' => array(
                            '\s+' => T_WHITESPACE,
                            '\w+' => function(Stateful $lexer) {
                                $lexer->popState();

                                return T_STRING;
                            },
                            '.' => function(Stateful $lexer) {
                                $lexer->popState();

                                throw new RestartException;
                            },
                        ),
                    ),
                    '', // no additional modifiers
                    array(
                        'as' =>
                        array(
                            array(T_AS, 1, 'as'),
                        ),
                        '$foo -> bar' =>
                        array(
                            array(T_VARIABLE, 1, '$foo'),
                            array(T_WHITESPACE, 1, ' '),
                            array(T_OBJECT_OPERATOR, 1, '->'),
                            array(T_WHITESPACE, 1, ' '),
                            array(T_STRING, 1, 'bar'),
                        ),
                        '$foo -> as' =>
                        array(
                            array(T_VARIABLE, 1, '$foo'),
                            array(T_WHITESPACE, 1, ' '),
                            array(T_OBJECT_OPERATOR, 1, '->'),
                            array(T_WHITESPACE, 1, ' '),
                            array(T_STRING, 1, 'as'),
                        ),
                        '$foo -> {foo}' =>
                        array(
                            array(T_VARIABLE, 1, '$foo'),
                            array(T_WHITESPACE, 1, ' '),
                            array(T_OBJECT_OPERATOR, 1, '->'),
                            array(T_WHITESPACE, 1, ' '),
                            array('{', 1, '{'),
                            array(T_STRING, 1, 'foo'),
                            array('}', 1, '}'),
                        ),
                    )
                ),
                array(
                    // this doesn't make any sense, just testing different state methods
                    array(
                        'INITIAL' => array(
                            'a' => function(Stateful $lexer) {
                                $token = array($lexer->hasPushedStates(), $lexer->getStateStack());
                                $lexer->pushState('A');
                                return $token;
                            },
                        ),
                        'A' => array(
                            'b' => function(Stateful $lexer) {
                                $token = array($lexer->hasPushedStates(), $lexer->getStateStack());
                                $lexer->pushState('B');
                                return $token;
                            },
                            'e' => function(Stateful $lexer) {
                                $token = array($lexer->hasPushedStates(), $lexer->getStateStack());
                                // nothing more
                                return $token;
                            },
                        ),
                        'B' => array(
                            'c' => function(Stateful $lexer) {
                                $token = array($lexer->hasPushedStates(), $lexer->getStateStack());
                                $lexer->swapState('C');
                                return $token;
                            },
                        ),
                        'C' => array(
                            'd' => function(Stateful $lexer) {
                                $token = array($lexer->hasPushedStates(), $lexer->getStateStack());
                                $lexer->popState();
                                return $token;
                            },
                        ),
                    ),
                    '', // no additional modifiers
                    array(
                        'abcde' => array(
                            array(array(false, array('INITIAL')), 1, 'a'),
                            array(array(true, array('INITIAL', 'A')), 1, 'b'),
                            array(array(true, array('INITIAL', 'A', 'B')), 1, 'c'),
                            array(array(true, array('INITIAL', 'A', 'C')), 1, 'd'),
                            array(array(true, array('INITIAL', 'A')), 1, 'e'),
                        )
                    )
                ),
            )
        );
    }

    protected function statelessTestsToStateful(array $tests) {
        foreach ($tests as &$test) {
            // just put all rules into a single state
            $test[0] = array('INITIAL' => $test[0]);

            // for the capturing group tests the stateful output is different because
            // the capturing groups are not returned, so this has to be adjusted here
            foreach ($test[2] as &$output) {
                foreach ($output as &$token) {
                    $token = array_slice($token, 0, 3);
                }
            }
        }

        return $tests;
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
            array(
                array(
                    'foo' => 0,
                    'bar' => 1,
                ),
                'foobazbar',
                'Unexpected character "b" on line 1'
            ),
        );
    }
}