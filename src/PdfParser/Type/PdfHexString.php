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

/**
 * Class representing a hexadecimal encoded PDF string object
 *
 * @package setasign\Fpdi\PdfParser\Type
 */
class PdfHexString extends PdfType
{
    /**
     * Parses a hexadecimal string object from the stream reader.
     *
     * @param StreamReader $streamReader
     * @return bool|self
     */
    public static function parse(StreamReader $streamReader)
    {
        $bufferOffset = $streamReader->getOffset();

        /**
         * @var string $buffer
         * @var int $pos
         */
        while (true) {
            $buffer = $streamReader->getBuffer(false);
            $pos = strpos($buffer, '>', $bufferOffset);
            if (false === $pos) {
                if (!$streamReader->increaseLength()) {
                    return false;
                }
                continue;
            }

            break;
        }

        $result = substr($buffer, $bufferOffset, $pos - $bufferOffset);
        $streamReader->setOffset($pos + 1);

        $v = new self;
        $v->value = $result;

        return $v;
    }

    /**
     * Helper method to create an instance.
     *
     * @param string $string The hex encoded string.
     * @return self
     */
    public static function create($string)
    {
        $v = new self;
        $v->value = $string;

        return $v;
    }

    /**
     * Ensures that the passed value is a PdfHexString instance.
     *
     * @param mixed $hexString
     * @return self
     */
    public static function ensure($hexString)
    {
        return PdfType::ensureType(self::class, $hexString, 'Hex string value expected.');
    }
}
