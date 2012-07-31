<?php

error_reporting(E_ALL | E_STRICT);

require dirname(__FILE__) . '/../lib/Phlexy/bootstrap.php';

if (php_sapi_name() != 'cli') echo '<pre>';

$cvsRegexes = array(
    '[^",\r\n]+'                     => 0,
    '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"' => 1,
    ','                              => 2,
    '\r?\n'                          => 3,
);

$cvsData = trim(str_repeat('hallo world,foo bar,more foo,more bar,"rare , escape",some more,stuff' . "\n", 5000));

$alphabet = range('a', 'z');
$alphabetRegexes = array_combine($alphabet, $alphabet);

$allAString = str_repeat('a', 100000);
$allZString = str_repeat('z', 20000);

$randomString = '';
for ($i = 0; $i < 50000; ++$i) {
    $randomString .= $alphabet[mt_rand(0, count($alphabet) - 1)];
}

echo 'Timing lexing of CVS data:', "\n";
testPerformanceOfAllLexers($cvsRegexes, $cvsData);
echo 'Timing alphabet lexing of all "a":', "\n";
testPerformanceOfAllLexers($alphabetRegexes, $allAString);
echo 'Timing alphabet lexing of all "z":', "\n";
testPerformanceOfAllLexers($alphabetRegexes, $allZString);
echo 'Timing alphabet lexing of random string:', "\n";
testPerformanceOfAllLexers($alphabetRegexes, $randomString);

function testPerformanceOfAllLexers(array $regexToToken, $string) {
    testLexingPerformance('SimpleLexer', $regexToToken, $string);
    testLexingPerformance('Phlexy_Lexer', $regexToToken, $string);
    testLexingPerformance('CompilingLexerWithoutCapturingGroups', $regexToToken, $string);
    echo "\n";
}

function testLexingPerformance($lexerClassName, array $regexToToken, $string) {
    $lexer = new $lexerClassName($regexToToken);

    $startTime = microtime(true);
    $lexer->lex($string);
    $endTime = microtime(true);

    echo 'Took ', $endTime - $startTime, ' seconds (', $lexerClassName, ')', "\n";
}

class SimpleLexer {
    protected $regexToToken;

    public function __construct(array $regexToToken) {
        $this->regexToToken = array();
        foreach ($regexToToken as $regex => $token) {
            $this->regexToToken['~' . str_replace('~', '\~', $regex) . '~A'] = $token;
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

                $tokens[] = array_merge(array($token, $line), $matches);

                $offset += strlen($matches[0]);
                $line += substr_count("\n", $matches[0]);

                continue 2;
            }

            throw new Phlexy_LexingException(sprintf('Unexpected character "%s"', $string[$offset]));
        }

        return $tokens;
    }
}

class CompilingLexerWithoutCapturingGroups {
    protected $regex;
    protected $offsetToToken;

    public function __construct(array $regexToToken) {
        $this->regex = '~(' . str_replace('~', '\~', implode(')|(', array_keys($regexToToken))) . ')~A';

        $this->offsetToToken = array_values($regexToToken);
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

            $tokens[] = array($matches[0], $this->offsetToToken[$i - 1]);

            $offset += strlen($matches[0]);
            $line += substr_count("\n", $matches[0]);
        }

        return $tokens;
    }
}