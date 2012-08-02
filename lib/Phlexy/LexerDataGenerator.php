<?php

namespace Phlexy;

class LexerDataGenerator {
    public function getAllRegexesCompiledIntoOne($regexes) {
        return '~(' . str_replace('~', '\~', implode(')|(', $regexes)) . ')~A';
    }

    public function getNumberOfCapturingGroupsInRegex($regex) {
        // The regex to count the number of capturing groups should be fairly complete. The only thing I know it
        // won't work with are (?| ... ) groups.
        return preg_match_all(
            '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                ) (*SKIP)(*FAIL) |
                \(
                (?!
                    \?
                    (?!
                        <(?![!=])
                      | P<
                      | \'
                    )
                  | \*
                )
            ~x',
            $regex, $dummyVar
        );
    }
}