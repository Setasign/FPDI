<?php

namespace setasign\Fpdi\functional\PdfParser\Type;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfString;

class PdfStringTest extends TestCase
{
    public function parseProvider()
    {
        $data = [
            [
                'a simple string)',
                PdfString::create('a simple string')
            ],
            [
                'a (simple) string)',
                PdfString::create('a (simple) string')
            ],
            [
                'a \(simple) string)',
                PdfString::create('a \(simple')
            ],
            [
                'a \(simple\) string)',
                PdfString::create('a \(simple\) string')
            ],
            [
                "a \nstring)",
                PdfString::create("a \nstring")
            ],
            // this is should be handled when data is accessed
            [
                "a\\nstring)",
                PdfString::create("a\\nstring")
            ],
            [
                '())',
                PdfString::create('()')
            ],
            [
                '(a\(\)))',
                PdfString::create('(a\(\))')
            ],
            [
                'abc',
                PdfString::create('abc')
            ]
        ];

        return $data;
    }

    /**
     * @param $in
     * @param $expectedResult
     * @dataProvider parseProvider
     */
    public function testParse($in, $expectedResult)
    {
        $stream = StreamReader::createByString($in);
        $result = PdfString::parse($stream);

        $this->assertEquals($expectedResult, $result);
    }

    public function unescapeProvider()
    {
        return [
            // in, out
            ['do not unescape', 'do not unescape'],

            ['abcd\\\\', 'abcd\\'],
            ['\\\\abcd', '\\abcd'],
            ['ab\\\\cd', 'ab\\cd'],

            ['abcd\)', 'abcd)'],
            ['\)abcd', ')abcd'],
            ['ab\)cd', 'ab)cd'],

            ['abcd\(', 'abcd('],
            ['\(abcd', '(abcd'],
            ['ab\(cd', 'ab(cd'],

            ['abcd\r', "abcd\x0D"],
            ['\rabcd', "\x0Dabcd"],
            ['ab\rcd', "ab\x0Dcd"],

            ['abcd\n', "abcd\x0A"],
            ['\nabcd', "\x0Aabcd"],
            ['ab\ncd', "ab\x0Acd"],

            ['abcd\t', "abcd\x09"],
            ['\tabcd', "\x09abcd"],
            ['ab\tcd', "ab\x09cd"],

            ['abcd\b', "abcd\x08"],
            ['\babcd', "\x08abcd"],
            ['ab\bcd', "ab\x08cd"],

            ['abcd\f', "abcd\x0C"],
            ['\fabcd', "\x0Cabcd"],
            ['ab\fcd', "ab\x0Ccd"],

            ["A simple\nline break", "A simple\nline break"],
            ["A simple\\nline break", "A simple\nline break"],
            ["Another simple\rline break", "Another simple\rline break"],
            ["Another simple\\rline break", "Another simple\rline break"],
            ["And again another simple\r\nline break", "And again another simple\r\nline break"],
            ["And again another simple\\r\\nline break", "And again another simple\r\nline break"],

            ["A long text \\\rwithout a line break", "A long text without a line break", ],
            ["A long text \\\nwithout a line break", "A long text without a line break", ],
            ["A long text \\\r\nwithout a line break", "A long text without a line break", ],

            ['\245', chr(octdec('245')), ],
            ['\307', chr(octdec('307'))],

            ['\053', chr(octdec('053'))],
            ['\53', chr(octdec('053'))],
            ['\0053', chr(octdec('005')).'3'],
            ['\5', chr(octdec('005'))],

            ['\just ignored', 'just ignored'],
            ['just\ ignored', 'just ignored'],
            ['just i\gnored', 'just ignored'],
            ['just ignored\\', 'just ignored'],
        ];
    }

    /**
     * @param $expected
     * @param $in
     * @dataProvider unescapeProvider
     */
    public function testUnescape($in, $expected)
    {
        $this->assertEquals($expected, PdfString::unescape($in));
    }
}
