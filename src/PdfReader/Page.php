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

use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfStream;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfReader\DataStructure\Rectangle;

/**
 * Class representing a page of a PDF document
 *
 * @package setasign\Fpdi\PdfReader
 */
class Page
{
    /**
     * @var PdfIndirectObject
     */
    protected $pageObject;

    /**
     * @var PdfDictionary
     */
    protected $pageDictionary;

    /**
     * @var PdfParser
     */
    protected $parser;

    /**
     * Inherited attributes
     *
     * @var null|array
     */
    protected $inheritedAttributes;

    /**
     * Page constructor.
     *
     * @param PdfIndirectObject $page
     * @param PdfParser $parser
     */
    public function __construct(PdfIndirectObject $page, PdfParser $parser)
    {
        $this->pageObject = $page;
        $this->parser = $parser;
    }

    /**
     * Get the indirect object of this page.
     *
     * @return PdfIndirectObject
     */
    public function getPageObject()
    {
        return $this->pageObject;
    }

    /**
     * Get the dictionary of this page.
     *
     * @return PdfDictionary
     */
    public function getPageDictionary()
    {
        if (null === $this->pageDictionary) {
            $this->pageDictionary = PdfDictionary::ensure(PdfType::resolve($this->getPageObject(), $this->parser));
        }

        return $this->pageDictionary;
    }

    /**
     * Get a page attribute.
     *
     * @param string $name
     * @param bool $inherited
     * @return PdfType|null
     */
    public function getAttribute($name, $inherited = true)
    {
        $dict = $this->getPageDictionary();

        if (isset($dict->value[$name])) {
            return $dict->value[$name];
        }

        $inheritedKeys = ['Resources', 'MediaBox', 'CropBox', 'Rotate'];
        if ($inherited && in_array($name, $inheritedKeys, true)) {
            if (null === $this->inheritedAttributes) {
                $this->inheritedAttributes = [];
                $inheritedKeys = array_filter($inheritedKeys, function ($key) use ($dict) {
                    return !isset($dict->value[$key]);
                });

                if (count($inheritedKeys) > 0) {
                    $parentDict = PdfType::resolve(PdfDictionary::get($dict, 'Parent'), $this->parser);
                    while ($parentDict instanceof PdfDictionary) {
                        foreach ($inheritedKeys as $index => $key) {
                            if (isset($parentDict->value[$key])) {
                                $this->inheritedAttributes[$key] = $parentDict->value[$key];
                                unset($inheritedKeys[$index]);
                            }
                        }

                        /** @noinspection NotOptimalIfConditionsInspection */
                        if (isset($parentDict->value['Parent']) && count($inheritedKeys) > 0) {
                            $parentDict = PdfType::resolve(PdfDictionary::get($parentDict, 'Parent'), $this->parser);
                        } else {
                            break;
                        }
                    }
                }
            }

            if (isset($this->inheritedAttributes[$name])) {
                return $this->inheritedAttributes[$name];
            }
        }

        return null;
    }

    /**
     * Get the rotation value.
     *
     * @return int
     */
    public function getRotation()
    {
        $rotation = $this->getAttribute('Rotate');
        if (null === $rotation) {
            return 0;
        }

        $rotation = PdfNumeric::ensure(PdfType::resolve($rotation, $this->parser))->value % 360;

        if ($rotation < 0) {
            $rotation += 360;
        }

        return $rotation;
    }

    /**
     * Get a boundary of this page.
     *
     * @param string $box
     * @param bool $fallback
     * @return bool|Rectangle
     * @see PageBoundaries
     */
    public function getBoundary($box = PageBoundaries::CROP_BOX, $fallback = true)
    {
        $value = $this->getAttribute($box);

        if (null !== $value) {
            return Rectangle::byPdfArray($value, $this->parser);
        }

        if (false === $fallback) {
            return false;
        }

        switch ($box) {
            case PageBoundaries::BLEED_BOX:
            case PageBoundaries::TRIM_BOX:
            case PageBoundaries::ART_BOX:
                return $this->getBoundary(PageBoundaries::CROP_BOX, true);
            case PageBoundaries::CROP_BOX:
                return $this->getBoundary(PageBoundaries::MEDIA_BOX, true);
        }

        return false;
    }

    /**
     * Get the width and height of this page.
     *
     * @param string $box
     * @param bool $fallback
     * @return array|bool
     */
    public function getWidthAndHeight($box = PageBoundaries::CROP_BOX, $fallback = true)
    {
        $boundary = $this->getBoundary($box, $fallback);
        if (false === $boundary) {
            return false;
        }

        $rotation = $this->getRotation();
        $interchange = ($rotation / 90) % 2;

        return [
            $interchange ? $boundary->getHeight() : $boundary->getWidth(),
            $interchange ? $boundary->getWidth() : $boundary->getHeight()
        ];
    }

    /**
     * Get the raw content stream.
     *
     * @return string
     * @throws PdfReaderException
     */
    public function getContentStream()
    {
        $dict = $this->getPageDictionary();
        $contents = PdfType::resolve(PdfDictionary::get($dict, 'Contents'), $this->parser);
        if ($contents instanceof PdfNull) {
            return '';
        }

        if ($contents instanceof PdfArray) {
            $result = [];
            foreach ($contents->value as $content) {
                $content = PdfType::resolve($content, $this->parser);
                if (!($content instanceof PdfStream)) {
                    continue;
                }
                $result[] = $content->getUnfilteredStream();
            }

            return implode("\n", $result);
        }

        if ($contents instanceof PdfStream) {
            return $contents->getUnfilteredStream();
        }

        throw new PdfReaderException(
            'Array or stream expected.',
            PdfReaderException::UNEXPECTED_DATA_TYPE
        );
    }
}
