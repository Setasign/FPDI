<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
  */

namespace setasign\Fpdi\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\UnsupportedException;

/**
 * Exception used by the CrossReference and Reader classes.
 *
 * @package setasign\Fpdi\PdfParser\CrossReference
 */
class CrossReferenceException extends PdfParserException
{
    /**
     * @var int
     */
    const INVALID_DATA = 0x0101;

    /**
     * @var int
     */
    const XREF_MISSING = 0x0102;

    /**
     * @var int
     */
    const ENTRIES_TOO_LARGE = 0x0103;

    /**
     * @var int
     */
    const ENTRIES_TOO_SHORT = 0x0104;

    /**
     * @var int
     */
    const NO_ENTRIES = 0x0105;

    /**
     * @var int
     */
    const NO_TRAILER_FOUND = 0x0106;

    /**
     * @var int
     */
    const NO_STARTXREF_FOUND = 0x0107;

    /**
     * @var int
     */
    const NO_XREF_FOUND = 0x0108;

    /**
     * @var int
     */
    const UNEXPECTED_END = 0x0109;

    /**
     * @var int
     */
    const OBJECT_NOT_FOUND = 0x010A;

    /**
     * @var int
     * @deprecated See UnsupportedException exception.
     */
    const COMPRESSED_XREF = UnsupportedException::COMPRESSED_XREF;

    /**
     * @var int
     * @deprecated See UnsupportedException exception.
     */
    const ENCRYPTED = UnsupportedException::ENCRYPTED;
}
