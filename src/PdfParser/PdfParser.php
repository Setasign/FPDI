<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   2.0.0-rc1
 */

namespace setasign\Fpdi\PdfParser;

use setasign\Fpdi\PdfParser\CrossReference\CrossReference;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfBoolean;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfHexString;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObjectReference;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfToken;
use setasign\Fpdi\PdfParser\Type\PdfType;

/**
 * A PDF parser class
 *
 * @package setasign\Fpdi\PdfParser
 */
class PdfParser
{
    /**
     * @var StreamReader
     */
    protected $streamReader;

    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * The file header.
     *
     * @var string
     */
    protected $fileHeader;

    /**
     * The offset to the file header.
     *
     * @var int
     */
    protected $fileHeaderOffset;

    /**
     * @var CrossReference
     */
    protected $xref;

    /**
     * All read objects.
     *
     * @var array
     */
    protected $objects = [];

    /**
     * PdfParser constructor.
     *
     * @param StreamReader $streamReader
     */
    public function __construct(StreamReader $streamReader)
    {
        $this->streamReader = $streamReader;
        $this->tokenizer = new Tokenizer($streamReader);
    }

    /**
     * Get the stream reader instance.
     *
     * @return StreamReader
     */
    public function getStreamReader()
    {
        return $this->streamReader;
    }

    /**
     * Get the tokenizer instance.
     *
     * @return Tokenizer
     */
    public function getTokenizer()
    {
        return $this->tokenizer;
    }

    /**
     * Resolves the file header.
     *
     * @throws PdfParserException
     */
    protected function resolveFileHeader()
    {
        if ($this->fileHeader) {
            return $this->fileHeaderOffset;
        }

        $this->streamReader->reset(0);
        $offset = false;
        $maxIterations = 1000;
        while (true) {
            $buffer = $this->streamReader->getBuffer(false);
            $offset = strpos($buffer, '%PDF-');
            if (false === $offset) {
                if (!$this->streamReader->increaseLength(100) || (--$maxIterations === 0)) {
                    throw new PdfParserException(
                        'Unable to find PDF file header.',
                        PdfParserException::FILE_HEADER_NOT_FOUND
                    );
                }
                continue;
            }
            break;
        }

        $this->fileHeaderOffset = $offset;
        $this->streamReader->setOffset($offset);

        $this->fileHeader = trim($this->streamReader->readLine());
        return $this->fileHeaderOffset;
    }

    /**
     * Get the cross reference instance.
     *
     * @return CrossReference
     */
    public function getCrossReference()
    {
        if (null === $this->xref) {
            $this->xref = new CrossReference($this, $this->resolveFileHeader());
        }

        return $this->xref;
    }

    /**
     * Get the PDF version.
     *
     * @return int[] An array of major and minor version.
     * @throws PdfParserException
     */
    public function getPdfVersion()
    {
        $this->resolveFileHeader();

        if (preg_match('/%PDF-(\d)\.(\d)/', $this->fileHeader, $result) === 0) {
            throw new PdfParserException(
                'Unable to extract PDF version from file header.',
                PdfParserException::PDF_VERSION_NOT_FOUND
            );
        }
        list(, $major, $minor) = $result;

        $catalog = $this->getCatalog();
        if (isset($catalog->value['Version'])) {
            list($major, $minor) = explode('.', PdfType::resolve($catalog->value['Version'], $this)->value);
        }

        return [(int) $major, (int) $minor];
    }

    /**
     * Get the catalog dictionary.
     *
     * @return PdfDictionary
     */
    public function getCatalog()
    {
        $xref = $this->getCrossReference();
        $trailer = $xref->getTrailer();

        $catalog = PdfType::resolve(PdfDictionary::get($trailer, 'Root'), $this);

        return PdfDictionary::ensure($catalog);
    }

    /**
     * Get an indirect object by its object number.
     *
     * @param int $objectNumber
     * @param bool $cache
     * @return PdfIndirectObject
     */
    public function getIndirectObject($objectNumber, $cache = false)
    {
        $objectNumber = (int) $objectNumber;
        if (isset($this->objects[$objectNumber])) {
            return $this->objects[$objectNumber];
        }

        $xref = $this->getCrossReference();
        $object = $xref->getIndirectObject($objectNumber);

        if ($cache) {
            $this->objects[$objectNumber] = $object;
        }

        return $object;
    }

    /**
     * Read a PDF value.
     *
     * @param null|bool|string $token
     * @return bool|PdfArray|PdfBoolean|PdfHexString|PdfName|PdfNull|PdfNumeric|PdfString|PdfToken|PdfIndirectObjectReference
     */
    public function readValue($token = null)
    {
        if (null === $token) {
            $token = $this->tokenizer->getNextToken();
        }

        if (false === $token) {
            return false;
        }

        switch ($token) {
            case '(':
                return PdfString::parse($this->streamReader);

            case '<':
                if ($this->streamReader->getByte() === '<') {
                    $this->streamReader->addOffset(1);
                    return PdfDictionary::parse($this->tokenizer, $this->streamReader, $this);
                }

                return PdfHexString::parse($this->streamReader);

            case '/':
                return PdfName::parse($this->tokenizer, $this->streamReader);

            case '[':
                return PdfArray::parse($this->tokenizer, $this);

            default:
                if (is_numeric($token)) {
                    if (($token2 = $this->tokenizer->getNextToken()) !== false) {
                        if (is_numeric($token2)) {
                            if (($token3 = $this->tokenizer->getNextToken()) !== false) {
                                switch ($token3) {
                                    case 'obj':
                                        return PdfIndirectObject::parse(
                                            $token,
                                            $token2,
                                            $this,
                                            $this->tokenizer,
                                            $this->streamReader
                                        );
                                    case 'R':
                                        return PdfIndirectObjectReference::create($token, $token2);
                                }

                                $this->tokenizer->pushStack($token3);
                            }
                        }

                        $this->tokenizer->pushStack($token2);
                    }

                    return PdfNumeric::create($token);
                }

                if ('true' === $token || 'false' === $token) {
                    return PdfBoolean::create('true' === $token);
                }

                if ('null' === $token) {
                    return new PdfNull();
                }

                $v = new PdfToken();
                $v->value = $token;

                return $v;
        }
    }
}
