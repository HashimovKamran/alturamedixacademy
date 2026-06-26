<?php

namespace App\Services\Site;

use Carbon\CarbonInterface;

class DateFormatter
{
    private array $months = [
        'az' => [1 => 'Yanvar', 2 => 'Fevral', 3 => 'Mart', 4 => 'Aprel', 5 => 'May', 6 => 'İyun', 7 => 'İyul', 8 => 'Avqust', 9 => 'Sentyabr', 10 => 'Oktyabr', 11 => 'Noyabr', 12 => 'Dekabr'],
        'en' => [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'],
        'ru' => [1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'],
        'tr' => [1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'],
    ];

    public function az(?CarbonInterface $date): string
    {
        if (!$date) {
            return '';
        }

        return $this->format($date, 'az');
    }

    public function format(?CarbonInterface $date, string $language = 'az'): string
    {
        if (!$date) {
            return '';
        }

        $months = $this->months[$language] ?? $this->months['az'];

        return $date->day . ' ' . $months[$date->month] . ' ' . $date->year;
    }

    public function shortMonth(?CarbonInterface $date, string $language = 'az'): array
    {
        if (!$date) {
            return ['--', '---'];
        }

        $months = $this->months[$language] ?? $this->months['az'];

        return [str_pad((string) $date->day, 2, '0', STR_PAD_LEFT), mb_substr($months[$date->month], 0, 3)];
    }
}
