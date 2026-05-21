<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details — LegalCase</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @include('client.partials.styles')
</head>
<body>
<div class="bg-scene"></div>
<div class="app">
    @include('client.partials.sidebar')
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <h1>Appointment Details</h1>
                <p>Review the appointment information and status.</p>
            </div>
            <div class="topbar-right">
                <a href="{{ route('client.appointments.index') }}" class="btn-secondary">Back to Appointments</a>
            </div>
        </div>
        <div class="content">
            <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_1fr] gap-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-xl font-serif font-semibold text-white mb-4">{{ $appointment->purpose ?? 'Appointment' }}</h2>
                        <div class="space-y-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Lawyer</label>
                                <span class="text-white">{{ $appointment->lawyer->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Date & Time</label>
                                <span class="text-white">{{ $appointment->appointment_at?->format('M d, Y g:i A') ?? 'TBD' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Duration</label>
                                <span class="text-white">{{ $appointment->duration_minutes ?? 60 }} minutes</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Hourly Rate</label>
                                <span class="text-white">{{ money_display($appointment->hourly_rate ?? 0) }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Estimated Total Cost</label>
                                <span class="text-white">{{ money_display(($appointment->hourly_rate ?? 0) * (($appointment->duration_minutes ?? 60) / 60)) }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Status</label>
                                <span class="status-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-gray-500 uppercase tracking-wider">Notes</label>
                                <span class="text-white">{{ $appointment->notes ?? 'No additional notes.' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-xl font-serif font-semibold text-white mb-4">Actions</h2>
                        <p class="text-gray-400 leading-relaxed mb-6">You can cancel this appointment if it is still pending or confirmed.</p>
                        @if(in_array($appointment->status, ['pending','confirmed']))
                            <form method="POST" action="{{ route('client.appointments.cancel', $appointment->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-secondary w-full">Cancel Appointment</button>
                            </form>
                        @else
                            <div class="text-center py-8">
                                <h3 class="text-lg font-medium text-white mb-2">No actions available</h3>
                                <p class="text-gray-400">This appointment cannot be modified at this time.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
