Phlexy
======

This project is a followup to [my post on fast lexing in PHP][lexing_blog_post]. It contains a few lexer implementations
(both stateless and stateful) and related performance tests.

Install
-------

To install with composer:

```sh
composer require nikic/phlexy
```

Usage
-----

Lexers are created from a lexer definition using a factory class.

For example, if you want to create a MARK based stateless CSV lexer, you can use the following code:

```php
<?php
require 'path/to/vendor/autoload.php';

$factory = new Phlexy\LexerFactory\Stateless\UsingMarks(
    new Phlexy\LexerDataGenerator
);

$lexer = $factory->createLexer(array(
    '[^",\r\n]+'                     => 0, // 0, 1, 2, 3 are the tokens
    '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"' => 1, // they should really be constants
    ','                              => 2,
    '\r?\n'                          => 3,
));

$tokens = $lexer->lex("hallo world,foo bar,more foo,more bar,\"rare , escape\",some more,stuff\n...");
```

Similarly a stateful lexer:

```php
<?php
require 'path/to/lib/Phlexy/bootstrap.php';

$factory = new Phlexy\LexerFactory\Stateful\UsingMarks(
    new Phlexy\LexerDataGenerator
);

// The "i" is an additional modifier (all createLexer methods accept it)
$lexer = $factory->createLexer($lexerDefinition, 'i');
```

For an example of a stateful lexer definition, you can look the [definition for lexing PHP source
code][php_lexer_definition].

Performance
-----------

A performance comparison for the different lexer implementations can be done using the [performance testing
script][performance_test_file]:

```
$ php-7.2 examples/performanceTests.php

Timing lexing of CVS data:
Took 0.55736708641052 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.526859998703 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.49272608757019 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.5570011138916 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.46333193778992 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing alphabet lexing of all "a":
Took 0.58650183677673 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.754310131073 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.70682787895203 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.76406478881836 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.62837815284729 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing alphabet lexing of all "z":
Took 0.79967403411865 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.30202317237854 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.29198718070984 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.36609601974487 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.12433409690857 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing alphabet lexing of random string:
Took 1.1720998287201 seconds (Phlexy\Lexer\Stateless\Simple)
Took 0.5946900844574 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
Took 0.55696296691895 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
Took 0.6708779335022 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)
Took 0.33155107498169 seconds (Phlexy\Lexer\Stateless\UsingMarks)

Timing PHP lexing of this file:
Took 0.151211977005 seconds (Phlexy\Lexer\Stateful\Simple)
Took 0.025480031967163 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)
Took 0.007037878036499 seconds (Phlexy\Lexer\Stateful\UsingMarks)

Timing PHP lexing of larger TestAbstract file:
Took 0.49794602394104 seconds (Phlexy\Lexer\Stateful\Simple)
Took 0.083348035812378 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)
Took 0.019592046737671 seconds (Phlexy\Lexer\Stateful\UsingMarks)
```

`Stateless\Simple` and `Stateful\Simple` are trivial lexer implementations (which loop through the regular expressions).

`Stateless\WithoutCapturingGroups`, `Stateless\WithCapturingGroups` and `Stateful\UsingCompiledRegex` use the compiled
regex approach described in the blog post mentioned above.

`Stateless\UsingPregReplace` is an extension of the compiled regex approach, where the looping through the regular
expression is done by (mis)using `preg_replace_callback`.

`Stateless\UsingMarks` and `Stateful\UsingMark` use the `(*MARK)` mechanism that was exposed in PHP 5.5.

As the above performance measurments show, the `Simple` approach is a good bit slower than using a compiled regex approach. Mark based implementation perform much better than group offset based ones. The benefits increase with lexer size: For the CSV lexer there is relatively little difference, while for the PHP lexer the mark based implementation is 25x faster than the naive one.

 [lexing_blog_post]: http://nikic.github.com/2011/10/23/Improving-lexing-performance-in-PHP.html
 [php_lexer_definition]: https://github.com/nikic/Phlexy/blob/master/examples/phpLexerDefinition.php
 [performance_test_file]: https://github.com/nikic/Phlexy/blob/master/examples/performanceTests.php
