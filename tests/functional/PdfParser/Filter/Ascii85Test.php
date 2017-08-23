<?php

namespace setasign\Fpdi\functional\PdfParser\Filter;

use PHPUnit\Framework\TestCase;
use setasign\Fpdi\PdfParser\Filter\Ascii85;

class Ascii85Test extends TestCase
{
    public function decodeProvider()
    {
        $data = [
            [
                '',
                ''
            ],
            [
                '8,',
                'H'
            ],
            [
                '8,s',
                "H\x00"
            ],
            [
                '8,sa',
                "H\x00\x1F"
            ],
            [
                '87?=NDc^LdF8',
                'HalloWelt'
            ],
            [
                '<~87?=NDc^LdF8~>',
                'HalloWelt'
            ],
            [
                'z',
                "\x00\x00\x00\x00"
            ],
            [
                '7;6W^<+U,m$<hRh+B2onFCf=s<Gl=iFCfN8ASqiS78m/S+X&!P',
                "Ein Test\nZum Testen\nUnd testen\nTESTE!!!!"
            ],
            [
                '7;6W           ^<+U,  m$<hR h+B 2  o  n   F   Cf= s<Gl=iFCfN8 ASqi S78 m/S+X    &!P',
                "Ein Test\nZum Testen\nUnd testen\nTESTE!!!!"
            ],
            [
                '-c;at[_obqKYs+nSAMKR,/U6\',pk8n.6T1+/M8S93aX6a5f0bBZGNqp>?uY^I_rMPJun+IU?/MDj+4MrJr/N=N8oJh/0O&bZfJa$jcGJKOW`#Ul(=1$S;ZU2T\'W/Tm"D%tVi^8=Yj@%9m$>"0+Qf2bl)q2QWh/UCSEdMal_fgl',
                '\'äöüÄÖÜ°!"§$%&/()=+#-.,;:_\'*@°²³{[]}\\~µ€漢字ひらがな, 平仮名Б б韓國語조선말조선어, 朝鮮語한국말\''
            ],
            [
                '<~9jqo^BlbD-BleB1DJ+*+F(f,q/0JhKF<GL>Cj@.4Gp$d7F!,L7@<6@)/0JDEF<G%<+EV:2F!,O<DJ+*.@<*K0@<6L(Df-\0Ec5e;DffZ(EZee.Bl.9pF"AGXBPCsi+DGm>@3BB/F*&OCAfu2/AKYi(DIb:@FD,*)+C]U=@3BN#EcYf8ATD3s@q?d$AftVqCh[NqF<G:8+EV:.+Cf>-FD5W8ARlolDIal(DId<j@<?3r@:F%a+D58\'ATD4$Bl@l3De:,-DJs`8ARoFb/0JMK@qB4^F!,R<AKZ&-DfTqBG%G>uD.RTpAKYo\'+CT/5+Cei#DII?(E,9)oF*2M7/c~>',
                'Man is distinguished, not only by his reason, but by this singular passion from other animals, which is a lust of the mind, that by a perseverance of delight in the continued and indefatigable generation of knowledge, exceeds the short vehemence of any carnal pleasure.'
            ]
        ];

        return $data;
    }

    /**
     * @dataProvider decodeProvider
     */
    public function testDecode($in, $expected)
    {
        $filter = new Ascii85();

        $decoded = $filter->decode($in);

        $this->assertSame($expected, $decoded);
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Filter\Ascii85Exception
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Filter\Ascii85Exception::ILLEGAL_CHAR_FOUND
     */
    public function testDecodeWithIllegalCharacter()
    {
        $filter = new Ascii85();

        $filter->decode("\x00");
    }

    /**
     * @expectedException \setasign\Fpdi\PdfParser\Filter\Ascii85Exception
     * @expectedExceptionCode \setasign\Fpdi\PdfParser\Filter\Ascii85Exception::ILLEGAL_LENGTH
     */
    public function testDecodeWithIllegalLength()
    {
        $filter = new Ascii85();

        $filter->decode("a");
    }
}