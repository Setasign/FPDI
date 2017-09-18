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

/**
 * Class representing an indirect object reference
 *
 * @package setasign\Fpdi\PdfParser\Type
 */
class PdfIndirectObjectReference extends PdfType
{
    /**
     * Helper method to create an instance.
     *
     * @param int $objectNumber
     * @param int $generationNumber
     * @return self
     */
    public static function create($objectNumber, $generationNumber)
    {
        $v = new self;
        $v->value = (int) $objectNumber;
        $v->generationNumber = (int) $generationNumber;

        return $v;
    }

    /**
     * Ensures that the passed value is a PdfIndirectObject instance.
     *
     * @param mixed $value
     * @return self
     */
    public static function ensure($value)
    {
        return PdfType::ensureType(self::class, $value, 'Indirect reference value expected.');
    }

    /**
     * The generation number.
     *
     * @var int
     */
    public $generationNumber;
}
