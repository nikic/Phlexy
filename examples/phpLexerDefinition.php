<?php

use Phlexy\Lexer\Stateful;

function getPHPLexerDefinition() {
    $labelRegex = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';

    $binNumberRegex = '0b[01]+';
    $hexNumberRegex = '0x[0-9a-f]+';
    $decNumberRegex = '0|[1-9][0-9]*';
    $octNumberRegex = '0[0-9]+'; // 0-9 is intentional

    $scriptOpenTagRegexPart = 'script[ \n\r\t]+language[ \n\r\t]*=[ \n\r\t]*(?:php|"php"|\'php\')[ \n\r\t]*>';

    $sharedStringRegexes = array(
        '\$' . $labelRegex . '(?=\[)' => function(Stateful $lexer) {
            $lexer->pushState('VAR_OFFSET');

            return T_VARIABLE;
        },
        '\$' . $labelRegex . '(?=->[a-zA-Z_\x7f-\xff])' => function(Stateful $lexer) {
            $lexer->pushState('LOOKING_FOR_PROPERTY');

            return T_VARIABLE;
        },
        '\$' . $labelRegex => T_VARIABLE,
        '\$\{(?=' . $labelRegex . '[\[}])' => function(Stateful $lexer) {
            $lexer->pushState('LOOKING_FOR_VARNAME');

            return T_DOLLAR_OPEN_CURLY_BRACES;
        },
        '\$\{' => function(Stateful $lexer) {
            $lexer->pushState('IN_SCRIPTING');

            return T_DOLLAR_OPEN_CURLY_BRACES;
        },
        '\{(?=\$)' => function(Stateful $lexer) {
            $lexer->pushState('IN_SCRIPTING');

            return T_CURLY_OPEN;
        },
    );

    return array(
        'INITIAL' => array(
            '<\?=' => function(Stateful $lexer) {
                $lexer->swapState('IN_SCRIPTING');

                return T_OPEN_TAG_WITH_ECHO;
            },
            '<\?php(?:\r\n|[ \t\r\n])' => function(Stateful $lexer) {
                $lexer->swapState('IN_SCRIPTING');

                return T_OPEN_TAG;
            },
            '<' . $scriptOpenTagRegexPart => function(Stateful $lexer) {
                $lexer->swapState('IN_SCRIPTING');

                return T_OPEN_TAG;
            },
            '[^<]*(?:<(?!\?=|\?php[ \t\r\n]|' . $scriptOpenTagRegexPart . ')[^<]*)*' => T_INLINE_HTML,
        ),
        'IN_SCRIPTING' => array(
            '(?:\?>|</script[ \n\r\t]*>)(?:\r\n|\r|\n)?' => function(Stateful $lexer) {
                $lexer->swapState('INITIAL');

                return T_CLOSE_TAG;
            },
            '\{' => function(Stateful $lexer) {
                $lexer->pushState('IN_SCRIPTING');

                return '{';
            },
            '\}' => function(Stateful $lexer) {
                if ($lexer->hasPushedStates()) {
                    $lexer->popState();
                }

                return '}';
            },

            // the most important parts of the code
            '[ \n\r\t]+' => T_WHITESPACE,
            '\$' . $labelRegex => T_VARIABLE,

            // keywords
            '__class__\b'       => T_CLASS_C,
            '__dir__\b'         => T_DIR,
            '__file__\b'        => T_FILE,
            '__function__\b'    => T_FUNC_C,
            '__halt_compiler\b' => T_HALT_COMPILER,
            '__line__\b'        => T_LINE,
            '__method__\b'      => T_METHOD_C,
            '__namespace__\b'   => T_NS_C,
            '__trait__\b'       => T_TRAIT_C,
            'abstract\b'        => T_ABSTRACT,
            'and\b'             => T_LOGICAL_AND,
            'array\b'           => T_ARRAY,
            'as\b'              => T_AS,
            'break\b'           => T_BREAK,
            'callable\b'        => T_CALLABLE,
            'case\b'            => T_CASE,
            'catch\b'           => T_CATCH,
            'class\b'           => T_CLASS,
            'clone\b'           => T_CLONE,
            'const\b'           => T_CONST,
            'continue\b'        => T_CONTINUE,
            'declare\b'         => T_DECLARE,
            'default\b'         => T_DEFAULT,
            'die\b'             => T_EXIT,
            'do\b'              => T_DO,
            'echo\b'            => T_ECHO,
            'else\b'            => T_ELSE,
            'elseif\b'          => T_ELSEIF,
            'empty\b'           => T_EMPTY,
            'enddeclare\b'      => T_ENDDECLARE,
            'endfor\b'          => T_ENDFOR,
            'endforeach\b'      => T_ENDFOREACH,
            'endif\b'           => T_ENDIF,
            'endswitch\b'       => T_ENDSWITCH,
            'endwhile\b'        => T_ENDWHILE,
            'eval\b'            => T_EVAL,
            'exit\b'            => T_EXIT,
            'extends\b'         => T_EXTENDS,
            'final\b'           => T_FINAL,
            'for\b'             => T_FOR,
            'foreach\b'         => T_FOREACH,
            'function\b'        => T_FUNCTION,
            'global\b'          => T_GLOBAL,
            'goto\b'            => T_GOTO,
            'if\b'              => T_IF,
            'implements\b'      => T_IMPLEMENTS,
            'include\b'         => T_INCLUDE,
            'include_once\b'    => T_INCLUDE_ONCE,
            'instanceof\b'      => T_INSTANCEOF,
            'insteadof\b'       => T_INSTEADOF,
            'interface\b'       => T_INTERFACE,
            'isset\b'           => T_ISSET,
            'list\b'            => T_LIST,
            'namespace\b'       => T_NAMESPACE,
            'new\b'             => T_NEW,
            'or\b'              => T_LOGICAL_OR,
            'print\b'           => T_PRINT,
            'private\b'         => T_PRIVATE,
            'protected\b'       => T_PROTECTED,
            'public\b'          => T_PUBLIC,
            'require\b'         => T_REQUIRE,
            'require_once\b'    => T_REQUIRE_ONCE,
            'return\b'          => T_RETURN,
            'static\b'          => T_STATIC,
            'switch\b'          => T_SWITCH,
            'throw\b'           => T_THROW,
            'trait\b'           => T_TRAIT,
            'try\b'             => T_TRY,
            'unset\b'           => T_UNSET,
            'use\b'             => T_USE,
            'var\b'             => T_VAR,
            'while\b'           => T_WHILE,
            'xor\b'             => T_LOGICAL_XOR,

            // casts
            '\([ \t]*array[ \t]*\)'                 => T_ARRAY_CAST,
            '\([ \t]*bool(?:ean)?[ \t]*\)'          => T_BOOL_CAST,
            '\([ \t]*(?:real|double|float)[ \t]*\)' => T_DOUBLE_CAST,
            '\([ \t]*int(?:eger)?[ \t]*\)'           => T_INT_CAST,
            '\([ \t]*object[ \t]*\)'                => T_OBJECT_CAST,
            '\([ \t]*(?:string|binary)[ \t]*\)'     => T_STRING_CAST,
            '\([ \t]*unset[ \t]*\)'                 => T_UNSET_CAST,

            // comparison operators
            '===' => T_IS_IDENTICAL,
            '!==' => T_IS_NOT_IDENTICAL,
            '=='  => T_IS_EQUAL,
            '!='  => T_IS_NOT_EQUAL,
            '<>'  => T_IS_NOT_EQUAL,
            '>='  => T_IS_GREATER_OR_EQUAL,
            '<='  => T_IS_SMALLER_OR_EQUAL,

            // combined assignment operators
            '\+=' => T_PLUS_EQUAL,
            '-='  => T_MINUS_EQUAL,
            '\*=' => T_MUL_EQUAL,
            '/='  => T_DIV_EQUAL,
            '%='  => T_MOD_EQUAL,
            '\.='  => T_CONCAT_EQUAL,
            '&='  => T_AND_EQUAL,
            '\|=' => T_OR_EQUAL,
            '\^=' => T_XOR_EQUAL,
            '<<=' => T_SL_EQUAL,
            '>>=' => T_SR_EQUAL,

            // other operators
            '=>'   => T_DOUBLE_ARROW,
            '\\\\' => T_NS_SEPARATOR,
            '::'   => T_PAAMAYIM_NEKUDOTAYIM,
            '\|\|' => T_BOOLEAN_OR,
            '&&'   => T_BOOLEAN_AND,
            '<<'   => T_SL,
            '>>'   => T_SR,
            '--'   => T_DEC,
            '\+\+' => T_INC,

            // number literals (order of rules is important)
            '(?:[0-9]+\.[0-9]*|\.[0-9]+)(?:e[+-]?[0-9]+)?' => T_DNUMBER,
            '[0-9]+e[+-]?[0-9]+' => T_DNUMBER,
            $binNumberRegex => function(Stateful $lexer, $matches) {
                return is_int(bindec($matches[0])) ? T_LNUMBER : T_DNUMBER;
            },
            $hexNumberRegex => function(Stateful $lexer, $matches) {
                return is_int(hexdec($matches[0])) ? T_LNUMBER : T_DNUMBER;
            },
            $octNumberRegex => function(Stateful $lexer, $matches) {
                // cut octal number after invalid digit, just like PHP would do it
                $num = substr($matches[0], 0, strspn($matches[0], '01234567'));
                return is_int(octdec($num)) ? T_LNUMBER : T_DNUMBER;
            },
            $decNumberRegex => function(Stateful $lexer, $matches) {
                return $matches[0] <= PHP_INT_MAX ? T_LNUMBER : T_DNUMBER;
            },

            // comments
            '(?:#|//)[^\r\n?]*(?:\?(?!>)[^\r\n?]*)*(?:\r\n|\n|\r|(?=\?>)|$)' => T_COMMENT,
            '/\*(\*[ \n\r\t])?[^*]*(?:\*(?!/)[^*]*)*\*/' => function(Stateful $lexer, $matches) {
                return isset($matches[1]) ? T_DOC_COMMENT : T_COMMENT;
            },

            // strings
            'b?\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'' => T_CONSTANT_ENCAPSED_STRING,
            'b?\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*$' => T_ENCAPSED_AND_WHITESPACE, // unterminated string literal
            'b?"[^"\\\\${]*(?:(?:\\\\.|\$(?!\{|' . $labelRegex . ')|\{(?!\$))[^"\\\\${]*)*"' => T_CONSTANT_ENCAPSED_STRING,
            'b?"' => function(Stateful $lexer) {
                $lexer->swapState('DOUBLE_QUOTES');

                return '"';
            },
            '`' => function(Stateful $lexer) {
                $lexer->swapState('BACKTICKS');

                return '`';
            },
            /*'b?<<<[\t ]*\'(' . $labelRegex . ')\'(?>\r|\n|\r\n)' => function(Stateful $lexer) {
                $lexer->swapState('NOWDOC');

                return T_START_HEREDOC;
            },*/

            // labels have to come after keywords and strings (strings can have a "b" prefix)
            $labelRegex => T_STRING,

            '->(?=[ \n\r\t]*' . $labelRegex . ')' => function(Stateful $lexer) {
                $lexer->pushState('LOOKING_FOR_PROPERTY');

                return T_OBJECT_OPERATOR;
            },

            '->' => T_OBJECT_OPERATOR,

            // heavily order dependent; has to come at the end
            '[;:,.\[\]()|^&+-/*=%!~$<>?@]' => function(Stateful $lexer, $matches) {
                // token char itself is token
                return $matches[0];
            },

            // TODO

            // <ST_IN_SCRIPTING>b?"<<<"{TABS_AND_SPACES}({LABEL}|([']{LABEL}['])|(["]{LABEL}["])){NEWLINE}
            // <ST_END_HEREDOC>{ANY_CHAR}
            // <ST_HEREDOC>{ANY_CHAR}
            // <ST_NOWDOC>{ANY_CHAR}

            /* nowdoc regex
             *
             * (*BSR_ANYCRLF)        # set \R to (?>\r\n|\r|\n)
             * (b?<<<[\t ]*\'(' . $labelRegex . ')\'\R) # opening token
             * ((?:(?!\2;?\R).*\R)*) # content
             * (\2)                  # closing token
             * (?=;?\R)              # must be followed by newline (with optional semicolon)
             */
        ),
        'DOUBLE_QUOTES' => array_merge($sharedStringRegexes, array(
            '"' => function(Stateful $lexer) {
                $lexer->swapState('IN_SCRIPTING');

                return '"';
            },
            '[^"\\\\${]*(?:(?:\\\\.|\$(?!\{|' . $labelRegex . ')|\{(?!\$))[^"\\\\${]*)*' => T_ENCAPSED_AND_WHITESPACE,
        )),
        'BACKTICKS' => array_merge($sharedStringRegexes, array(
            '`' => function(Stateful $lexer) {
                $lexer->swapState('IN_SCRIPTING');

                return '`';
            },
            '[^`\\\\${]*(?:(?:\\\\.|\$(?!\{|' . $labelRegex . ')|\{(?!\$))[^`\\\\${]*)*' => T_ENCAPSED_AND_WHITESPACE,
        )),
        'HEREDOC' => array_merge($sharedStringRegexes, array(
            // TODO
        )),
        'VAR_OFFSET' => array(
            '\]' => function(Stateful $lexer) {
                $lexer->popState();

                return ']';
            },

            $binNumberRegex . '|' . $hexNumberRegex . '|' . $octNumberRegex . '|' . $decNumberRegex => T_NUM_STRING,

            '\$' . $labelRegex => T_VARIABLE,
            $labelRegex => T_STRING,

            '[;:,.\[()|^&+-/*=%!~$<>?@{}"`]' => function(Stateful $lexer, $matches) {
                // only [ can be valid, but returning other tokens for better error messages
                return $matches[0];
            },
            '[ \n\r\t\\\\\'#]' => function(Stateful $lexer) {
                $lexer->popState();

                throw new \Phlexy\RestartException;
            },
        ),
        'LOOKING_FOR_PROPERTY' => array(
            '->' => T_OBJECT_OPERATOR,
            $labelRegex => function(Stateful $lexer) {
                $lexer->popState();

                return T_STRING;
            },
            '[ \n\r\t]+' => T_WHITESPACE,
        ),
        'LOOKING_FOR_VARNAME' => array(
            $labelRegex . '(?=[\[}])' => function(Stateful $lexer) {
                $lexer->swapState('IN_SCRIPTING');

                return T_STRING_VARNAME;
            },
        ),
    );
}