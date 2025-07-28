<x-mail::message>
# Confirmación de tu Pedido #{{ $orderNumber }}

Hola {{ $userName }},

Hemos recibido tu pedido **#{{ $orderNumber }}** por un total de **${{ number_format($orderTotal, 2) }}**.
Has seleccionado **{{ $paymentMethodName }}** como tu método de pago.

**Instrucciones para el pago:**

{{ $paymentInstructions }}

Una vez que hayamos confirmado tu pago, tu pedido será procesado y tu cuenta será activada completamente (si este es tu primer pedido de activación).

Puedes ver los detalles de tu pedido en tu panel de control una vez que tu cuenta esté activa.
<x-mail::button :url="route('login')">
Ver mis Pedidos (después de activar)
</x-mail::button>

Si tienes alguna pregunta sobre cómo realizar el pago, por favor, contáctanos.

Gracias por tu compra,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
