<?php
define('BASEPATH', dirname(__DIR__) . '/system/');
define('FCPATH', dirname(__DIR__) . '/');
require dirname(__DIR__) . '/application/libraries/PHPExcel.php';

$workbook = new PHPExcel();
$sheet = $workbook->getActiveSheet();
$solid = PHPExcel_Style_Fill::FILL_SOLID;

$sheet->fromArray([
    ['COIL NO.', 'Alias Name', 'Original Name', 'N.W.', 'G.W.', 'Length', 'BPM', 'Qty Roll', 'Pack'],
    ['', 'Material A', 'Original A', '', '', '', '', '', ''],
    ['', 'Material A', 'Original A', '', '', '', '', '', ''],
    ['', '', '', '', '', '', '', '', ''],
    ['', 'Material B', 'Original B', '', '', '', '', '', ''],
], null, 'A1');

$sheet->getStyle('A1:I1')->applyFromArray([
    'fill' => ['fillType' => $solid, 'startColor' => ['rgb' => 'D9E1F2']],
]);
$sheet->getStyle('A2:I3')->applyFromArray([
    'fill' => ['fillType' => $solid, 'startColor' => ['rgb' => 'EBF5FB']],
]);
$sheet->getStyle('A5:I5')->applyFromArray([
    'fill' => ['fillType' => $solid, 'startColor' => ['rgb' => 'E9F7EF']],
]);

$path = sys_get_temp_dir() . '/hfg-new-ros-template-color-test.xlsx';
PHPExcel_IOFactory::createWriter($workbook, 'Excel2007')->save($path);
$loaded = PHPExcel_IOFactory::load($path);
$loadedSheet = $loaded->getActiveSheet();

$expectations = [
    'A1' => 'D9E1F2',
    'A2' => 'EBF5FB',
    'I3' => 'EBF5FB',
    'A5' => 'E9F7EF',
    'I5' => 'E9F7EF',
];
foreach ($expectations as $cell => $expectedColor) {
    $fill = $loadedSheet->getStyle($cell)->getFill();
    if ($fill->getFillType() !== $solid || $fill->getStartColor()->getRGB() !== $expectedColor) {
        throw new RuntimeException(
            $cell . ' expected solid ' . $expectedColor . ', got '
            . $fill->getFillType() . ' ' . $fill->getStartColor()->getRGB()
        );
    }
}

if ($loadedSheet->getStyle('A4')->getFill()->getFillType() === $solid) {
    throw new RuntimeException('Separator row must not inherit a material fill.');
}

unlink($path);
echo "PASS New ROS XLSX header and material group colors survive write/read round-trip.\n";
