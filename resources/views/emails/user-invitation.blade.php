<p>Hola,</p>

<p>Has sido invitado/a a <strong>Contacts DB</strong>.</p>

<p>Para aceptar la invitación y crear tu contraseña, abra este enlace:</p>

<p><a href="{{ $acceptUrl }}">{{ $acceptUrl }}</a></p>

<p>Este enlace expirará el {{ $invitation->expires_at->format('Y-m-d H:i') }}.</p>

<p>Si no esperabas este correo, puedes ignorarlo.</p>