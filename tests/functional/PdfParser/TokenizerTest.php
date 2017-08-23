<?php

namespace setasign\Fpdi\functional\PdfParser;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Tokenizer;

class TokenizerTest extends TestCase
{
    public function getNextTokenProvider()
    {
        $longString = str_repeat('c', 610);
        $longString2 = str_repeat('A', 100);
        $longWs = str_repeat(' ', 610);
        $longWs2 = str_repeat("\r\n \r", 50);

        $data = [
            ['', []],
            ['bbbb', ['bbbb']],
            ['a', ['a']],
            [' a', ['a']],
            ['a ', ['a']],
            ["\ta", ['a']],
            ['0', ['0']],
            ['1', ['1']],
            [' 1', ['1']],
            ['1 ', ['1']],
            ["\t1", ['1']],
            ["1\n", ['1']],
            ['<', ['<']],
            ['>', ['>']],
            ['< a <', ['<', 'a', '<']],
            ['<<<h>>>', ['<', '<', '<', 'h', '>', '>', '>']],
            ['<<< h >>>', ['<', '<', '<', 'h', '>', '>', '>']],
            ['<<<', ['<', '<', '<']],
            ['<<<>>', ['<', '<', '<', '>', '>']],
            ['<<<>>>', ['<', '<', '<', '>', '>', '>']],
            ['<<', ['<', '<']],
            ['>>', ['>', '>']],
            ['>>>', ['>', '>', '>']],
            ['<<<', ['<', '<', '<']],
            ['<[', ['<', '[']],
            ['< [', ['<', '[']],
            ['<}', ['<', '}']],
            ['%a', []],
            ['a%', ['a']],
            ['%%', []],
            ['[', ['[']],
            ["\r[\n(\t\t )", ['[', '(', ')']],
            ['<a> <b>', ['<', 'a', '>', '<', 'b', '>']],
            ['a b ' . $longString . ' d', ['a', 'b', $longString, 'd']],
            [$longWs . '/1234' . $longWs . 'a', ['/', '1234', 'a']],
            [$longString2 . $longString2 . $longWs . 'b', [$longString2 . $longString2, 'b']],
            [$longWs2 . 'a' . $longWs2, ['a']],
            ['a <' . $longWs2 . '<' . $longWs . '<', ['a', '<', '<', '<']],
            ["/<><</>>[[\n\\n]]", ['/', '<', '>', '<', '<', '/', '>', '>', '[', '[', '\n', ']', ']']],
            ['< < > < > >', ['<', '<', '>', '<', '>', '>']],
            ['just a short sentence!', ['just', 'a', 'short', 'sentence!']],
            ['<<~ h ~>>', ['<', '<', '~', 'h', '~', '>', '>']],

            ['<~asd~>>', ['<', '~asd~', '>', '>']],
        ];

        // Postscript supports ASCII Base-85 Strings
        // At the end the parser is responsable to switch between HEX or ASCII Base-85
        // A seperate Tokenizer for PS is not needed!
        $data[] = ['<~', ['<', '~']];
        $data[] = ['<~a', ['<', '~a']];
        $data[] = ['<~ a', ['<', '~', 'a']];
        $data[] = ['< ~ a <', ['<', '~', 'a', '<']];
        $data[] = ['< ~ a <~', ['<', '~', 'a', '<', '~']];
        $data[] = ['<H~ a <~', ['<', 'H~', 'a', '<', '~']];

        $data[] = ['~ h', ['~', 'h']];
        $data[] = ['~> h', ['~', '>', 'h']];

        $data[] = ['<<~ h ~ a >>', ['<', '<', '~', 'h', '~', 'a', '>', '>']];

        $data[] = ['<<~h~a >>', ['<', '<', '~h~a', '>', '>']];
        $data[] = ['<<~ h ~>>', ['<', '<', '~', 'h', '~', '>', '>']];
        $data[] = ['<~asd~>>', ['<', '~asd~', '>', '>']];

        $data[] = ['{abcde}', ['{', 'abcde', '}']];
        $data[] = ["{\nabcde\n}\n", ['{', 'abcde', '}']];

        $long = '';
        $res = [];
        for ($i = 70; $i < 90; $i++) {
            $value = str_repeat(chr($i), 100);
            $res[] = $value;
            $long .= str_repeat("\n \r ", 100).$value;
        }
        for ($i = 70; $i < 90; $i++) {
            $value = str_repeat(chr($i), 200);
            $res[] = $value;
            $long .= str_repeat("\n \r ", 200).$value;
        }
        $data[] = [$long, $res];

        $long = str_repeat('a', 99)
            . ' bbbb 1234567890'
            . ' cccc';
        $res = [str_repeat('a', 99), 'bbbb', '1234567890', 'cccc'];
        $data[] = [$long, $res];

        // a "0" == false at the end
        $data[] = ['K 10', ['K', '10']];

        $data[] = ['6032 0 obj<</K 70', ['6032', '0', 'obj', '<', '<', '/', 'K', '70']];

        return $data;
    }

    /**
     * @param $string
     * @param array $expectedResult
     * @dataProvider getNextTokenProvider
     */
    public function testGetNextToken($string, array $expectedResult)
    {
        $reader = StreamReader::createByString($string);
        $tokenizer = new Tokenizer($reader);

        $check = 0;
        while (($token = $tokenizer->getNextToken()) !== false) {
            $this->assertSame($expectedResult[$check], $token);
            $check++;
        }

        $this->assertCount($check, $expectedResult);
    }

    public function testStackBehaviour()
    {
        $reader = StreamReader::createByString('');
        $tokenizer = new Tokenizer($reader);

        $this->assertFalse($tokenizer->getNextToken());

        $tokenizer->pushStack('abc');
        $tokenizer->pushStack('def');

        $this->assertSame('def', $tokenizer->getNextToken());
        $this->assertSame('abc', $tokenizer->getNextToken());

        $this->assertFalse($tokenizer->getNextToken());
    }
}
