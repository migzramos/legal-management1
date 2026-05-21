<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine if user can view appointment
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // Client or lawyer assigned to appointment can view
        return $user->id === $appointment->client_id 
            || $user->id === $appointment->lawyer_id
            || $user->role === 'admin';
    }

    /**
     * Determine if user can update appointment
     */
    public function update(User $user, Appointment $appointment): bool
    {
        // Only the assigned lawyer can update (confirm/reject)
        return $user->id === $appointment->lawyer_id;
    }

    /**
     * Determine if user can cancel appointment
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        // Both client and lawyer can cancel
        return $user->id === $appointment->client_id 
            || $user->id === $appointment->lawyer_id;
    }

    /**
     * Determine if user can send messages in appointment thread
     */
    public function sendMessage(User $user, Appointment $appointment): bool
    {
        // Only client and lawyer assigned to appointment can send messages
        return $user->id === $appointment->client_id 
            || $user->id === $appointment->lawyer_id;
    }

    /**
     * Determine if user can manage messages (delete, restore)
     */
    public function manageMessages(User $user, Appointment $appointment): bool
    {
        // Only client and lawyer assigned to appointment can manage
        return $user->id === $appointment->client_id 
            || $user->id === $appointment->lawyer_id;
    }
}
