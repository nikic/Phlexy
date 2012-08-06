<?php

namespace Phlexy\Lexer\Stateless;

class Simple implements \Phlexy\Lexer {
    protected $regexToToken;

    public function __construct(array $regexToToken, $additionalModifiers = 'i') {
        $this->regexToToken = array();
        foreach ($regexToToken as $regex => $token) {
            $regex = '~' . str_replace('~', '\~', $regex) . '~A' . $additionalModifiers;
            $this->regexToToken[$regex] = $token;
        }
    }

    public function lex($string) {
        $tokens = array();

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            foreach ($this->regexToToken as $regex => $token) {
                if (!preg_match($regex, $string, $matches, 0, $offset)) {
                    continue;
                }

                $matchedString = $matches[0];

                unset($matches[0]);
                if (!empty($matches)) {
                    $tokens[] = array($token, $line, $matchedString, $matches);
                } else {
                    $tokens[] = array($token, $line, $matchedString);
                }

                $offset += strlen($matchedString);
                $line += substr_count($matchedString, "\n");

                continue 2;
            }

            throw new \Phlexy\LexingException(sprintf(
                'Unexpected character "%s" on line %d', $string[$offset], $line
            ));
        }

        return $tokens;
    }
}