<?php

namespace Phlexy\Lexer\Stateful;

use Phlexy\Lexer\Stateful;

class UsingCompiledRegex extends Stateful {
    public function __construct($initialState, array $stateData) {
        parent::__construct($initialState, $stateData);
    }

    public function lex(string $string): array {
        $this->initStateStack();

        $tokens = array();
        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            if (!preg_match($this->currentStateData['compiledRegex'], $string, $matches, 0, $offset)) {
                throw new \Phlexy\LexingException(sprintf(
                    'Unexpected character "%s" on line %d', $string[$offset], $line
                ));
            }

            // find the first non-empty element (but skipping $matches[0]) using a quick for loop
            for ($i = 1; '' === $matches[$i]; ++$i);

            $action = $this->currentStateData['offsetToActionMap'][$i - 1];
            if (is_callable($action)) {
                $realMatches = array();
                for ($j = 0; $j < $this->currentStateData['offsetToLengthMap'][$i - 1]; ++$j) {
                    if (isset($matches[$i + $j])) {
                        $realMatches[$j] = $matches[$i + $j];
                    }
                }

                try {
                    $token = array($action($this, $realMatches), $line, $matches[0]);
                } catch (\Phlexy\RestartException $e) {
                    continue;
                }
            } else {
                $token = array($action, $line, $matches[0]);
            }

            $tokens[] = $token;

            $offset += strlen($matches[0]);
            $line += substr_count($matches[0], "\n");
        }

        return $tokens;
    }
}