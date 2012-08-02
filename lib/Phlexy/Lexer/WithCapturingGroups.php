<?php

class Phlexy_Lexer_WithCapturingGroups implements Phlexy_Lexer {
    protected $regex;
    protected $offsetToToken;
    protected $offsetToNumberOfCapturingGroups;

    public function __construct(array $regexToToken) {
        $lexerDataGenerator = new Phlexy_LexerDataGenerator;

        $this->regex = $lexerDataGenerator->getAllRegexesCompiledIntoOne(array_keys($regexToToken));

        $this->offsetToToken = array();
        $this->offsetToNumberOfCapturingGroups = array();
        $currentOffset = 0;
        foreach ($regexToToken as $regex => $token) {
            // We have to add +1 because the whole regex will also be made capturing
            $numberOfCapturingGroups = 1 + $lexerDataGenerator->getNumberOfCapturingGroupsInRegex($regex);

            $this->offsetToToken[$currentOffset] = $token;
            $this->offsetToNumberOfCapturingGroups[$currentOffset] = $numberOfCapturingGroups;

            $currentOffset += $numberOfCapturingGroups;
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

            $token = array($this->offsetToToken[$i - 1], $line);
            for ($j = 0; $j < $this->offsetToNumberOfCapturingGroups[$i - 1]; ++$j) {
                $token[] = $matches[$i + $j];
            }

            $tokens[] = $token;

            $offset += strlen($matches[0]);
            $line += substr_count("\n", $matches[0]);
        }

        return $tokens;
    }
}