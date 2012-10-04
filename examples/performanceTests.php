<?php

/*
 * Sample output for this file:
 *
 * $ /c/php-5.4.1/php examples/performanceTests.php
 * Timing lexing of CVS data:
 * Took 0.33259892463684 seconds (Phlexy\Lexer\Stateless\Simple)
 * Took 0.28691792488098 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
 * Took 0.26784682273865 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
 * Took 0.22256088256836 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
 *
 * Timing alphabet lexing of all "a":
 * Took 0.30809283256531 seconds (Phlexy\Lexer\Stateless\Simple)
 * Took 0.40949702262878 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
 * Took 0.38628792762756 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
 * Took 0.31351900100708 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
 *
 * Timing alphabet lexing of all "z":
 * Took 0.62087893486023 seconds (Phlexy\Lexer\Stateless\Simple)
 * Took 0.23668503761292 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
 * Took 0.22538208961487 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
 * Took 0.18682312965393 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
 *
 * Timing alphabet lexing of random string:
 * Took 0.94398212432861 seconds (Phlexy\Lexer\Stateless\Simple)
 * Took 0.42041087150574 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
 * Took 0.40309715270996 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
 * Took 0.37058591842651 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
 *
 * Timing PHP lexing of this file:
 * Took 0.098251104354858 seconds (Phlexy\Lexer\Stateful\Simple)
 * Took 0.020735025405884 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)
 *
 * Timing PHP lexing of larger TestAbstract file:
 * Took 0.268701076507570 seconds (Phlexy\Lexer\Stateful\Simple)
 * Took 0.065788984298706 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)
 */

use Phlexy\Lexer;

error_reporting(E_ALL | E_STRICT);

require dirname(__FILE__) . '/../lib/Phlexy/bootstrap.php';

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