<?php

namespace Phlexy\Lexer\Stateless;

class UsingMarks implements \Phlexy\Lexer {
    protected $compiledRegex;
    protected $markToTokenMap;

    public function __construct(string $compiledRegex, array $markToTokenMap) {
        $this->compiledRegex = $compiledRegex;
        $this->markToTokenMap = $markToTokenMap;
    }

    public function lex(string $string): array {
        $tokens = array();

        $offset = 0;
        $line = 1;
        while (isset($string[$offset])) {
            if (!preg_match($this->compiledRegex, $string, $matches, 0, $offset)) {
                throw new \Phlexy\LexingException(sprintf(
                    'Unexpected character "%s" on line %d', $string[$offset], $line
                ));
            }

            $mark = $matches['MARK'];
            $text = $matches[0];
            if (\count($matches) > 2) {
                unset($matches[0], $matches['MARK']);
                $tokens[] = array($this->markToTokenMap[$mark], $line, $text, $matches);
            } else {
                $tokens[] = array($this->markToTokenMap[$mark], $line, $text);
            }

            $offset += \strlen($text);
            $line += substr_count($text, "\n");
        }

        return $tokens;
    }
}