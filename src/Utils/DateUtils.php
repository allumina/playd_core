<?php

namespace Allumina\PlaydCore\Utils;

class DateUtils
{
    public const DATE_PART_DAY = 'j';
    public const DATE_PART_MONTH = 'n';
    public const DATE_PART_YEAR = 'Y';
    public const DATE_PART_WEEK = 'W';

    public static function DatePart(int $timestamp, string $part) {
        return date($part, $timestamp);
    }

    public static function FromYear(int $year) {
        $temp = $year.'-01-01';
        return strtotime($temp);
    }

    public static function FromWeek(int $year, int $week) {
        $temp = $year.'W'.str_pad($week, 2, '0', STR_PAD_LEFT);
        return strtotime($temp);
    }

    public static function FromMonth(int $year, int $month) {
        $temp = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-01';
        return strtotime($temp);
    }

    public static function FromDay(int $year, int $month, int $day) {
        $temp = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
        return strtotime($temp);
    }
}
