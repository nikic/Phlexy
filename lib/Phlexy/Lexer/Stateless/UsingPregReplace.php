<?php

namespace Phlexy\Lexer\Stateless;

class UsingPregReplace implements \Phlexy\Lexer {
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
        $line = 1;

        // 5.3 can't access $this in closure, so put it in a variable
        $offsetToTokenMap = $this->offsetToTokenMap;
        $offsetToLengthMap = $this->offsetToLengthMap;
        $res = preg_replace_callback(
            $this->compiledRegex,
            function($matches) use($offsetToTokenMap, $offsetToLengthMap, &$tokens, &$line) {
                // find the first non-empty element (but skipping $matches[0]) using a quick for loop
                for ($i = 2; '' === $matches[$i]; ++$i);

                $realMatches = array();
                for ($j = 1, $length = $offsetToLengthMap[$i - 2]; $j < $length; ++$j) {
                    if (isset($matches[$i + $j])) {
                        $realMatches[$j] = $matches[$i + $j];
                    }
                }

                if (!empty($realMatches)) {
                    $tokens[] = array($offsetToTokenMap[$i - 2], $line, $matches[0], $realMatches);
                } else {
                    $tokens[] = array($offsetToTokenMap[$i - 2], $line, $matches[0]);
                }

                $line += substr_count($matches[0], "\n");

                return '';
            },
            $string
        );

        if ('' !== $res) {
            throw new \Phlexy\LexingException(sprintf(
                'Unexpected character "%s" on line %d', $res[0], $line
            ));
        }

        return $tokens;
    }
}