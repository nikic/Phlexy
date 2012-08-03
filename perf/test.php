<?php

use Phlexy\Lexer;

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

function testPerformanceOfAllLexers(array $regexToTokenMap, $string) {
    $lexerDataGenerator = new \Phlexy\LexerDataGenerator;

    list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
        = $lexerDataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

    testLexingPerformance(new Lexer\Simple($regexToTokenMap), $string);
    testLexingPerformance(new Lexer\WithCapturingGroups($compiledRegex, $offsetToTokenMap, $offsetToLengthMap), $string);
    testLexingPerformance(new Lexer\WithoutCapturingGroups($compiledRegex, $offsetToTokenMap), $string);

    $lexer = generateLexer($regexToTokenMap);
    testLexingPerformance($lexer, $string);

    echo "\n";
}

function testLexingPerformance(Lexer $lexer, $string) {
    $startTime = microtime(true);
    $lexer->lex($string);
    $endTime = microtime(true);

    echo 'Took ', $endTime - $startTime, ' seconds (', get_class($lexer), ')', "\n";
}

function generateLexer(array $regexToTokenMap) {
    $codeGen = new Phlexy\CodeGenerator;
    $dataGenerator = new Phlexy\LexerDataGenerator;

    list($compiledRegex, $offsetToTokenMap, $offsetToLengthMap)
        = $dataGenerator->getDataFromRegexToTokenMap($regexToTokenMap);

    $offsets = array_keys($offsetToTokenMap);
    $tokens = array_values($offsetToTokenMap);
    $actions = range(0, count($regexToTokenMap) - 1);

    $offsetToActionMap = array_combine($offsets, $actions);

    $compiledRegexString = $codeGen->makeString($compiledRegex);
    $offsetToActionMapArray = $codeGen->makeArray($offsetToActionMap);
    $dispatchCode = $codeGen->indent(
        generateBinaryDispatchCode($codeGen, $tokens, 0, count($tokens) - 1),
        3
    );

    $name = 'GeneratedLexer_' . uniqid();

    $code = <<<CODE
class $name implements \Phlexy\Lexer {
    protected \$compiledRegex = $compiledRegexString;
    protected \$offsetToActionMap = $offsetToActionMapArray;

    public function lex(\$string) {
        \$tokens = array();

        \$offset = 0;
        \$line = 1;
        while (isset(\$string[\$offset])) {
            if (!preg_match(\$this->compiledRegex, \$string, \$matches, 0, \$offset)) {
                throw new \Phlexy\LexingException(sprintf(
                    'Unexpected character "%s" on line %d', \$string[\$offset], \$line
                ));
            }

            // find the first non-empty element (but skipping \$matches[0]) using a quick for loop
            for (\$i = 1; '' === \$matches[\$i]; ++\$i);

            \$action = \$this->offsetToActionMap[\$i - 1];
$dispatchCode

            \$tokens[] = \$token;

            \$offset += strlen(\$matches[0]);
            \$line += substr_count("\n", \$matches[0]);
        }

        return \$tokens;
    }
}
CODE;

    eval($code);

    return new $name;
}

function generateBinaryDispatchCode(Phlexy\CodeGenerator $codeGen, array $tokens, $start, $end) {
    if ($end == $start) {
        return '$token = array(' . $codeGen->makeValue($tokens[$start]) . ', $line);';
    } else if ($end - $start == 1) {
        return $codeGen->makeIf(
            '$action === ' . $start,
            '$token = array(' . $codeGen->makeValue($tokens[$start]) . ', $line);',
            '$token = array(' . $codeGen->makeValue($tokens[$end]) . ', $line);'
        );
    } else {
        $middle = $start + (int) ceil(($end - $start) / 2);

        return $codeGen->makeIf(
            '$action < ' . $middle,
            generateBinaryDispatchCode($codeGen, $tokens, $start, $middle - 1),
            generateBinaryDispatchCode($codeGen, $tokens, $middle, $end)
        );
    }
}