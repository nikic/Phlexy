<?php

namespace Phlexy\Lexer\Stateless;

class WithCapturingGroups implements \Phlexy\Lexer {
    protected $compiledRegex;
    protected $offsetToTokenMap;
    protected $offsetToLengthMap;

    public function __construct($compiledRegex, array $offsetToTokenMap, array $offsetToLengthMap) {
        $this->compiledRegex = $compiledRegex;
        $this->offsetToTokenMap = $offsetToTokenMap;
        $this->offsetToLengthMap = $offsetToLengthMap;
    }

    public function lex($string) {
        $tokens = array();

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            if (!preg_match($this->compiledRegex, $string, $matches, 0, $offset)) {
                throw new \Phlexy\LexingException(sprintf(
                    'Unexpected character "%s" on line %d', $string[$offset], $line
                ));
            }

            // find the first non-empty element (but skipping $matches[0]) using a quick for loop
            for ($i = 1; '' === $matches[$i]; ++$i);

            // if the length is 1 we already know there won't be matches, so we can skip their creation
            if (1 !== $length = $this->offsetToLengthMap[$i - 1]) {
                $realMatches = array();
                for ($j = 1; $j < $length; ++$j) {
                    if (isset($matches[$i + $j])) {
                        $realMatches[$j] = $matches[$i + $j];
                    }
                }

                if (!empty($realMatches)) {
                    $tokens[] = array($this->offsetToTokenMap[$i - 1], $line, $matches[0], $realMatches);
                } else {
                    $tokens[] = array($this->offsetToTokenMap[$i - 1], $line, $matches[0]);
                }
            } else {
                $tokens[] = array($this->offsetToTokenMap[$i - 1], $line, $matches[0]);
            }

            $offset += strlen($matches[0]);
            $line += substr_count($matches[0], "\n");
        }

        return $tokens;
    }
}