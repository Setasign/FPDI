<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace setasign\Fpdi\Math;

/**
 * A simple 2D-Matrix class
 */
class Matrix
{
    protected $a;
    protected $b;
    protected $c;
    protected $d;
    protected $e;
    protected $f;

    public function __construct($a = 1, $b = 0, $c = 0, $d = 1, $e = 0, $f = 0)
    {
        $this->a = (float)$a;
        $this->b = (float)$b;
        $this->c = (float)$c;
        $this->d = (float)$d;
        $this->e = (float)$e;
        $this->f = (float)$f;
    }

    public function getValues()
    {
        return [$this->a, $this->b, $this->c, $this->d, $this->e, $this->f];
    }

    public function multiply(self $by)
    {
        $a =
            $this->a * $by->a
            + $this->b * $by->c
            //+ 0 * $by->e
        ;

        $b =
            $this->a * $by->b
            + $this->b * $by->d
            //+ 0 * $by->f
        ;

        $c =
            $this->c * $by->a
            + $this->d * $by->c
            //+ 0 * $by->e
        ;

        $d =
            $this->c * $by->b
            + $this->d * $by->d
            //+ 0 * $by->f
        ;

        $e =
            $this->e * $by->a
            + $this->f * $by->c
            + /*1 * */$by->e;

        $f =
            $this->e * $by->b
            + $this->f * $by->d
            + /*1 * */$by->f;

        return new self($a, $b, $c, $d, $e, $f);
    }
}
