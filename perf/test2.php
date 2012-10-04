<?php

error_reporting(E_ALL | E_STRICT);

require __DIR__ . '/../lib/Phlexy/bootstrap.php';
require __DIR__ . '/phpLexerDefinition.php';

$factory = new \Phlexy\LexerFactory\Stateful\UsingCompiledRegex(
    new \Phlexy\LexerDataGenerator
);

$lexer = $factory->createLexer(getPHPLexerDefinition(), 'i');

$myTime = 0;
$phpTime = 0;

$tokenCounts = array();

$skip = 10;

//$dir = '../php-src';
$dir = '../Symfony';
foreach (
    new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::LEAVES_ONLY)
    as $file
) {
    //if (!preg_match('~\.phpt$~', $file)) {
    if (!preg_match('~\.php$~', $file)) {
        continue;
    }

    // the two bug tests are incorrectly lexed on php's side
    /*if (preg_match('~zend_multibyte|multibyte_encoding|bug21820|bug61681~', $file)) {
        echo 'S';
        continue;
    }*/

    $code = file_get_contents($file);

    // skip heredoc
    if (false !== strpos($code, '<<<')) {
        echo 'S';
        continue;
    }

    /*if (preg_match('/<\?(?!php|=)/', $code)) {
        echo 'S';
        continue;
    }

    if (false !== stripos($code, '__halt_compiler')) {
        echo 'S';
        continue;
    }*/

    //echo $file, "\n";

    try {
        $startTime = microtime(true);
        $myLex = $lexer->lex($code);
        $myTime += microtime(true) - $startTime;
    } catch (Exception $e) {
        echo "\n", 'Exception: ', $file, "\n";
        echo 'Message: ', $e->getMessage(), "\n";
        echo 'State stack: ', '[' . implode(', ', $lexer->getStateStack()) . ']';

        die;
        continue;
    }

    $startTime = microtime(true);
    $phpLex = token_get_all($code);
    $phpTime += microtime(true) - $startTime;

    foreach ($phpLex as $token) {
        if (!is_string($token)) {
            $token = $token[0];
        }

        if (!isset($tokenCounts[$token])) {
            $tokenCounts[$token] = 0;
        }

        ++$tokenCounts[$token];
    }

    if (convertLexToPHPFormat($myLex) !== $phpLex) {
        echo "\n", 'Differing: ', $file, "\n";

        if (--$skip == 0) {
            file_put_contents('myLex', print_r(convertLexToPHPFormat($myLex), true));
            file_put_contents('phpLex', print_r($phpLex, true));

            die;
        }
    } else {
        echo '.';
    }
}

arsort($tokenCounts);

echo "\n\n";
foreach ($tokenCounts as $token => $count) {
    echo (is_string($token) ? $token : token_name($token)) . ' => ', $count, "\n";
}
echo "\n";

var_dump($myTime, $phpTime);

function convertLexToPHPFormat(array $myLex) {
    $result = array();

    foreach ($myLex as $token) {
        if (is_string($token[0])) {
            $result[] = $token[2];
        } else {
            $result[] = array($token[0], $token[2], $token[1]);
        }
    }

    return $result;
}