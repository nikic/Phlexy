<?php

namespace Phlexy;

class LexerDataGeneratorTest extends \PHPUnit_Framework_TestCase {
    public function testRegexCompilation() {
        $dataGenerator = new LexerDataGenerator;

        $this->assertEquals(
            '~(foo bar)|(foo (bar), (?(?!bla)bli|blu))|(foo \~ bar)~A',
            $dataGenerator->getCompiledRegex(array(
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
                19 => 1,
                20 => 2,
                22 => 3,
                25 => 1,
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
                'a\(b',
                '(foo)',
                'bar(b(a)z)',
                'blub',
            ))
        );
    }
}