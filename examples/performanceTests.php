<?php

/* Sample output for this file (PHP 7.2):

Timing lexing of CVS data:
Took 0.54317283630371 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.52546691894531 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.47881102561951 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.56563687324524 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

Timing alphabet lexing of all "a":
Took 0.57232809066772 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.75582599639893 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.71053194999695 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.77658700942993 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

Timing alphabet lexing of all "z":
Took 0.79460787773132 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.30368900299072 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.28874707221985 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.37151384353638 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

Timing alphabet lexing of random string:
Took 1.1753499507904 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.59115791320801 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.55604815483093 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.68863797187805 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

Timing PHP lexing of this file:
Took 0.14126992225647 seconds (Phlexy\Lexer\Stateful\Simple)
Took 0.025708198547363 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)

Timing PHP lexing of larger TestAbstract file:
Took 0.45643091201782 seconds (Phlexy\Lexer\Stateful\Simple)
Took 0.080940961837769 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)

*/

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
