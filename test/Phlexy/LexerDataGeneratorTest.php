<?php

namespace Phlexy;

class LexerDataGeneratorTest extends \PHPUnit_Framework_TestCase {
    public function testRegexCompilation() {
        $dataGenerator = new LexerDataGenerator;

        $this->assertEquals(
            '~(foo bar)|(foo (bar), (?(?!bla)bli|blu))|(foo \~ bar)~A',
            $dataGenerator->getAllRegexesCompiledIntoOne(array(
                'foo bar',
                'foo (bar), (?(?!bla)bli|blu)',
                'foo ~ bar',
            ))
        );
    }

    public function testOffsetToLengthMapGeneration() {
        $dataGenerator = new LexerDataGenerator;

        $this->assertEquals(
            array(
                0  => 1,
                1  => 1,
                2  => 1,
                3  => 1,
                4  => 2,
                6  => 2,
                8  => 2,
                10 => 1,
                11 => 1,
                12 => 1,
                13 => 1,
                14 => 1,
                15 => 1,
                16 => 1,
                17 => 1,
                18 => 1,
                19 => 2,
                21 => 3,
                24 => 1,
            ),
            $dataGenerator->getOffsetToLengthMap(array(
                'x[\](]',
                'x[(]',
                'x(*FAIL)',
                'x(?(1)n)',
                "x(?'l'm)",
                'x(?P<j>k)',
                'x(?<h>i)',
                'x(?P>j)',
                'x(?&h)',
                'x(?#g)',
                'x(?>f)',
                '(?<=e)x',
                '(?<!d)x',
                'x(?=c)',
                'x(?!b)',
                'x(?:a)',
                '(foo)',
                'bar(b(a)z)',
                'blub',
            ))
        );
    }

    public function testGettingDataFromRegexToTokenMap() {
        $dataGenerator = new LexerDataGenerator;

        $this->assertEquals(
            array(
                '~(foo)|(b(a(a)a)r)|(baz)~A',
                array(
                    0 => 'tok1',
                    1 => 'tok2',
                    4 => 'tok3',
                ),
                array(
                    0 => 1,
                    1 => 3,
                    4 => 1,
                )
            ),
            $dataGenerator->getDataFromRegexToTokenMap(array(
                'foo'       => 'tok1',
                'b(a(a)a)r' => 'tok2',
                'baz'       => 'tok3',
            ))
        );
    }
}