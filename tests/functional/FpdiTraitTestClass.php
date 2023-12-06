<?php

namespace setasign\Fpdi\functional;

use setasign\Fpdi\FpdfTrait;
use setasign\Fpdi\FpdiTrait;
use setasign\Fpdi\PdfParser\Type\PdfIndirectObject;
use setasign\Fpdi\PdfParser\Type\PdfType;

/**
 * This class is used for testing methods of the FpdiTrait trait.
 */
class FpdiTraitTestClass {
    use FpdiTrait, FpdfTrait;

    protected $buffer;
    protected $n = 0;

    protected function _newobj($n = null)
    {
        if ($n === null) {
            $n = ++$this->n;
        }

        $this->_put($n . ' 0 obj');
    }

    public function simulateWritePdfType(PdfType $value)
    {
        // If the object was not referenced before we need to add an object number here
        if ($value instanceof PdfIndirectObject) {
            if (!isset($this->objectMap[$this->currentReaderId][$value->objectNumber])) {
                $this->objectMap[$this->currentReaderId][$value->objectNumber] = ++$this->n;
            }
        }

        $this->buffer = '';
        $this->writePdfType($value);
        return $this->buffer;
    }
}
