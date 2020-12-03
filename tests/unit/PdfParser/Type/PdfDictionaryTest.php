<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfNull;
use setasign\Fpdi\PdfParser\Type\PdfNumeric;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

class PdfDictionaryTest extends TestCase
{
    public function testCreate()
    {
        $values = [
            'A' => PdfNumeric::create(123),
            'B' => PdfString::create('Test')
        ];

        $dict = PdfDictionary::create($values);
        $this->assertInstanceOf(PdfDictionary::class, $dict);

        $this->assertSame($values, $dict->value);
    }

    public function testGetWithDefault()
    {
        $default = PdfName::create('Default');
        $dict = PdfDictionary::create([
            'Type' => PdfName::create('Anything')
        ]);

        $this->assertSame($default, PdfDictionary::get($dict, 'Root', $default));

        $this->assertInstanceOf(PdfNull::class, PdfDictionary::get($dict, 'Root'));
    }

    public function testGet()
    {
        $type = PdfName::create('Anything');
        $dict = PdfDictionary::create([
            'Type' => $type
        ]);

        $this->assertSame($type, PdfDictionary::get($dict, 'Type'));
    }

    public function testEnsureWithInvlaidArgument1()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfDictionary::ensure('test');
    }

    public function testEnsureWithInvlaidArgument2()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfDictionary::ensure(PdfName::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfDictionary::create([]);
        $b = PdfDictionary::ensure($a);
        $this->assertSame($a, $b);
    }
}