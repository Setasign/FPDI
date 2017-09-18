<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   2.0.0-rc1
 */

namespace setasign\Fpdi\PdfReader;

use setasign\Fpdi\FpdiException;

/**
 * Exception for the pdf reader class
 *
 * @package setasign\Fpdi\PdfReader
 */
class PdfReaderException extends FpdiException
{
    /**
     * @var int
     */
    const KIDS_EMPTY = 0x0101;

    /**
     * @var int
     */
    const UNEXPECTED_DATA_TYPE = 0x0102;

    /**
     * @var int
     */
    const MISSING_DATA = 0x0103;
}
