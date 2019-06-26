Phlexy
======

This project is a followup to [my post on fast lexing in PHP][lexing_blog_post]. It contains a few lexer implementations
(both stateless and stateful) and related performance tests.

Usage
-----

Lexers are created from a lexer definition using a factory class.

For example, if you want to create a MARK based stateless CSV lexer, you can use the following code:

```php
<?php
require 'path/to/lib/Phlexy/bootstrap.php';

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

$factory = new Phlexy\LexerFactory\Stateful\UsingCompiledRegex(
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
```


`Stateless\Simple` and `Stateful\Simple` are trivial lexer implementations (which loop through the regular expressions).

`Stateless\WithoutCapturingGroups`, `Stateless\WithCapturingGroups` and `Stateful\UsingCompiledRegex` use the compiled
regex approach described in the blog post mentioned above.

`Stateless\UsingPregReplace` is an extension of the compiled regex approach, where the looping through the regular
expression is done by (mis)using `preg_replace_callback`.

As the above performance measurments show, the `Simple` approach is a good bit slower than using compiled regexes. For
the CVS data it's only 1.17 times faster, but the difference significantly increases the more regular expressions there
are. E.g. lexing of the alphabet on a random string is more than twice as fast. For lexing PHP the compiled approach
is five times as fast.

The `preg_replace` trick makes the whole thing another bit faster. Sadly `preg_replace` can't be used for stateful
lexers, at least I couldn't figure out a fast way to do the state transitions.

 [lexing_blog_post]: http://nikic.github.com/2011/10/23/Improving-lexing-performance-in-PHP.html
 [php_lexer_definition]: https://github.com/nikic/Phlexy/blob/master/examples/phpLexerDefinition.php
 [performance_test_file]: https://github.com/nikic/Phlexy/blob/master/examples/performanceTests.php