<?php

use Phlexy\Lexer;

error_reporting(E_ALL | E_STRICT);

require __DIR__ . '/../lib/Phlexy/bootstrap.php';

/*
 * Lexer definitions
 */

$cvsLexerDefinition = array(
    '[^",\r\n]+'                     => 0,
    '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"' => 1,
    ','                              => 2,
    '\r?\n'                          => 3,
);

$alphabet = range('a', 'z');
$alphabetLexerDefinition = array_combine($alphabet, $alphabet);

require __DIR__ . '/phpLexerDefinition.php';
$phpLexerDefinition = getPHPLexerDefinition();

/*
 * Test data
 */

$cvsString = trim(str_repeat('hallo world,foo bar,more foo,more bar,"rare , escape",some more,stuff' . "\n", 5000));

$allAString = str_repeat('a', 100000);
$allZString = str_repeat('z', 20000);

$randomString = '';
for ($i = 0; $i < 50000; ++$i) {
    $randomString .= $alphabet[mt_rand(0, count($alphabet) - 1)];
}

$phpCodeOfThisFile = file_get_contents(__FILE__);
$phpCodeOfTestAbstractFile = file_get_contents(__DIR__ . '/../test/Phlexy/Lexer/TestAbstract.php');

// Stateless
echo 'Timing lexing of CVS data:', "\n";
testPerformanceOfStatelessLexers($cvsString, $cvsLexerDefinition);
echo 'Timing alphabet lexing of all "a":', "\n";
testPerformanceOfStatelessLexers($allAString, $alphabetLexerDefinition);
echo 'Timing alphabet lexing of all "z":', "\n";
testPerformanceOfStatelessLexers($allZString, $alphabetLexerDefinition);
echo 'Timing alphabet lexing of random string:', "\n";
testPerformanceOfStatelessLexers($randomString, $alphabetLexerDefinition);

// Stateful
echo 'Timing PHP lexing of this file:', "\n";
testPerformanceOfStatefulLexers($phpCodeOfThisFile, $phpLexerDefinition, 'i');
echo 'Timing PHP lexing of larger TestAbstract file:', "\n";
testPerformanceOfStatefulLexers($phpCodeOfTestAbstractFile, $phpLexerDefinition, 'i');

function testPerformanceOfStatelessLexers($string, array $lexerDefinition, $additionalModifiers = '') {
    testPerformanceOfLexers(array(
        'Stateless\\Simple',
        'Stateless\\WithCapturingGroups',
        'Stateless\\WithoutCapturingGroups',
        'Stateless\\UsingPregReplace',
        'Stateless\\UsingMarks',
    ), $string, $lexerDefinition, $additionalModifiers);
}

function testPerformanceOfStatefulLexers($string, array $lexerDefinition, $additionalModifiers = '') {
    testPerformanceOfLexers(array(
        'Stateful\\Simple',
        'Stateful\\UsingCompiledRegex',
        'Stateful\\UsingMarks',
    ), $string, $lexerDefinition, $additionalModifiers);
}

function testPerformanceOfLexers(array $lexerTypes, $string, array $lexerDefinition, $additionalModifiers) {
    $dataGen = new \Phlexy\LexerDataGenerator;

    foreach ($lexerTypes as $lexerType) {
        $factoryName = 'Phlexy\\LexerFactory\\' . $lexerType;
        /** @var \Phlexy\LexerFactory $factory */
        $factory = new $factoryName($dataGen);
        testLexingPerformance($factory->createLexer($lexerDefinition, $additionalModifiers), $string);
    }

    echo "\n";
}

function testLexingPerformance(Lexer $lexer, $string) {
    $runs = 10;
    $startTime = microtime(true);
    for ($i = 0; $i < $runs; $i++) {
        $lexer->lex($string);
    }
    $endTime = microtime(true);

    echo 'Took ', $endTime - $startTime, ' seconds (', get_class($lexer), ')', "\n";
}
