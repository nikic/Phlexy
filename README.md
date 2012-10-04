Phlexy
======

This project is a followup to [my post on fast lexing in PHP][lexing_blog_post]. It contains a few lexer implementations
(both stateless and stateful) and related performance tests.

Usage
-----

Lexers are created from a lexer definition using a factory class.

For example, if you want to create a `preg_replace` based stateless CSV lexer, you can use the following code:

```php
<?php
require 'path/to/lib/Phlexy/bootstrap.php';

$factory = new Phlexy\LexerFactory\Stateless\UsingPregReplace(
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

    $ /c/php-5.4.1/php examples/performanceTests.php

    Timing lexing of CVS data:
    Took 0.33259892463684 seconds (Phlexy\Lexer\Stateless\Simple)
    Took 0.28691792488098 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
    Took 0.26784682273865 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
    Took 0.22256088256836 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

    Timing alphabet lexing of all "a":
    Took 0.30809283256531 seconds (Phlexy\Lexer\Stateless\Simple)
    Took 0.40949702262878 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
    Took 0.38628792762756 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
    Took 0.31351900100708 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

    Timing alphabet lexing of all "z":
    Took 0.62087893486023 seconds (Phlexy\Lexer\Stateless\Simple)
    Took 0.23668503761292 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
    Took 0.22538208961487 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
    Took 0.18682312965393 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

    Timing alphabet lexing of random string:
    Took 0.94398212432861 seconds (Phlexy\Lexer\Stateless\Simple)
    Took 0.42041087150574 seconds (Phlexy\Lexer\Stateless\WithCapturingGroups)
    Took 0.40309715270996 seconds (Phlexy\Lexer\Stateless\WithoutCapturingGroups)
    Took 0.37058591842651 seconds (Phlexy\Lexer\Stateless\UsingPregReplace)

    Timing PHP lexing of this file:
    Took 0.098251104354858 seconds (Phlexy\Lexer\Stateful\Simple)
    Took 0.020735025405884 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)

    Timing PHP lexing of larger TestAbstract file:
    Took 0.268701076507570 seconds (Phlexy\Lexer\Stateful\Simple)
    Took 0.065788984298706 seconds (Phlexy\Lexer\Stateful\UsingCompiledRegex)

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