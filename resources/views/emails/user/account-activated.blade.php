<x-mail::message>
# ¡Tu Cuenta Ha Sido Activada!

Hola {{ $userName }},

¡Felicidades! Tu cuenta en {{ config('app.name') }} ha sido activada exitosamente.

@if($rankName !== 'N/A')
Has alcanzado el rango: **{{ $rankName }}**.
@else
Aún no has alcanzado un rango específico, ¡sigue adelante!
@endif

Ya puedes ingresar a tu oficina virtual y disfrutar de todos los beneficios.

<x-mail::button :url="$loginUrl">
Ingresar Ahora
</x-mail::button>

Gracias por unirte,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
