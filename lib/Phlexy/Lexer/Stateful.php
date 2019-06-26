<?php

namespace Phlexy\Lexer;

interface Stateful extends \Phlexy\Lexer {
    public function pushState($state): void;
    public function popState(): void;
    public function swapState($state): void;
    public function hasPushedStates(): bool;
    public function getStateStack(): array;
}