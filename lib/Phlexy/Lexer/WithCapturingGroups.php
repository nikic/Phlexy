<?php

namespace Phlexy\Lexer;

class WithCapturingGroups implements \Phlexy\Lexer {
    protected $regex;
    protected $offsetToTokenMap;
    protected $offsetToLengthMap;

    public function __construct($regex, array $offsetToTokenMap, array $offsetToLengthMap) {
        $this->regex = $regex;
        $this->offsetToTokenMap = $offsetToTokenMap;
        $this->offsetToLengthMap = $offsetToLengthMap;
    }

    public function lex($string) {
        $tokens = array();

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            if (!preg_match($this->regex, $string, $matches, 0, $offset)) {
                throw new \Phlexy\LexingException(sprintf('Unexpected character "%s"', $string[$offset]));
            }

            // find the first non-empty element (but skipping $matches[0]) using a quick for loop
            for ($i = 1; '' === $matches[$i]; ++$i);

            $token = array($this->offsetToTokenMap[$i - 1], $line);
            for ($j = 0; $j < $this->offsetToLengthMap[$i - 1]; ++$j) {
                $token[] = $matches[$i + $j];
            }

            $tokens[] = $token;

            $offset += strlen($matches[0]);
            $line += substr_count("\n", $matches[0]);
        }

        return $tokens;
    }
}