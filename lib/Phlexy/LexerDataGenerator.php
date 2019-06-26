<?php

namespace Phlexy;

class LexerDataGenerator {
    public function getCompiledRegex(array $regexes, string $additionalModifiers = ''): string {
        return '~(' . $this->escapeDelimiter(implode(')|(', $regexes)) . ')~A' . $additionalModifiers;
    }

    public function getCompiledRegexForPregReplace(array $regexes, string $additionalModifiers = ''): string {
        // the \G is not strictly necessary, but it makes preg_replace abort early when not lexable
        return '~\G((' . $this->escapeDelimiter(implode(')|(', $regexes)) . '))~' . $additionalModifiers;
    }

    public function getMarks(int $num): array {
        $marks = [];
        $mark = 'A';
        for ($i = 0; $i < $num; $i++) {
            $marks[] = $mark++;
        }
        return $marks;
    }

    public function getCompiledRegexWithMarks(array $regexes, array $marks, string $additionalModifiers = ''): string {
        $regexParts = array_map(function($regex, $mark) {
            return $regex . '(*MARK:' . $mark . ')';
        }, $regexes, $marks);
        return '~(?|' . $this->escapeDelimiter(implode('|', $regexParts)) . ')~A' . $additionalModifiers;
    }

    private function escapeDelimiter(string $regex): string {
        return str_replace('~', '\~', $regex);
    }

    public function getOffsetToLengthMap(array $regexes): array {
        $offsetToLengthMap = array();

        $currentOffset = 0;
        foreach ($regexes as $regex) {
            // We have to add +1 because the whole regex will also be made capturing
            $numberOfCapturingGroups = 1 + $this->getNumberOfCapturingGroupsInRegex($regex);

            $offsetToLengthMap[$currentOffset] = $numberOfCapturingGroups;
            $currentOffset += $numberOfCapturingGroups;
        }

        return $offsetToLengthMap;
    }

    protected function getNumberOfCapturingGroupsInRegex($regex): int {
        // The regex to count the number of capturing groups should be fairly complete. The only thing I know it
        // won't work with are (?| ... ) groups.
        return preg_match_all(
            '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                  | \\\\ .
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