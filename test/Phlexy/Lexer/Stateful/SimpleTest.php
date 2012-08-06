<?php

namespace Phlexy\Lexer\Stateful;

use Phlexy\Lexer\Stateful;

require_once __DIR__ . '/../TestAbstract.php';

class SimpleTest extends \Phlexy\Lexer\TestAbstract{
    public function createLexerFactory() {
        return new \Phlexy\LexerFactory\Stateful\Simple;
    }

    public function provideTestLexing() {
        return $this->getStatefulTests();
    }

    public function provideTestLexingException() {
        $tests = parent::provideTestLexingException();
        foreach ($tests as &$test) {
            $test[0] = array('INITIAL' => $test[0]);
        }
        return $tests;
    }
}