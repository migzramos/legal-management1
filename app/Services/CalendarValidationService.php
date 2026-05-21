<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Calendar Validation Service
 * 
 * Ensures strict backend validation to prevent overlapping bookings
 * Uses time range checks with multiple safety measures
 */
class CalendarValidationService
{
    /**
     * Check if lawyer is available at the requested time
     * 
     * @param int $lawyerId
     * @param Carbon $startTime
     * @param int $durationMinutes
     * @param int|null $excludeAppointmentId Appointment ID to exclude (for updates)
     * @return array Success/failure result with details
     */
    public static function checkAvailability(int $lawyerId, Carbon $startTime, int $durationMinutes, ?int $excludeAppointmentId = null): array
    {
        // Validate times
        if (!$startTime || $startTime->isPast()) {
            return [
                'available' => false,
                'reason' => 'Appointment time must be in the future.',
            ];
        }

        $endTime = (clone $startTime)->addMinutes($durationMinutes);

        // Check for overlapping appointments
        $conflictingAppointments = self::findConflictingAppointments(
            $lawyerId,
            $startTime,
            $endTime,
            $excludeAppointmentId
        );

        if ($conflictingAppointments->isNotEmpty()) {
            $firstConflict = $conflictingAppointments->first();
            return [
                'available' => false,
                'reason' => 'Lawyer is not available during this time slot.',
                'conflicting_appointment' => [
                    'id' => $firstConflict->id,
                    'starts_at' => $firstConflict->appointment_at->toIso8601String(),
                    'ends_at' => $firstConflict->end_time->toIso8601String(),
                    'status' => $firstConflict->status,
                ],
            ];
        }

        return [
            'available' => true,
            'start_time' => $startTime->toIso8601String(),
            'end_time' => $endTime->toIso8601String(),
            'duration_minutes' => $durationMinutes,
        ];
    }

    /**
     * Find all conflicting appointments using multiple validation strategies
     * 
     * @param int $lawyerId
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @param int|null $excludeAppointmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    /**
     * Find all conflicting appointments using inclusive time-range overlap.
     * Overlap condition: existingStart < newEnd AND existingEnd > newStart
     *
     * Using PHP/Carbon to avoid DB-engine-specific date math (e.g., MySQL's DATE_ADD
     * is not compatible with SQLite). We load only the minimal column set and
     * filter in memory — this is safe given appointment volumes.
     *
     * @param int $lawyerId
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @param int|null $excludeAppointmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private static function findConflictingAppointments(int $lawyerId, Carbon $startTime, Carbon $endTime, ?int $excludeAppointmentId = null)
    {
        $query = Appointment::where('lawyer_id', $lawyerId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->select(['id', 'appointment_at', 'duration_minutes', 'status']);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->get()->filter(function ($appt) use ($startTime, $endTime) {
            $existingStart = $appt->appointment_at;
            $existingEnd   = $appt->appointment_at->copy()->addMinutes($appt->duration_minutes);

            // Standard interval overlap: start < other_end AND end > other_start
            return $startTime->lt($existingEnd) && $endTime->gt($existingStart);
        });
    }


    /**
     * Get available time slots for lawyer on a specific date
     * 
     * @param int $lawyerId
     * @param Carbon $date
     * @param int $slotDurationMinutes Minimum duration for a time slot
     * @param string $workStartTime Format: "H:i" (e.g., "09:00")
     * @param string $workEndTime Format: "H:i" (e.g., "17:00")
     * @return array List of available time slots
     */
    public static function getAvailableSlots(int $lawyerId, Carbon $date, int $slotDurationMinutes = 30, string $workStartTime = "09:00", string $workEndTime = "17:00"): array
    {
        $slots = [];
        $dayStart = $date->copy()->setTimeFromTimeString($workStartTime);
        $dayEnd = $date->copy()->setTimeFromTimeString($workEndTime);

        // Get all appointments for this lawyer on this date
        $appointments = Appointment::where('lawyer_id', $lawyerId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('appointment_at', $date->toDateString())
            ->orderBy('appointment_at')
            ->get();

        $currentTime = $dayStart->copy();

        foreach ($appointments as $appointment) {
            $appointmentStart = $appointment->appointment_at;

            // Add available slots before this appointment
            while ($currentTime->copy()->addMinutes($slotDurationMinutes) <= $appointmentStart) {
                $slots[] = [
                    'start' => $currentTime->toIso8601String(),
                    'end' => $currentTime->copy()->addMinutes($slotDurationMinutes)->toIso8601String(),
                    'duration_minutes' => $slotDurationMinutes,
                ];
                $currentTime->addMinutes($slotDurationMinutes);
            }

            // Move current time to after this appointment
            $currentTime = $appointmentStart->copy()->addMinutes($appointment->duration_minutes);
        }

        // Add remaining slots until end of day
        while ($currentTime->copy()->addMinutes($slotDurationMinutes) <= $dayEnd) {
            $slots[] = [
                'start' => $currentTime->toIso8601String(),
                'end' => $currentTime->copy()->addMinutes($slotDurationMinutes)->toIso8601String(),
                'duration_minutes' => $slotDurationMinutes,
            ];
            $currentTime->addMinutes($slotDurationMinutes);
        }

        return $slots;
    }

    /**
     * Validate appointment update for time conflicts
     * 
     * @param Appointment $appointment
     * @param Carbon $newStartTime
     * @return array
     */
    public static function validateUpdate(Appointment $appointment, Carbon $newStartTime): array
    {
        return self::checkAvailability(
            $appointment->lawyer_id,
            $newStartTime,
            $appointment->duration_minutes,
            $appointment->id
        );
    }

    /**
     * Get lawyer's schedule for a date range
     * 
     * @param int $lawyerId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public static function getScheduleRange(int $lawyerId, Carbon $startDate, Carbon $endDate): array
    {
        $appointments = Appointment::where('lawyer_id', $lawyerId)
            ->whereBetween('appointment_at', [$startDate, $endDate])
            ->with('client:id,name')
            ->orderBy('appointment_at')
            ->get();

        return $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'title' => 'Appointment with ' . $appointment->client->name,
                'start' => $appointment->appointment_at->toIso8601String(),
                'end' => $appointment->end_time->toIso8601String(),
                'status' => $appointment->status,
                'client_name' => $appointment->client->name,
                'purpose' => $appointment->purpose,
            ];
        })->toArray();
    }
}
