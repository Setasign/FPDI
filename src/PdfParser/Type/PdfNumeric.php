<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   2.0.0-alpha
 */

namespace setasign\Fpdi\PdfParser\Type;

class PdfNumeric extends PdfType
{
    public static function create($value)
    {
        $v = new self;
        $v->value = $value + 0;

        return $v;
    }

    public static function ensure($value)
    {
        return PdfType::ensureType(self::class, $value, 'Numeric value expected.');
    }
}
