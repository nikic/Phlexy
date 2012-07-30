<?php

class Phlexy_Lexer {
    protected $regex;
    protected $offsetToToken;

    public function __construct(array $tokenMap) {
        $this->regex = '~(' . str_replace('~', '\~', implode(')|(', array_keys($tokenMap))) . ')~A';

        $this->offsetToToken = array();
        $currentOffset = 0;
        foreach ($tokenMap as $regex => $token) {
            $this->offsetToToken[$currentOffset] = $token;

            // increase the offset by one plus the number of capturing groups in the regex. I think the regex for
            // counting is fairly complete, but it does not handle (?| ... ) groups.
            $currentOffset += 1 + preg_match_all(
                '~
                    (?:
                        \(\?\(
                      | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                    ) (*SKIP)(*FAIL) |
                    \(
                    (?!
                        \?
                        (?!
                            <(?![!=])
                          | P<
                          | \'
                        )
                      | \*
                    )
                ~x',
                $regex, $dummyVar
            );
        }
    }

    public function lex($string) {
        $tokens = array();

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            if (!preg_match($this->regex, $string, $matches, 0, $offset)) {
                throw new Phlexy_LexingException(sprintf('Unexpected character "%s"', $string[$offset]));
            }

            // find the first non-empty element (but skipping $matches[0]) using a quick for loop
            for ($i = 1; '' === $matches[$i]; ++$i);

            $tokens[] = array($matches[0], $this->offsetToToken[$i - 1], $line);

            $offset += strlen($matches[0]);
            $line += substr_count("\n", $matches[0]);
        }

        return $tokens;
    }
}