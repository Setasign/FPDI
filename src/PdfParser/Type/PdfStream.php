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

use setasign\Fpdi\PdfParser\Filter\Ascii85;
use setasign\Fpdi\PdfParser\Filter\AsciiHex;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\Filter\Flate;
use setasign\Fpdi\PdfParser\Filter\Lzw;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\FpdiPdfParser\PdfParser\Filter\Predictor;

/**
 * Class representing a PDF stream object
 *
 * @package setasign\Fpdi\PdfParser\Type
 */
class PdfStream extends PdfType
{
    /**
     * Parses a stream from a stream reader.
     *
     * @param PdfDictionary $dictionary
     * @param StreamReader $reader
     * @return self
     * @throws PdfTypeException
     */
    public static function parse(PdfDictionary $dictionary, StreamReader $reader)
    {
        $v = new self;
        $v->value = $dictionary;
        $v->reader = $reader;

        $offset = $reader->getOffset();

        // Find the first "newline"
        while (($firstByte = $reader->getByte($offset)) !== false) {
            if ($firstByte !== "\n" && $firstByte !== "\r") {
                $offset++;
            } else {
                break;
            }
        }

        if (false === $firstByte) {
            throw new PdfTypeException(
                'Unable to parse stream data. No newline after the stream keyword found.',
                PdfTypeException::NO_NEWLINE_AFTER_STREAM_KEYWORD
            );
        }

        $sndByte = $reader->getByte($offset + 1);
        if ($firstByte === "\n" || $firstByte === "\r") {
            $offset++;
        }

        if ($sndByte === "\n" && $firstByte !== "\n") {
            $offset++;
        }

        $reader->setOffset($offset);
        // let's only save the byte-offset and read the stream only when needed
        $v->stream = $reader->getPosition() + $reader->getOffset();

        return $v;
    }

    /**
     * Helper method to create an instance.
     *
     * @param PdfDictionary $dictionary
     * @param string $stream
     * @return self
     */
    public static function create(PdfDictionary $dictionary, $stream)
    {
        $v = new self;
        $v->value = $dictionary;
        $v->stream = (string) $stream;

        return $v;
    }

    /**
     * Ensures that the passed value is a PdfStream instance.
     *
     * @param mixed $stream
     * @return self
     */
    public static function ensure($stream)
    {
        return PdfType::ensureType(self::class, $stream, 'Stream value expected.');
    }

    /**
     * The stream or its byte-offset position.
     *
     * @var int|string
     */
    protected $stream;

    /**
     * The stream reader instance.
     *
     * @var StreamReader
     */
    protected $reader;

    /**
     * Get the stream data.
     *
     * @param bool $cache Whether cache the stream data or not.
     * @return bool|string
     */
    public function getStream($cache = false)
    {
        if (is_int($this->stream)) {
            $length = PdfDictionary::get($this->value, 'Length');
            $this->reader->reset($this->stream, $length->value);
            if (!($length instanceof PdfNumeric) || $length->value === 0) {
                while (true) {
                    $buffer = $this->reader->getBuffer(false);
                    $length = strpos($buffer, 'endstream');
                    if (false === $length) {
                        if (!$this->reader->increaseLength(100000)) {
                            return false;
                        }
                        continue;
                    }
                    break;
                }

                $buffer = substr($buffer, 0, $length);
                $lastByte = substr($buffer, -1);

                // Check for EOL
                if ($lastByte === "\n") {
                    $buffer = substr($buffer, 0, -1);
                }

                $lastByte = substr($buffer, -1);
                if ($lastByte === "\r") {
                    $buffer = substr($buffer, 0, -1);
                }

            } else {
                $buffer = $this->reader->getBuffer(false);
            }
            if ($cache === false) {
                return $buffer;
            }

            $this->stream = $buffer;
            $this->reader = null;
        }

        return $this->stream;
    }

    /**
     * Get the unfiltered stream data.
     *
     * @return string
     * @throws FilterException
     * @throws PdfParserException
     */
    public function getUnfilteredStream()
    {
        $stream = $this->getStream();
        $filters = PdfDictionary::get($this->value, 'Filter');
        if ($filters instanceof PdfNull) {
            return $stream;
        }

        if ($filters instanceof PdfArray) {
            $filters = $filters->value;
        } else {
            $filters = [$filters];
        }

        $decodeParams = PdfDictionary::get($this->value, 'DecodeParms');
        if ($decodeParams instanceof PdfArray) {
            $decodeParams = $decodeParams->value;
        } else {
            $decodeParams = [$decodeParams];
        }

        foreach ($filters as $key => $filter) {
            if (!($filter instanceof PdfName)) {
                continue;
            }

            $decodeParam = null;
            if (isset($decodeParams[$key])) {
                $decodeParam = ($decodeParams[$key] instanceof PdfDictionary ? $decodeParams[$key] : null);
            }

            switch ($filter->value) {
                case 'FlateDecode':
                case 'Fl':
                case 'LZWDecode':
                case 'LZW':
                    if (strpos($filter->value, 'LZW') === 0) {
                        $filterObject = new Lzw();
                    } else {
                        $filterObject = new Flate();
                    }

                    $stream = $filterObject->decode($stream);

                    if ($decodeParam instanceof PdfDictionary) {
                        $predictor = PdfDictionary::get($decodeParam, 'Predictor', PdfNumeric::create(1));
                        if ($predictor->value !== 1) {
                            if (!class_exists(Predictor::class)) {
                                throw new PdfParserException(
                                    'This PDF document makes use of features which are only implemented in the ' .
                                    'commercial "FPDI PDF-Parser" add-on (see https://www.setasign.com/fpdi-pdf-' .
                                    'parser).',
                                    PdfParserException::IMPLEMENTED_IN_FPDI_PDF_PARSER
                                );
                            }

                            $colors = PdfDictionary::get($decodeParam, 'Colors', PdfNumeric::create(1));
                            $bitsPerComponent = PdfDictionary::get(
                                $decodeParam,
                                'BitsPerComponent',
                                PdfNumeric::create(8)
                            );

                            $columns = PdfDictionary::get($decodeParam, 'Columns', PdfNumeric::create(1));

                            $filterObject = new Predictor(
                                $predictor->value,
                                $colors->value,
                                $bitsPerComponent->value,
                                $columns->value
                            );

                            $stream = $filterObject->decode($stream);
                        }
                    }

                    break;
                case 'ASCII85Decode':
                case 'A85':
                    $filterObject = new Ascii85();
                    $stream = $filterObject->decode($stream);
                    break;

                case 'ASCIIHexDecode':
                case 'AHx':
                    $filterObject = new AsciiHex();
                    $stream = $filterObject->decode($stream);
                    break;

                default:
                    throw new FilterException(
                        sprintf('Unsupported filter "%s".', $filter->value),
                        FilterException::UNSUPPORTED_FILTER
                    );
            }
        }

        return $stream;
    }
}
