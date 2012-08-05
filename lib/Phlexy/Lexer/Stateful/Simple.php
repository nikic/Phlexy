<?php

namespace Phlexy\Lexer\Stateful;

class Simple implements \Phlexy\Lexer\Stateful {
    protected $initialState;
    protected $stateData;
    protected $additionalModifiers;

    protected $stateStack;
    protected $currentStackPosition;
    protected $currentStateData;

    public function __construct($initialState, array $stateData, $additionalModifiers = '') {
        $this->initialState = $initialState;
        $this->stateData = $stateData;
        $this->additionalModifiers = $additionalModifiers;
    }

    public function pushState($state) {
        $this->stateStack[++$this->currentStackPosition] = $state;
        $this->currentStateData = $this->stateData[$state];
    }

    public function popState() {
        $state = $this->stateStack[--$this->currentStackPosition];
        $this->currentStateData = $this->stateData[$state];
    }

    public function swapState($state) {
        $this->stateStack[$this->currentStackPosition] = $state;
        $this->currentStateData = $this->stateData[$state];
    }

    public function hasPushedStates() {
        return $this->currentStackPosition > 0;
    }

    public function getStateStack() {
        return array_slice($this->stateStack, 0, $this->currentStackPosition + 1);
    }

    public function lex($string) {
        $tokens = array();

        $this->stateStack = array($this->initialState);
        $this->currentStackPosition = 0;
        $this->currentStateData = $this->stateData[$this->initialState];

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            foreach ($this->currentStateData as $regex => $tokenOrAction) {
                $regex = '~' . str_replace('~', '\~', $regex) . '~A' . $this->additionalModifiers;
                if (!preg_match($regex, $string, $matches, 0, $offset)) {
                    continue;
                }

                try {
                    $tokens[] = array(
                        is_callable($tokenOrAction) ? $tokenOrAction($this, $matches) : $tokenOrAction,
                        $line,
                        $matches[0]
                    );
                } catch (\Phlexy\RestartException $e) {
                    continue 2;
                }

                $offset += strlen($matches[0]);
                $line += substr_count($matches[0], "\n");

                continue 2;
            }

            throw new \Phlexy\LexingException(sprintf(
                'Unexpected character "%s" on line %d', $string[$offset], $line
            ));
        }

        return $tokens;
    }
}