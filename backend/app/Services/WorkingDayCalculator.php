<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use InvalidArgumentException;

class WorkingDayCalculator
{
    /**
     * @return array{
     *   working_days: int,
     *   total_calendar_days: int,
     *   weekend_days: int,
     *   holiday_days: int,
     *   excluded_dates: array<int, array{date: string, reason: string}>
     * }
     */
    public function calculate(string|Carbon $startDate, string|Carbon $endDate): array
    {
        $start = $this->normalizeDate($startDate);
        $end = $this->normalizeDate($endDate);

        if ($end->lt($start)) {
            throw new InvalidArgumentException('The end date cannot be earlier than the start date.');
        }

        $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());
        $holidays = Holiday::query()
            ->where('is_active', true)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip()
            ->all();

        $totalCalendarDays = 0;
        $weekendDays = 0;
        $holidayDays = 0;
        $excludedDates = [];
        $workingDays = 0;

        foreach ($period as $date) {
            $dateKey = $date->toDateString();
            $totalCalendarDays++;

            $isWeekend = $date->isSaturday() || $date->isSunday();
            $isHoliday = isset($holidays[$dateKey]);

            if ($isWeekend && $isHoliday) {
                $weekendDays++;
                $holidayDays++;
                $excludedDates[] = ['date' => $dateKey, 'reason' => 'weekend + holiday'];
                continue;
            }

            if ($isWeekend) {
                $weekendDays++;
                $excludedDates[] = ['date' => $dateKey, 'reason' => 'weekend'];
                continue;
            }

            if ($isHoliday) {
                $holidayDays++;
                $excludedDates[] = ['date' => $dateKey, 'reason' => 'holiday'];
                continue;
            }

            $workingDays++;
        }

        return [
            'working_days' => $workingDays,
            'total_calendar_days' => $totalCalendarDays,
            'weekend_days' => $weekendDays,
            'holiday_days' => $holidayDays,
            'excluded_dates' => $excludedDates,
        ];
    }

    private function normalizeDate(string|Carbon $date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date->copy()->startOfDay();
        }

        return Carbon::parse($date)->startOfDay();
    }
}
