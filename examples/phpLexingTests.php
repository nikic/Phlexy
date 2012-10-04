<?php

/**
 * Sample outputs for this file:
 *
 * $ /c/php-5.4.1/php examples/phpLexingTests.php php-src-tests ../php-src
 *
 * Lexer took: 67.417944669724 seconds.
 * token_get_all took: 1.693163394928 seconds.
 *
 * $ /c/php-5.4.1/php examples/phpLexingTests.php code ../Symfony
 *
 * Lexer took: 53.443058013916 seconds.
 * token_get_all took: 1.6169309616089 seconds.
 *
 * Yes, it's dead slow compared to C :/
 */

error_reporting(E_ALL | E_STRICT);

require __DIR__ . '/../lib/Phlexy/bootstrap.php';
require __DIR__ . '/phpLexerDefinition.php';

if ($argc !== 3) {
    showHelp('Invalid argument count.');
}

$testType = $argv[1];
$testFilesDirectory = $argv[2];

if ($testType === 'php-src-tests') {
    $codes = getCodes($testFilesDirectory, 'PhpTestFileFilterIterator', 'CodeFilterIterator');
} elseif ($testType === 'code') {
    $codes = getCodes($testFilesDirectory, 'PhpFileFilterIterator', 'CodeFilterIterator');
} else {
    showHelp('Invalid test type.');
}

$factory = new \Phlexy\LexerFactory\Stateful\UsingCompiledRegex(
    new \Phlexy\LexerDataGenerator
);

$lexer = $factory->createLexer(getPHPLexerDefinition(), 'i');

$myTime = 0;
$phpTime = 0;

foreach ($codes as $code) {
    try {
        $startTime = microtime(true);
        $myLex = $lexer->lex($code);
        $myTime += microtime(true) - $startTime;
    } catch (Exception $e) {
        echo "\n", 'Exception: ', $codes->getPathName(), "\n";
        echo 'Message: ', $e->getMessage(), "\n";
        echo 'State stack: ', '[' . implode(', ', $lexer->getStateStack()) . ']';

        die;
    }

    $startTime = microtime(true);
    $phpLex = token_get_all($code);
    $phpTime += microtime(true) - $startTime;

    $myLexInPHPFormat = convertLexToPHPFormat($myLex);
    if ($myLexInPHPFormat !== $phpLex) {
        echo "\n", 'Differing: ', $codes->getPathName(), "\n";

        file_put_contents('myLex', print_r($myLexInPHPFormat, true));
        file_put_contents('phpLex', print_r($phpLex, true));

        die;
    } else {
        echo '.';
    }
}

echo "\n\n";

echo 'Lexer took: ', $myTime, " seconds.\n";
echo 'token_get_all took: ', $phpTime, " seconds.\n";

function showHelp($error) {
    die($error . "\n\n" .
        <<<OUTPUT
This script has to be called with the following signature:

    php phpLexingTests.php testType pathToTestFiles

The test type can be either "php-src-tests" or "code".
OUTPUT
    );
}

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

function getCodes($directory, $fileFilterIteratorClass, $codeFilterIteratorClass) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    $filteredFiles = new $fileFilterIteratorClass($files);
    $codes = new FileGetContentsIterator($filteredFiles);
    $filteredCodes = new $codeFilterIteratorClass($codes);

    return $filteredCodes;
}

class FileGetContentsIterator extends IteratorIterator {
    public function current() {
        return file_get_contents($this->getInnerIterator()->current());
    }
}

class PhpFileFilterIterator extends FilterIterator {
    public function accept() {
        $fileName = $this->current();
        return preg_match('~\.php$~', $fileName);
    }
}

class PhpTestFileFilterIterator extends FilterIterator {
    public function accept() {
        $fileName = $this->current();
        return preg_match('~\.phpt$~', $fileName)
            // the two bug tests are incorrectly lexed on php's side
            && !preg_match('~zend_multibyte|multibyte_encoding|bug21820|bug61681~', $fileName);
    }
}

class CodeFilterIterator extends FilterIterator {
    public function accept() {
        $code = $this->current();

        // skip heredoc/nowdoc
        if (false !== strpos($code, '<<<')) {
            return false;
        }

        // skip short open tags
        if (preg_match('/<\?(?!php|=)/', $code)) {
            return false;
        }

        // skip halt compiler
        if (false !== stripos($code, '__halt_compiler')) {
            return false;
        }

        return true;
    }
}