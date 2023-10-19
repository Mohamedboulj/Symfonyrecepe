<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppHourToMinExtenxionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppHourToMinExtenxionExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('HourToMinute', [$this, 'HourToMinute']),
        ];
    }

    public function HourToMinute(?int $val): string
    {
        if ($val < 10 || !$val) {
            $minute = '0' . $val;
        }
        $hour = floor($val / 60);
        $minute = $val % 60;
        if ($minute < 10) {
            $minute = '0' . $minute;
        }
        $time = sprintf('%sh%sm', $hour, $minute);
        return $time;
    }
}
