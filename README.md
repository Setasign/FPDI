FPDI - Free PDF Document Importer
=================================

[![Latest Stable Version](https://poser.pugx.org/setasign/fpdi/v/stable.svg)](https://packagist.org/packages/setasign/fpdi) [![Total Downloads](https://poser.pugx.org/setasign/fpdi/downloads.svg)](https://packagist.org/packages/setasign/fpdi) [![Latest Unstable Version](https://poser.pugx.org/setasign/fpdi/v/unstable.svg)](https://packagist.org/packages/setasign/fpdi) [![License](https://poser.pugx.org/setasign/fpdi/license.svg)](https://packagist.org/packages/setasign/fpdi)

A clone to [FPDI](https://www.setasign.com/fpdi)

FPDI is a collection of PHP classes facilitating developers to read pages from existing PDF documents and use them as templates in FPDF, which was developed by Olivier Plathey. Apart from a copy of FPDF, FPDI does not require any special PHP extensions.

As of version 1.2.1 FPDI can also be used with TCPDF.

## Installation

Run the command:

```bash
$ composer require "setasign/fpdi"
```

Usage
-----

The usage is very easy: open the document, put a page into a template, and use it like an image!

```php
<?php
// ...

use \FPDI;
use \FPDF;

// ...
$pdf = new FPDI();

$pageCount = $pdf->setSourceFile("Fantastic-Speaker.pdf");
$tplIdx = $pdf->importPage(1, '/MediaBox');

$pdf->addPage();
$pdf->useTemplate($tplIdx, 10, 10, 90);

$pdf->Output();
```
