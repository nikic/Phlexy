<?php

namespace Phlexy\Lexer\Stateful;

use Phlexy\Lexer\Stateful;

class Simple extends Stateful {
    protected $additionalModifiers;

    public function __construct($initialState, array $stateData, string $additionalModifiers = '') {
        parent::__construct($initialState, $stateData);
        $this->additionalModifiers = $additionalModifiers;
    }

    public function lex(string $string): array {
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