<?php

/* Sample output for this file (PHP 7.2):

Timing lexing of CVS data:
Took 0.53451085090637 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.5123028755188 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.47754406929016 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.56304383277893 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.45579981803894 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing alphabet lexing of all "a":
Took 0.56700110435486 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.73676705360413 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.68615889549255 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.74947309494019 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.62207102775574 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing alphabet lexing of all "z":
Took 0.78618907928467 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.29536390304565 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.2872040271759 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.35811686515808 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.12243986129761 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing alphabet lexing of random string:
Took 1.1390540599823 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.579421043396 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.54870915412903 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.67329716682434 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.32394981384277 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing PHP lexing of this file:
Took 0.15059280395508 seconds (Phlexy\Lexer\Stateful\Simple)
Took 0.025473117828369 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)

Timing PHP lexing of larger TestAbstract file:
Took 0.45711994171143 seconds (Phlexy\Lexer\Stateful\Simple)
Took 0.082152843475342 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)

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
        'Stateless\\UsingMarks',
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
