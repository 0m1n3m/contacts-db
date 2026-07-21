<?php

namespace App\Helpers;

use Carbon\Carbon;

class WorkingTimeHelper
{
    private const WORKING_HOURS_PER_DAY = 8;
    private const WEEKEND_DAYS = [0, 6]; // 0 = domingo, 6 = sábado

    /**
     * Calcular segundos de tiempo laboral entre dos fechas
     * Excluyendo fines de semana y considerando 8 horas por día laboral
     */
    public static function getWorkingSeconds(Carbon $startDate, Carbon $endDate): int
    {
        if ($endDate < $startDate) {
            return 0;
        }

        $workingSeconds = 0;
        $current = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        while ($current < $end) {
            // Si no es fin de semana
            if (!in_array($current->dayOfWeek, self::WEEKEND_DAYS)) {
                // Calcular segundos dentro de este día laboral
                $dayStart = $current->copy();
                $dayEnd = $current->copy()->endOfDay();

                // Si es el primer día, empezar desde la hora específica
                if ($current->isSameDay($startDate)) {
                    $dayStart = $startDate->copy();
                }

                // Si es el último día, terminar en la hora específica
                if ($current->isSameDay($endDate)) {
                    $dayEnd = $endDate->copy();
                }

                $secondsInDay = $dayStart->diffInSeconds($dayEnd);
                
                // Limitar a 8 horas máximo por día
                $maxSecondsPerDay = self::WORKING_HOURS_PER_DAY * 3600;
                $workingSeconds += min($secondsInDay, $maxSecondsPerDay);
            }

            $current->addDay();
        }

        return $workingSeconds;
    }

    /**
     * Calcular segundos de tiempo laboral entre dos fechas
     * pero teniendo en cuenta la fecha/hora exacta del inicio y fin
     */
    public static function getWorkingSecondsPrecise(Carbon $startDate, Carbon $endDate): int
    {
        if ($endDate < $startDate) {
            return 0;
        }

        $workingSeconds = 0;
        $current = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        // Procesar cada día
        while ($current <= $end) {
            // Si no es fin de semana
            if (!in_array($current->dayOfWeek, self::WEEKEND_DAYS)) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();

                // Ajustar para el primer día
                if ($current->isSameDay($startDate)) {
                    $dayStart = $startDate->copy();
                }

                // Ajustar para el último día
                if ($current->isSameDay($endDate)) {
                    $dayEnd = $endDate->copy();
                }

                $workingSeconds += $dayStart->diffInSeconds($dayEnd);
            }

            $current->addDay();
        }

        return $workingSeconds;
    }

    /**
     * Convertir segundos de trabajo a formato legible
     */
    public static function formatWorkingTime(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        if ($hours >= 24) {
            $days = intdiv($hours, 8);
            $remainingHours = $hours % 8;
            if ($remainingHours > 0) {
                return "{$days}d {$remainingHours}h {$minutes}m";
            }
            return "{$days}d";
        }

        if ($hours > 0) {
            return "{$hours}h {$minutes}m {$secs}s";
        }
        if ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        }
        return "{$secs}s";
    }

    /**
     * Convertir segundos a horas de trabajo
     */
    public static function secondsToWorkingHours(int $seconds): float
    {
        return $seconds / 3600;
    }

    /**
     * Convertir segundos a días de trabajo (basado en 8h/día)
     */
    public static function secondsToWorkingDays(int $seconds): float
    {
        return $seconds / (self::WORKING_HOURS_PER_DAY * 3600);
    }
}