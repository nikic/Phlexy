<?php

namespace Phlexy;

class CodeGenerator {
    protected $indentationString;

    public function __construct($indentationString = '    ') {
        $this->indentationString = $indentationString;
    }

    public function makeValue($value) {
        if (null === $value) {
            return 'null';
        } elseif (is_bool($value)) {
            return $this->makeBool($value);
        } elseif (is_int($value)) {
            return $this->makeInt($value);
        } elseif (is_float($value)) {
            return $this->makeFloat($value);
        } elseif (is_string($value)) {
            return $this->makeString($value);
        } elseif (is_array($value)) {
            return $this->makeArray($value);
        } else {
            throw new \RuntimeException(sprintf('Cannot PHP-ize value of type %s', gettype($value)));
        }
    }

    public function makeBool($bool) {
        return $bool ? 'true' : 'false';
    }

    public function makeInt($int) {
        return (string) $int;
    }

    public function makeFloat($float) {
        // NaN and Inf are printed incorrectly by printf
        if (is_nan($float)) {
            return 'NAN';
        } elseif ($float === -INF) {
            return '-INF';
        }

        // casting to string is locale aware, so we use the local-unaware %F printf format
        return sprintf('%F', $float);
    }

    public function makeString($string) {
        return "'" . str_replace(array("\\", "'"), array("\\\\", "\\'"), $string) . "'";
    }

    public function makeArray(array $array) {
        $printedElements = array();

        if ($this->isContinuousNumericArray($array)) {
            foreach ($array as $value) {
                $printedElements []= $this->makeValue($value);
            }
        } else {
            foreach ($array as $key => $value) {
                $printedElements []= $this->makeValue($key) . ' => ' . $this->makeValue($value);
            }
        }

        return 'array(' . implode(', ', $printedElements) . ')';
    }

    protected function isContinuousNumericArray(array $array) {
        $currentIndex = null;
        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return false;
            }

            if ($currentIndex !== null && $key !== $currentIndex + 1) {
                return false;
            }

            $currentIndex = $key;
        }

        return true;
    }

    public function indent($string, $level = 1) {
        $lines = explode("\n", $string);

        foreach ($lines as &$line) {
            $line = str_repeat($this->indentationString, $level) . $line;
        }

        return implode("\n", $lines);
    }

    public function makeBraces($code) {
        return '{' . "\n" . $this->indent($code) . "\n" . '}';
    }

    public function makeIf($cond, $if, $else = '') {
        return 'if (' . $cond . ') ' . $this->makeBraces($if)
            . ($else ? ' else ' . $this->makeBraces($else) : '');
    }
}