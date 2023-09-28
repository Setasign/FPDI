<?php

namespace setasign\Fpdi\unit\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfString;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;

class PdfStringTest extends TestCase
{
    public function testCreate()
    {
        $v = PdfString::create("Test");
        $this->assertInstanceOf(PdfString::class, $v);
        $this->assertSame("Test", $v->value);
    }

    public function testEnsureWithInvlaidArgument1()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfString::ensure('test');
    }

    public function testEnsureWithInvlaidArgument2()
    {
        $this->expectException(PdfTypeException::class);
        $this->expectExceptionCode(PdfTypeException::INVALID_DATA_TYPE);
        PdfString::ensure(PdfName::create('test'));
    }

    public function testEnsure()
    {
        $a = PdfString::create('Testing is cool');
        $b = PdfString::ensure($a);
        $this->assertSame($a, $b);
    }

    public function escapeProvider()
    {
        return [
            // in    => out
            ['do not escape', 'do not escape'],
            ['abcd\\', 'abcd\\\\'],
            ['\\abcd', '\\\\abcd'],
            ['ab\\cd', 'ab\\\\cd'],

            ['abcd)', 'abcd\)'],
            [')abcd', '\)abcd'],
            ['ab)cd', 'ab\)cd'],

            ['abcd(', 'abcd\('],
            ['(abcd', '\(abcd'],
            ['ab(cd', 'ab\(cd'],

            ["abcd\x0D", 'abcd\r'],
            ["\x0Dabcd", '\rabcd'],
            ["ab\x0Dcd", 'ab\rcd'],

            ["abcd\x0A", 'abcd\n'],
            ["\x0Aabcd", '\nabcd'],
            ["ab\x0Acd", 'ab\ncd'],

            ["abcd\x09", 'abcd\t'],
            ["\x09abcd", '\tabcd'],
            ["ab\x09cd", 'ab\tcd'],

            ["abcd\x08", 'abcd\b'],
            ["\x08abcd", '\babcd'],
            ["ab\x08cd", 'ab\bcd'],

            ["abcd\x0C", 'abcd\f'],
            ["\x0Cabcd", '\fabcd'],
            ["ab\x0Ccd", 'ab\fcd'],
        ];
    }

    /**
     * @dataProvider escapeProvider
     */
    public function testEscape($in, $out)
    {
        $this->assertEquals($out, PdfString::escape($in));
    }

    public function unescapeProvider()
    {
        return [
            // out   , in
            ['do not unescape', 'do not unescape'],

            ['abcd\\', 'abcd\\\\'],
            ['\\abcd', '\\\\abcd'],
            ['ab\\cd', 'ab\\\\cd'],

            ['abcd)', 'abcd\)'],
            [')abcd', '\)abcd'],
            ['ab)cd', 'ab\)cd'],

            ['abcd(', 'abcd\('],
            ['(abcd', '\(abcd'],
            ['ab(cd', 'ab\(cd'],

            ["abcd\x0D", 'abcd\r'],
            ["\x0Dabcd", '\rabcd'],
            ["ab\x0Dcd", 'ab\rcd'],

            ["abcd\x0A", 'abcd\n'],
            ["\x0Aabcd", '\nabcd'],
            ["ab\x0Acd", 'ab\ncd'],

            ["abcd\x09", 'abcd\t'],
            ["\x09abcd", '\tabcd'],
            ["ab\x09cd", 'ab\tcd'],

            ["abcd\x08", 'abcd\b'],
            ["\x08abcd", '\babcd'],
            ["ab\x08cd", 'ab\bcd'],

            ["abcd\x0C", 'abcd\f'],
            ["\x0Cabcd", '\fabcd'],
            ["ab\x0Ccd", 'ab\fcd'],

            ["A simple\nline break", "A simple\nline break"],
            ["A simple\nline break", "A simple\\nline break"],
            ["Another simple\rline break", "Another simple\rline break"],
            ["Another simple\rline break", "Another simple\\rline break"],
            ["And again another simple\r\nline break", "And again another simple\r\nline break"],
            ["And again another simple\r\nline break", "And again another simple\\r\\nline break"],

            ["A long text without a line break", "A long text \\\rwithout a line break"],
            ["A long text without a line break", "A long text \\\nwithout a line break"],
            ["A long text without a line break", "A long text \\\r\nwithout a line break"],

            [chr(octdec('245')), '\245'],
            [chr(octdec('307')), '\307'],

            [chr(octdec('053')), '\053'],
            [chr(octdec('053')), '\53'],
            [chr(octdec('005')).'3', '\0053'],
            [chr(octdec('005')), '\5'],

            ['just ignored', '\just ignored'],
            ['just ignored', 'just\ ignored'],
            ['just ignored', 'just i\gnored'],
            ['just ignored', 'just ignored\\'],
        ];
    }

    /**
     * @dataProvider unescapeProvider
     */
    public function testUnescape($out, $in)
    {
        $this->assertEquals($out, PdfString::unescape($in));
    }
}
