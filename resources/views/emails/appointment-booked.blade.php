<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Appointment Booked</title>
    <style>
        body { background: #f8fafc; color: #111827; margin: 0; padding: 0; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .container { width: 100%; max-width: 640px; margin: 0 auto; padding: 24px; }
        .card { background: #ffffff; border-radius: 18px; box-shadow: 0 18px 50px rgba(15,23,42,0.08); overflow: hidden; }
        .header { background: #4f46e5; color: #ffffff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 28px 32px; }
        .body h2 { margin-top: 0; font-size: 20px; color: #111827; }
        .body p { color: #4b5563; line-height: 1.7; }
        .details { background: #f3f4f6; border-radius: 14px; padding: 18px; margin: 20px 0; }
        .details p { margin: 8px 0; }
        .button { display: inline-block; background: #6d28d9; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 999px; font-weight: 700; }
        .footer { padding: 18px 32px 32px; color: #6b7280; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>LegalCase — Appointment Booked</h1>
            </div>
            <div class="body">
                <h2>New Appointment Request</h2>
                <p>A new appointment was booked by <strong>{{ $appointment->client->name }}</strong>.</p>
                <div class="details">
                    <p><strong>Date & Time</strong><br>{{ optional($appointment->appointment_at)->format('F j, Y \a\t g:i A') }}</p>
                    <p><strong>Purpose</strong><br>{{ $appointment->purpose ?? 'Not specified' }}</p>
                    <p><strong>Estimated Cost</strong><br>{{ money_display(($appointment->hourly_rate * $appointment->duration_minutes) / 60) }}</p>
                </div>
                <p>Please review the appointment and confirm it from your dashboard.</p>
                <a href="{{ route('lawyer.appointments.show', $appointment) }}" class="button">View Appointment</a>
            </div>
            <div class="footer">
                LegalCase — Philippine Legal Management System
            </div>
        </div>
    </div>
</body>
</html>
