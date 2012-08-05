<?php

namespace Phlexy\Lexer;

interface Stateful extends \Phlexy\Lexer {
    public function pushState($state);
    public function popState();
    public function swapState($state);
    public function hasPushedStates();
    public function getStateStack();
}