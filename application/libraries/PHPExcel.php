<?php
/**
 * Compatibility layer: PHPExcel API -> PhpSpreadsheet (composer).
 *
 * PHPExcel 1.8.0 tidak kompatibel dengan PHP 8.x (parse error, deprecated).
 * File ini menjaga nama class lama (PHPExcel, PHPExcel_IOFactory, PHPExcel_Style_*, dst)
 * agar kode di application/modules tetap berjalan di atas phpoffice/phpspreadsheet.
 */
defined('BASEPATH') or exit('No direct script access allowed');

if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    require_once FCPATH . 'vendor/autoload.php';
}

if (!class_exists('PHPExcel', false)) {

    class PHPExcel extends \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
    }

    class PHPExcel_IOFactory
    {
        /** Nama tipe PHPExcel => PhpSpreadsheet */
        private static $typeMap = array(
            'Excel2007' => 'Xlsx',
            'Excel5'    => 'Xls',
            'Excel2003XML' => 'Xml',
            'OOCalc'    => 'Ods',
            'CSV'       => 'Csv',
            'HTML'      => 'Html',
        );

        private static function mapType($type)
        {
            return isset(self::$typeMap[$type]) ? self::$typeMap[$type] : $type;
        }

        public static function createWriter($spreadsheet, $type = 'Excel2007')
        {
            return \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, self::mapType($type));
        }

        public static function createReader($type = '')
        {
            return \PhpOffice\PhpSpreadsheet\IOFactory::createReader(self::mapType($type));
        }

        public static function createReaderForFile($filename)
        {
            return \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filename);
        }

        public static function load($filename)
        {
            return \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
        }
    }

    class_alias('PhpOffice\PhpSpreadsheet\Cell\DataType', 'PHPExcel_Cell_DataType');
    class_alias('PhpOffice\PhpSpreadsheet\Style\Alignment', 'PHPExcel_Style_Alignment');
    class_alias('PhpOffice\PhpSpreadsheet\Style\Border', 'PHPExcel_Style_Border');
    class_alias('PhpOffice\PhpSpreadsheet\Style\Fill', 'PHPExcel_Style_Fill');
    class_alias('PhpOffice\PhpSpreadsheet\Style\Color', 'PHPExcel_Style_Color');
    class_alias('PhpOffice\PhpSpreadsheet\Style\NumberFormat', 'PHPExcel_Style_NumberFormat');
    class_alias('PhpOffice\PhpSpreadsheet\Worksheet\PageSetup', 'PHPExcel_Worksheet_PageSetup');
    class_alias('PhpOffice\PhpSpreadsheet\Shared\Date', 'PHPExcel_Shared_Date');
}
