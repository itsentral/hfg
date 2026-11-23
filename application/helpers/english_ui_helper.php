<?php
defined('BASEPATH') || exit('No direct script access allowed');

/** English display text for the integer part; no currency conversion or amount calculation. */
if (!function_exists('hfg_number_to_words_english')) {
    function hfg_number_to_words_english($number)
    {
        $number = (float)$number;
        if (!is_finite($number) || abs($number) >= 1000000000000000) return 'Number is too large';
        $negative = $number < 0;
        $number = (int)abs($number);
        $units = ['zero','one','two','three','four','five','six','seven','eight','nine',
            'ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];
        if ($number < 20) {
            $words = $units[$number];
        } elseif ($number < 100) {
            $tens = [2=>'twenty',3=>'thirty',4=>'forty',5=>'fifty',6=>'sixty',7=>'seventy',8=>'eighty',9=>'ninety'];
            $words = $tens[intdiv($number,10)] . ($number % 10 ? '-' . $units[$number % 10] : '');
        } else {
            foreach ([1000000000000=>'trillion',1000000000=>'billion',1000000=>'million',1000=>'thousand',100=>'hundred'] as $scale=>$label) {
                if ($number < $scale) continue;
                $words = hfg_number_to_words_english(intdiv($number,$scale)) . ' ' . $label;
                if ($number % $scale) $words .= ' ' . hfg_number_to_words_english($number % $scale);
                break;
            }
        }
        return ($negative ? 'minus ' : '') . $words;
    }
}
