<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\Fpdi\PdfParser;

/**
 * Class UnsupportedException
 *
 * @package setasign\Fpdi\PdfParser
 */
class UnsupportedException extends PdfParserException
{
    /**
     * @var int
     */
    const ENCRYPTED = 0x0601;

    /**
     * @var int
     */
    const IMPLEMENTED_IN_FPDI_PDF_PARSER = 0x0602;

    /**
     * @var int
     */
    const COMPRESSED_XREF = 0x0603;
}