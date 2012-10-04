<?php

use Phlexy\Lexer;

error_reporting(E_ALL | E_STRICT);

require dirname(__FILE__) . '/../lib/Phlexy/bootstrap.php';

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

require __DIR__ . '/phpLexerDefinition.php';
$phpLexerDefinition = getPHPLexerDefinition();
$phpThisFile = file_get_contents(__FILE__);
$phpTestAbstractFile = file_get_contents(__DIR__ . '/../test/Phlexy/Lexer/TestAbstract.php');

// Stateless
echo 'Timing lexing of CVS data:', "\n";
testPerformanceOfStatelessLexers($cvsData, $cvsRegexes);
echo 'Timing alphabet lexing of all "a":', "\n";
testPerformanceOfStatelessLexers($allAString, $alphabetRegexes);
echo 'Timing alphabet lexing of all "z":', "\n";
testPerformanceOfStatelessLexers($allZString, $alphabetRegexes);
echo 'Timing alphabet lexing of random string:', " '',\n";
testPerformanceOfStatelessLexers($randomString, $alphabetRegexes);

// Stateful
echo 'Timing PHP lexing of this file', "\n";
testPerformanceOfStatefulLexers($phpThisFile, $phpLexerDefinition, 'i');
echo 'Timing PHP lexing of larger TestAbstract file', "\n";
testPerformanceOfStatefulLexers($phpTestAbstractFile, $phpLexerDefinition, 'i');

function testPerformanceOfStatelessLexers($string, array $lexerDefinition, $additionalModifiers = '') {
    testPerformanceOfLexers(array(
        'Stateless\\Simple',
        'Stateless\\WithCapturingGroups',
        'Stateless\\WithoutCapturingGroups',
        'Stateless\\UsingPregReplace',
    ), $string, $lexerDefinition, $additionalModifiers);
}

function testPerformanceOfStatefulLexers($string, array $lexerDefinition, $additionalModifiers = '') {
    testPerformanceOfLexers(array(
        'Stateful\\Simple',
        'Stateful\\UsingCompiledRegex',
    ), $string, $lexerDefinition, $additionalModifiers);
}

function testPerformanceOfLexers(array $lexerTypes, $string, array $lexerDefinition, $additionalModifiers) {
    $dataGen = new \Phlexy\LexerDataGenerator;

    foreach ($lexerTypes as $lexerType) {
        $factoryName = 'Phlexy\\LexerFactory\\' . $lexerType;
        $factory = new $factoryName($dataGen);
        testLexingPerformance($factory->createLexer($lexerDefinition, $additionalModifiers), $string);
    }

    echo "\n";
}

function testLexingPerformance(Lexer $lexer, $string) {
    $startTime = microtime(true);
    $lexer->lex($string);
    $endTime = microtime(true);

    echo 'Took ', $endTime - $startTime, ' seconds (', get_class($lexer), ')', "\n";
}