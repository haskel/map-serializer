<?php

use Haskel\MapSerializer\EntityExtractor\ExtractorGenerator;
use Haskel\MapSerializer\Formatter\DatetimeFormatter;
use Haskel\MapSerializer\Order;
use Haskel\MapSerializer\Serializer;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . "/../vendor/autoload.php";

$serializer = new Serializer();

$orderSchemas = Yaml::parseFile(__DIR__ . "/config/Order.yml");
$datetimeSchemas = Yaml::parseFile(__DIR__ . "/config/DateTime.yml");


$generator = new ExtractorGenerator();
$serializer->addFormatter(new DatetimeFormatter());

//$directory = __DIR__ . "/" . str_replace("\\", '/', str_replace('Haskel\\SchemaSerializer\\', '', $generated->namespace));
$directory = __DIR__ . "/EntityExtractor/Generated";

foreach ($orderSchemas as $name => $orderSchema) {
    $serializer->addSchema(Order::class, $name, $orderSchema);
    $generated = $generator->generate(Order::class, $name, $orderSchema);
    $generated->saveFile($directory);
    $serializer->addExtractor(Order::class, $name, $generated->getFullClassName());
}
foreach ($datetimeSchemas as $name => $datetimeSchema) {
    $serializer->addSchema(DateTime::class, $name, $datetimeSchema);
}

$order = new Order();
$array = [
    'order1' => new Order(),
    'order2' => new Order(),
];

try {
    $val = $serializer->serialize($array, 'test');
    var_dump($val);
} catch (Exception $e) {
    $a = 1;
}
$a = 1;
