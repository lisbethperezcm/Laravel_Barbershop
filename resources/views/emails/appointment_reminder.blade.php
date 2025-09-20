@php
    $shopName = "VIP Stylist Barbershop";
    $shopAddress = "Calle Principal 123, Ciudad";
    $shopPhone = "+34 123 456 789";
    $shopEmail = "citas@VIPStylist.com";

    $customerName = ucwords(strtolower($appointment->client->person->first_name . ' ' . $appointment->client->person->last_name ?? 'Cliente'));
    $barberName   = ucwords(strtolower($appointment->barber->person->first_name . ' ' . $appointment->barber->person->last_name ?? 'Barbero'));
    $appointmentDate = isset($appointment->appointment_date)
        ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y')
        : 'Fecha no disponible';
    $startTime = isset($appointment->start_time)
        ? \Carbon\Carbon::parse($appointment->start_time)->format('h:i A')
        : 'Hora no disponible';
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de tu cita</title>
</head>
<body style="font-family: 'Helvetica Neue', Arial, sans-serif; background:#f4f4f4; padding:20px; text-align:center;">

    <div style="max-width:600px; margin:0 auto; background:#fff; border:1px solid #e0e0e0; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.05);">

        <!-- Encabezado -->
        <div style="background:#000; padding:20px;">
            <h1 style="color:#fff; margin:0; font-size:20px; font-weight:700;">
                {{ $shopName }}
            </h1>
        </div>

        <!-- Mensaje principal -->
        <div style="padding:25px 20px;">
            <h2 style="margin:0 0 10px; font-size:18px; color:#111;">
                Hola, {{ $customerName }}
            </h2>
            <p style="margin:0 0 15px; font-size:15px; color:#444; line-height:1.5;">
                Te recordamos que tienes una cita programada <strong>{{ $whenLabel }}</strong>:
            </p>
            <p style="margin:0 0 8px; font-size:15px; color:#111;"><strong>📅 {{ $appointmentDate }}</strong></p>
            <p style="margin:0 0 8px; font-size:15px; color:#111;"><strong>🕒 {{ $startTime }}</strong></p>
            <p style="margin:0 0 8px; font-size:15px; color:#111;"><strong>💈 Con: {{ $barberName }}</strong></p>
        </div>

        <!-- Nota -->
        <div style="padding:15px 20px; background:#f7f7f7; border-top:1px solid #eee;">
            <p style="margin:0; font-size:14px; color:#555;">
                Te sugerimos llegar <strong>10 minutos antes</strong> para asegurar la mejor atención.
            </p>
        </div>

        <!-- Footer -->
        <div style="padding:18px; border-top:1px solid #e5e5e5; background:#fafafa; font-size:12px; color:#777;">
            <strong style="color:#111;">{{ $shopName }}</strong><br>
            {{ $shopAddress }} · {{ $shopPhone }}<br>
            {{ $shopEmail }}
        </div>

    </div>

</body>
</html>
