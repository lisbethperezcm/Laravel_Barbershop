@php
    $shopName = "VIP Stylist Barbershop";
    $shopAddress = "Calle Principal 123, Ciudad";
    $shopPhone = "+34 123 456 789";
    $shopEmail = "citas@VIP Stylist.com";

    $appointmentId = $appointment->id ?? 'N/A';
    $customerName = ucwords(strtolower($appointment->client->person->first_name . ' ' . $appointment->client->person->last_name ?? 'Cliente no disponible'));
    $barberName = ucwords(strtolower($appointment->barber->person->first_name . ' ' . $appointment->barber->person->last_name ?? 'Barbero no disponible'));
    $appointmentDate = isset($appointment->appointment_date) ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') : 'Fecha no disponible';
    $startTime = isset($appointment->start_time) ? \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') : 'Hora no disponible';
    $endTime = isset($appointment->end_time) ? \Carbon\Carbon::parse($appointment->end_time)->format('h:i A') : 'Hora no disponible';
    
    
    $services = is_array($appointment->services) 
        ? implode(', ', $appointment->services) 
        : collect($appointment->services)->pluck('name')->implode(', ');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cita</title>
</head>
<body style="font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center;">

    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">

        <!-- Encabezado -->
        <div style="background-color: #000000; padding: 25px 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: 1px;">
                {{ $shopName }}
            </h1>
        </div>

        <!-- Banner de Confirmación -->
        <div style="background-color: #f8f8f8; padding: 15px 30px; text-align: center; border-bottom: 1px solid #eeeeee;">
            <p style="margin: 0; font-size: 14px; color: #666666; text-transform: uppercase; letter-spacing: 1px;">
                Confirmación de Cita 
            </p>
        </div>

        <!-- Mensaje de Bienvenida -->
        <div style="padding: 30px 30px 20px;">
            <h2 style="margin: 0 0 15px 0; font-size: 20px; font-weight: bold; color: #000000;">
                Hola, {{ $customerName }}
            </h2>
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #444444;">
                Tu cita ha sido confirmada. A continuación encontrarás los detalles de tu reserva:
            </p>
        </div>

        <!-- Detalles de la Cita -->
        <div style="padding: 0 30px 30px;">
            <div style="background-color: #f9f9f9; border-radius: 8px; border: 1px solid #eeeeee; margin-bottom: 25px;">
                <div style="background-color: #000000; padding: 12px 20px; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5px;">
                        Detalles de la Cita
                    </h3>
                </div>

                <div style="padding: 20px;">
                    <p style="margin: 0 0 10px 0; font-size: 15px; color: #555555;">
                        📅 <strong>Fecha:</strong> {{ $appointmentDate }}
                    </p>
                    <p style="margin: 0 0 10px 0; font-size: 15px; color: #555555;">
                        🕒 <strong>Hora:</strong>{{ $startTime }} - {{ $endTime }}
                    </p>
                    <p style="margin: 0 0 10px 0; font-size: 15px; color: #555555;">
                        💈 <strong>Barbero:</strong> {{ $barberName }}
                    </p>
                    <p style="margin: 0; font-size: 15px; color: #555555;">
                        ✂️ <strong>Servicios:</strong> {{ $services }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Información del Negocio -->
        <div style="margin: 0 30px 25px; padding: 20px; background-color: #f2f2f2; border-radius: 8px; border: 1px solid #e5e5e5;">
            <p style="margin: 0; font-size: 15px; line-height: 1.6; color: #444444;">
                📱 <strong style="color: #000000;">Más información en nuestra aplicación</strong>
            </p>
            <p style="margin: 0; font-size: 15px; color: #444444;">
                Consulta nuestra aplicación para ver más detalles sobre tu cita, gestionar tus reservas y mucho más.
            </p>
        </div>

        <!-- Recordatorio -->
        <div style="margin: 0 30px 25px; padding: 20px; background-color: #f2f2f2; border-radius: 8px; border: 1px solid #e5e5e5;">
            <p style="margin: 0; font-size: 15px; color: #444444;">
                ℹ️ <strong style="color: #000000;">Recordatorio:</strong> Te recomendamos llegar 10 minutos antes de tu cita. 
            </p>
        </div>

        <!-- Footer -->
        <div style="border-top: 1px solid #e0e0e0; padding: 25px 30px; background-color: #f9f9f9; text-align: center;">
            <p style="margin: 0 0 15px 0; font-size: 14px; color: #666666; line-height: 1.6;">
                <strong style="color: #000000; display: block; margin-bottom: 5px;">{{ $shopName }}</strong>
                {{ $shopAddress }}<br>
                Teléfono: {{ $shopPhone }} | Email: {{ $shopEmail }}
            </p>
            <p style="margin: 0; font-size: 13px; color: #999999;">
                © {{ date('Y') }} {{ $shopName }}. Todos los derechos reservados.
            </p>
        </div>

    </div>
</body>
</html>
