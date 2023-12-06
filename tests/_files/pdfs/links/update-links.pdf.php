<?php
/* A simple helper script to modify links.pdf in view to indirect references in annotation dictionaries.
 * We used SetaPDF-Core for doing such low-level modifications.
 */

require_once __DIR__ . '/../../../../../SetaPDF/library/SetaPDF/Autoload.php';

$writer = new SetaPDF_Core_Writer_File('links-with-indirect-references.pdf');
$document = SetaPDF_Core_Document::loadByFilename('links.pdf', $writer);

$pages = $document->getCatalog()->getPages();
$page = $pages->getPage(1);

$annotations = $page->getAnnotations();
$allAnnots = $annotations->getAll();

$dict = $allAnnots[0]->getDictionary();
$bs = $dict->getValue('BS');
$d = $bs->getValue('D');
$bs->offsetSet('D', $document->createNewObject($d));
$dict->offsetSet('BS', $document->createNewObject($bs));

$dict = $allAnnots[1]->getDictionary();
$c = $dict->getValue('C');
$object = $document->createNewObject($c);
$ref = new SetaPDF_Core_Type_IndirectReference($object, 0, $document);
$dict->offsetSet('C', $document->createNewObject($ref));


$dict = $allAnnots[5]->getDictionary();
$border = $dict->getValue('Border');
$dict->offsetSet('Border', $document->createNewObject($border));

$page = $pages->getPage(2);
$annotations = $page->getAnnotations();
$allAnnots = $annotations->getAll();

$dict = $allAnnots[0]->getDictionary();
$rect = $dict->getValue('Rect');
$dict->offsetSet('Rect', $document->createNewObject($rect));

$document->save()->finish();
