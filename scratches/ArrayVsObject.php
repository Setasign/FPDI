<?php
/* A simple scratch that shows the comparison of arrays vs. objects vs. factories vs. constructors.
 */
class A
{
    public static function getInstance($value)
    {
        $v = new self();
        $v->value = $value;

        return $v;
    }

    public $value;
}

class B extends A
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}

new A();
new B(12);

gc_disable();

$iterations = 500000;

echo "array:\n";
$res = [];
$start = memory_get_usage();
$tStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $res[] = [A::class, $i];
}

$end = memory_get_usage();
$tEnd = microtime(true);
echo ($tEnd - $tStart) . ': ';
var_dump(($end - $start) / 1024 / 1024);

echo "-------------------------\n";

echo "object + property:\n";
$res = [];
$start = memory_get_usage();
$tStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $v = new A();
    $v->value = $i;

    $res[] = $v;
}

$end = memory_get_usage();
$tEnd = microtime(true);
echo ($tEnd - $tStart) . ': ';
var_dump(($end - $start) / 1024 / 1024);

echo "-------------------------\n";

echo "object vis factory:\n";
$res = [];
$start = memory_get_usage();
$tStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $v = A::getInstance($i);
    $res[] = $v;
}

$end = memory_get_usage();
$tEnd = microtime(true);
echo ($tEnd - $tStart) . ': ';
var_dump(($end - $start) / 1024 / 1024);

echo "-------------------------\n";

echo "object + constructor:\n";
$res = [];
$start = memory_get_usage();
$tStart = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $v = new B($i);
    $res[] = $v;
}

$end = memory_get_usage();
$tEnd = microtime(true);
echo ($tEnd - $tStart) . ': ';

var_dump(($end - $start) / 1024 / 1024);
