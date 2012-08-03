<?php

namespace Phlexy;

class CodeGeneratorTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider provideTestMakeValue */
    public function testMakeValue($value, $expectedPrintedValue) {
        $codeGen = new CodeGenerator;

        $this->assertEquals($expectedPrintedValue, $codeGen->makeValue($value));
    }

    public function provideTestMakeValue() {
        return array(
            array(null, 'null'),
            array(true, 'true'),
            array(false, 'false'),
            array(12345, '12345'),
            array(3.141, '3.141'),
            array(INF, 'INF'),
            array(-INF, '-INF'),
            array(NAN, 'NAN'),
            array('foobar', "'foobar'"),
            array('foo\'bar\\baz', "'foo\\'bar\\\\baz'"),
            array(array(), 'array()'),
            array(array(1, 2, 3), 'array(1, 2, 3)'),
            array(array('foo' => 'bar', 'bar' => 'foo'), "array('foo' => 'bar', 'bar' => 'foo')"),
            array(array(0 => 0, 3 => 3), "array(0 => 0, 3 => 3)")
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot PHP-ize value of type object
     */
    public function testInvalidTypeError() {
        $codeGen = new CodeGenerator;

        $codeGen->makeValue(new \stdClass);
    }

    /** @dataProvider provideTestIndent */
    public function testIndent($string, $expectedOutput, $indentLevel, $indentString) {
        $codeGen = new CodeGenerator($indentString);

        $this->assertEquals($expectedOutput, $codeGen->indent($string, $indentLevel));
    }

    public function provideTestIndent() {
        return array(
            array('foobar;', '    foobar;', 1, '    '),
            array('foobar;', '            foobar;', 3, '    '),
            array('foobar;', "\t\t\tfoobar;", 3, "\t"),
            array("foo;\nbar;\nbaz;", "    foo;\n    bar;\n    baz;", 1, '    '),
            array("foo;\nbar;\nbaz;", "        foo;\n        bar;\n        baz;", 2, '    '),
            array("  a;\n  b;", "    a;\n    b;", 1, '  '),
        );
    }

    public function testMakeBraces() {
        $codeGen = new CodeGenerator;

        $this->assertEquals($this->normalizeNewlines(<<<'CODE_OUT'
{
    doFoo();
    if ($bar) {
        doBaz();
    }
    doMoreFoo();
}
CODE_OUT
        ), $codeGen->makeBraces($this->normalizeNewlines(<<<'CODE_IN'
doFoo();
if ($bar) {
    doBaz();
}
doMoreFoo();
CODE_IN
        )));
    }

    public function testMakeIf() {
        $codeGen = new CodeGenerator;

        $this->assertEquals($this->normalizeNewlines(<<<'CODE_OUT'
if (someCondition) {
    some;
    multiline;
    code;
}
CODE_OUT
            ) ,
            $codeGen->makeIf('someCondition', "some;\nmultiline;\ncode;")
        );

        $this->assertEquals($this->normalizeNewlines(<<<'CODE_OUT'
if (someCondition) {
    some;
    multiline;
    code;
} else {
    some;
    other;
    code;
}
CODE_OUT
            ) ,
            $codeGen->makeIf('someCondition', "some;\nmultiline;\ncode;", "some;\nother;\ncode;")
        );
    }

    protected  function normalizeNewlines($string) {
        return str_replace("\r\n", "\n", $string);
    }
}