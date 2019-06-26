<?php

namespace Phlexy\Lexer\Stateful;

use Phlexy\Lexer\Stateful;

class UsingMarks extends Stateful {
    public function __construct($initialState, array $stateData) {
        parent::__construct($initialState, $stateData);
    }

    public function lex(string $string): array {
        $tokens = array();

        $this->stateStack = array($this->initialState);
        $this->currentStackPosition = 0;
        $this->currentStateData = $this->stateData[$this->initialState];

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            if (!preg_match($this->currentStateData['compiledRegex'], $string, $matches, 0, $offset)) {
                throw new \Phlexy\LexingException(sprintf(
                    'Unexpected character "%s" on line %d', $string[$offset], $line
                ));
            }

            $mark = $matches['MARK'];
            $text = $matches[0];

            $action = $this->currentStateData['markToActionMap'][$mark];
            if (is_callable($action)) {
                try {
                    $token = array($action($this, $matches), $line, $text);
                } catch (\Phlexy\RestartException $e) {
                    continue;
                }
            } else {
                $token = array($action, $line, $text);
            }

            $tokens[] = $token;

            $offset += \strlen($text);
            $line += substr_count($text, "\n");
        }

        return $tokens;
    }
}