<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a VIP Stylist</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f7; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 30px 15px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background: #ffffff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); overflow: hidden;">
                    <!-- Header -->
                    <tr>
                        <td align="center" bgcolor="#000000" style="padding: 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">VIP Stylist</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px; color: #333333;">
                            <h2 style="margin-top: 0; color: #0F172A;">Hola, {{ $notifiable->person->first_name }} 👋</h2>
                            <p style="font-size: 16px; line-height: 1.5;">
                                ¡Gracias por registrarte en nuestra barbería! 🎉  
                                Estamos felices de tenerte con nosotros.
                            </p>
                            <p style="font-size: 15px; line-height: 1.5; margin-top: 20px;">
                                Ahora puedes agendar tu primera cita con nosotros y disfrutar de nuestros servicios.  
                                Si tienes alguna duda, contáctanos. ¡Nos vemos pronto!
                            </p>
                        
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" bgcolor="#f4f4f7" style="padding: 20px; font-size: 13px; color: #666;">
                            © {{ date('Y') }} VIP Stylist. Todos los derechos reservados.<br>
                            <small>Este es un correo automático, por favor no responder.</small>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
