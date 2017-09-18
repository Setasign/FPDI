<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   2.0.0-rc1
 */

namespace setasign\Fpdi\PdfParser\CrossReference;

use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfToken;

/**
 * Abstract class for cross-reference reader classes.
 *
 * @package setasign\Fpdi\PdfParser\CrossReference
 */
abstract class AbstractReader
{
    /**
     * @var PdfParser
     */
    protected $parser;

    /**
     * @var PdfDictionary
     */
    protected $trailer;

    /**
     * AbstractReader constructor.
     *
     * @param PdfParser $parser
     */
    public function __construct(PdfParser $parser)
    {
        $this->parser = $parser;
        $this->readTrailer();
    }

    /**
     * Get the trailer dictionary.
     *
     * @return PdfDictionary
     */
    public function getTrailer()
    {
        return $this->trailer;
    }

    /**
     * Read the trailer dictionary.
     *
     * @throws CrossReferenceException
     */
    protected function readTrailer()
    {
        $trailerKeyword = $this->parser->readValue();
        if ($trailerKeyword === false ||
            !($trailerKeyword instanceof PdfToken) ||
            $trailerKeyword->value !== 'trailer'
        ) {
            throw new CrossReferenceException(
                sprintf(
                    'Unexpected end of cross reference. "trailer"-keyword expected, got: %s',
                    $trailerKeyword instanceof PdfToken ? $trailerKeyword->value : 'nothing'
                ),
                CrossReferenceException::UNEXPECTED_END
            );
        }

        $trailer = $this->parser->readValue();
        if ($trailer === false || !($trailer instanceof PdfDictionary)) {
            throw new CrossReferenceException(
                'Unexpected end of cross reference. Trailer not found.',
                CrossReferenceException::UNEXPECTED_END
            );
        }

        $this->trailer = $trailer;
    }
}
