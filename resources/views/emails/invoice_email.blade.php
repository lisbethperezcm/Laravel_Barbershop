@php
  use Carbon\Carbon;
    // ===== Datos del comercio =====
    $shopName    = $shopName    ?? "VIP Stylist Barbershop";
    $shopAddress = $shopAddress ?? "Calle Principal 123, Santo Domingo Este";
    $shopPhone   = $shopPhone   ?? "+1 (809) 000-0000";
    $shopEmail   = $shopEmail   ?? "citas@vipstylist.com";

    // ===== Fuente: $invoice (objeto/array) =====
    $inv = isset($invoice) ? $invoice : ($data['data'] ?? $data ?? null);

    // Helper seguro (objeto o array)
    $get = function($src, $key, $default = null) {
        if (is_array($src)) return $src[$key] ?? $default;
        if (is_object($src)) return $src->{$key} ?? $default;
        return $default;
    };

    // Función solo para ITBIS
    $formatItbis = fn($val) => 'RD$ ' . number_format((float)$val, 2, '.', ',');
    // Campos principales (SIN re-formatear)
    $invoiceId      = $get($inv, 'id', 'N/A');
    $appointmentId  = $get($inv, 'appointment_id', 'N/A');
    $status         = $get($inv, 'status', 'N/A');
    $clientName     = $get($inv, 'client_name', 'Cliente');
    $paymentType    = $get($inv, 'payment_type', 'N/A');
   $createdAtRaw = (string) $get($inv, 'created_at', '');
    $createdAt = $createdAtRaw 
        ? Carbon::parse($createdAtRaw)->format('d/m/Y') 
        : '';
    // Detalles
    $details = $get($inv, 'details', []);
    if (!is_array($details)) $details = collect($details)->toArray();

    // Totales: mostrar tal cual si vienen; si faltan, calcular Fallback sin tocar el render
    $subtotal = $get($inv, 'subtotal', null);
    $itbis    = $get($inv, 'itbis', null);
    $total    = $get($inv, 'total', null);

    if ($subtotal === null || $itbis === null || $total === null) {
        // Normalización SOLO para cálculo interno
        $toFloat = function($val) {
            if ($val === null || $val === '') return 0.0;
            $s = (string)$val;
            // Quita separadores de miles y unifica decimal en '.'
            $s = str_replace([' ', ','], ['', '.'], $s);
            // Si hay más de un punto, elimina los de miles (excepto el último decimal)
            if (substr_count($s, '.') > 1) {
                $last = strrpos($s, '.');
                $s = str_replace('.', '', substr($s, 0, $last)) . substr($s, $last);
            }
            return (float)$s;
        };

        $calcSubtotal = 0.0;
        $calcItbis    = 0.0;
        foreach ($details as $d) {
            $qty         = (int)($d['quantity'] ?? 1);
            $price       = $toFloat($d['price']    ?? 0);
            $lineSubtotal= $d['subtotal'] ?? ($qty * $price);
            $lineSubF    = $toFloat($lineSubtotal);
            $lineItbisF  = $toFloat($d['itbis'] ?? 0);
            $calcSubtotal += $lineSubF;
            $calcItbis    += $lineItbisF;
        }
        // Si falta alguno, completar con cálculo
        $subtotal = $subtotal ?? number_format($calcSubtotal, 2, '.', ',');
        $itbis    = $itbis    ?? number_format($calcItbis, 2, '.', ',');
        $total    = $total    ?? number_format($calcSubtotal + $calcItbis, 2, '.', ',');
    }

    // Color del estatus (visual)
    $statusBg = '#111827';
    if (is_string($status)) {
        $st = mb_strtolower($status, 'UTF-8');
        if (str_contains($st, 'pag'))  $statusBg = '#15803d';
        if (str_contains($st, 'pend')) $statusBg = '#b45309';
        if (str_contains($st, 'canc')) $statusBg = '#b91c1c';
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $invoiceId }} - {{ $shopName }}</title>
</head>
<body style="margin:0; padding:16px; background-color:#f4f4f4; font-family:'Helvetica Neue',Arial,sans-serif;">

<div style="max-width:600px; margin:0 auto; background:#fff; border:1px solid #e5e5e5; border-radius:8px; overflow:hidden;">
    <!-- Encabezado -->
    <div style="background:#000; padding:18px 20px; text-align:center;">
        <h1 style="color:#fff; margin:0; font-size:18px; font-weight:800; letter-spacing:.5px;">
            {{ $shopName }}
        </h1>
    </div>

    <!-- Título -->
    <div style="background:#f7f7f7; padding:10px 16px; border-bottom:1px solid #eee; text-align:center;">
        <p style="margin:0; font-size:12px; color:#6b7280; letter-spacing:.5px; text-transform:uppercase;">
            Factura #{{ $invoiceId }}
        </p>
    </div>

    <!-- Resumen -->
    <div style="padding:14px 16px;">
        <p style="margin:0 0 6px 0; font-size:14px; color:#111827; font-weight:600;">
            Estimado(a): {{ $clientName }}
        </p>
        <p style="margin:0; font-size:13px; color:#374151; line-height:1.45;">
            A continuación se detalla su factura.
        </p>
    </div>

    <!-- Meta -->
    <div style="padding:0 16px 10px;">
        <div style="display:inline-block; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:700; color:#fff; background:{{ $statusBg }};">
            {{ $status }}
        </div>
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top:10px; border-collapse:collapse;">
            <tr>
                <td style="font-size:12px; color:#6b7280; padding:2px 0; width:30%;">Fecha</td>
                <td style="font-size:12px; color:#111827; padding:2px 0;">{{ $createdAt }}</td>
            </tr>
            <tr>
                <td style="font-size:12px; color:#6b7280; padding:2px 0;">Forma de pago</td>
                <td style="font-size:12px; color:#111827; padding:2px 0;">{{ $paymentType }}</td>
            </tr>
            <tr>
                <td style="font-size:12px; color:#6b7280; padding:2px 0;">Cita</td>
                <td style="font-size:12px; color:#111827; padding:2px 0;">#{{ $appointmentId }}</td>
            </tr>
        </table>
    </div>

    <!-- Detalle -->
    <div style="padding:0 16px 14px;">
        <div style="background:#fafafa; border:1px solid #eee; border-radius:6px; overflow:hidden;">
            <div style="background:#111827; padding:8px 12px;">
                <p style="margin:0; color:#fff; font-size:12px; font-weight:600; letter-spacing:.3px; text-transform:uppercase;">Detalle</p>
            </div>
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                <thead>
                    <tr>
                        <th align="left"  style="padding:8px 12px; font-size:11px; color:#6b7280; border-bottom:1px solid #eee; text-transform:uppercase; letter-spacing:.3px;">Tipo</th>
                        <th align="left"  style="padding:8px 0;   font-size:11px; color:#6b7280; border-bottom:1px solid #eee; text-transform:uppercase; letter-spacing:.3px;">Descripción</th>
                        <th align="center"style="padding:8px 0;   font-size:11px; color:#6b7280; border-bottom:1px solid #eee; text-transform:uppercase; letter-spacing:.3px;">Cant.</th>
                        <th align="right" style="padding:8px 12px; font-size:11px; color:#6b7280; border-bottom:1px solid #eee; text-transform:uppercase; letter-spacing:.3px;">Precio</th>
                        <th align="right" style="padding:8px 12px; font-size:11px; color:#6b7280; border-bottom:1px solid #eee; text-transform:uppercase; letter-spacing:.3px;">ITBIS</th>
                        <th align="right" style="padding:8px 12px; font-size:11px; color:#6b7280; border-bottom:1px solid #eee; text-transform:uppercase; letter-spacing:.3px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($details as $row)
                        @php
                            $rowType     = $row['type']     ?? '';
                            $rowName     = $row['name']     ?? '';
                            $rowQty      = (int)($row['quantity'] ?? 1);
                            $rowPrice    = $row['price']    ?? 0; // ya formateado o número
                            $rowItbis    =  $formatItbis($row['itbis'])    ?? 0; // ya formateado o número
                            $rowSubtotal = $row['subtotal'] ?? ($rowQty * (is_numeric($rowPrice) ? $rowPrice : 0));
                        @endphp
                        <tr>
                            <td style="padding:8px 12px; font-size:12px; color:#374151; border-bottom:1px solid #eee;">
                                {{ $rowType === 'service' ? 'Servicio' : 'Producto' }}
                            </td>
                            <td style="padding:8px 0; font-size:12px; color:#374151; border-bottom:1px solid #eee;">
                                {{ $rowName }}
                            </td>
                            <td align="center" style="padding:8px 0; font-size:12px; color:#374151; border-bottom:1px solid #eee;">
                                {{ $rowQty }}
                            </td>
                            <td align="right" style="padding:8px 12px; font-size:12px; color:#374151; border-bottom:1px solid #eee;">
                                {{ $rowPrice }}
                            </td>
                            <td align="right" style="padding:8px 12px; font-size:12px; color:#374151; border-bottom:1px solid #eee;">
                                {{ $rowItbis }}
                            </td>
                            <td align="right" style="padding:8px 12px; font-size:12px; color:#374151; border-bottom:1px solid #eee;">
                                {{ $rowSubtotal }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" align="center" style="padding:10px 12px; font-size:12px; color:#6b7280;">
                                No hay conceptos registrados en esta factura.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totales (tal cual vienen; si faltan, ya se calcularon arriba) -->
    <div style="padding:0 16px 16px;">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse; background:#f7f7f7; border:1px solid #eaeaea; border-radius:6px;">
            <tr>
                <td align="right" style="padding:8px 12px; font-size:12px; color:#374151;"><strong>Subtotal</strong></td>
                <td align="right" style="padding:8px 12px; font-size:12px; color:#374151; width:140px;">{{ $subtotal }}</td>
            </tr>
            <tr>
                <td align="right" style="padding:6px 12px; font-size:12px; color:#374151;"><strong>ITBIS</strong></td>
                <td align="right" style="padding:6px 12px; font-size:12px; color:#374151;">{{ $formatItbis($itbis) }}</td>
            </tr>
            <tr>
                <td align="right" style="padding:8px 12px; font-size:13px; color:#111827;"><strong>Total</strong></td>
                <td align="right" style="padding:8px 12px; font-size:13px; color:#111827;"><strong>{{ $total }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Pie -->
    <div style="border-top:1px solid #e5e5e5; padding:14px 16px; background:#fafafa; text-align:center;">
        <p style="margin:0 0 6px 0; font-size:12px; color:#111827; font-weight:600;">{{ $shopName }}</p>
        <p style="margin:0; font-size:12px; color:#6b7280; line-height:1.4;">
            {{ $shopAddress }} · Tel. {{ $shopPhone }} · {{ $shopEmail }}
        </p>
        <p style="margin:6px 0 0 0; font-size:11px; color:#9ca3af;">
            © {{ date('Y') }} {{ $shopName }}. Todos los derechos reservados.
        </p>
    </div>
</div>

</body>
</html>
