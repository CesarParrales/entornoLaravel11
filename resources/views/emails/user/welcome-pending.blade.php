<x-mail::message>
# ¡Bienvenido/a a {{ config('app.name') }}, {{ $userName }}!

Gracias por registrarte. Tu cuenta ha sido creada y tu primer pedido con el número **{{ $orderNumber }}** está registrado.

Actualmente, tu cuenta se encuentra pendiente de la confirmación del pago de este primer pedido. Una vez que el pago sea confirmado, tu cuenta será activada completamente y podrás disfrutar de todos los beneficios.

**Próximos pasos:**
1.  Realiza el pago de tu pedido **{{ $orderNumber }}** según las instrucciones que te hemos enviado en un correo separado (o que se te mostraron al finalizar el pedido).
2.  Una vez confirmado el pago, recibirás un correo de activación.

Si tienes alguna pregunta, no dudes en contactarnos.

<x-mail::button :url="route('login')">
Ir a mi cuenta
</x-mail::button>

Saludos,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
