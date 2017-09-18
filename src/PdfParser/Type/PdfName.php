<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   2.0.0-rc1
 */

namespace setasign\Fpdi\PdfParser\Type;

use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Tokenizer;

/**
 * Class representing a PDF name object
 *
 * @package setasign\Fpdi\PdfParser\Type
 */
class PdfName extends PdfType
{
    /**
     * Parses a name object from the passed tokenizer and stream-reader.
     *
     * @param Tokenizer $tokenizer
     * @param StreamReader $streamReader
     * @return self
     */
    public static function parse(Tokenizer $tokenizer, StreamReader $streamReader)
    {
        $v = new self;
        if (strspn($streamReader->getByte(), "\x00\x09\x0A\x0C\x0D\x20()<>[]{}/%") === 0) {
            $v->value = (string) $tokenizer->getNextToken();
            return $v;
        }

        $v->value = '';
        return $v;
    }

    /**
     * Helper method to create an instance.
     *
     * @param string $string
     * @return self
     */
    public static function create($string)
    {
        $v = new self;
        $v->value = $string;

        return $v;
    }

    /**
     * Ensures that the passed value is a PdfName instance.
     *
     * @param mixed $name
     * @return self
     */
    public static function ensure($name)
    {
        return PdfType::ensureType(self::class, $name, 'Name value expected.');
    }
}
