<?php

namespace Phlexy\Lexer;

abstract class Stateful implements \Phlexy\Lexer {
    protected $initialState;
    protected $stateData;
    protected $stateStack;
    protected $currentStackPosition;
    protected $currentStateData;

    public function __construct($initialState, array $stateData) {
        $this->initialState = $initialState;
        $this->stateData = $stateData;
    }

    public function pushState($state): void {
        $this->stateStack[++$this->currentStackPosition] = $state;
        $this->currentStateData = $this->stateData[$state];
    }

    public function popState(): void {
        $state = $this->stateStack[--$this->currentStackPosition];
        $this->currentStateData = $this->stateData[$state];
    }

    public function swapState($state): void {
        $this->stateStack[$this->currentStackPosition] = $state;
        $this->currentStateData = $this->stateData[$state];
    }

    public function hasPushedStates(): bool {
        return $this->currentStackPosition > 0;
    }

    public function getStateStack(): array {
        return array_slice($this->stateStack, 0, $this->currentStackPosition + 1);
    }
}