<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Tienda Orgánica</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white shadow-lg">
            <div class="container px-4 mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <a href="{{ url('/') }}" class="text-3xl font-bold text-amber-600 hover:text-amber-700 transition-colors">
                            {{ config('app.name', 'Orgánica') }}
                        </a>
                    </div>

                    <!-- Navegación Principal - Centrada -->
                    <nav class="hidden md:flex space-x-6 items-center">
                        <a href="{{ route('catalog.index') }}" class="px-3 py-2 text-base font-medium text-slate-700 rounded-md hover:text-amber-600 transition-colors">Catálogo</a>
                        <a href="#" class="px-3 py-2 text-base font-medium text-slate-700 rounded-md hover:text-amber-600 transition-colors">Novedades</a>
                        <a href="#" class="px-3 py-2 text-base font-medium text-slate-700 rounded-md hover:text-amber-600 transition-colors">Ofertas</a>
                        <a href="#" class="px-3 py-2 text-base font-medium text-slate-700 rounded-md hover:text-amber-600 transition-colors">Blog</a>
                        <a href="#" class="px-3 py-2 text-base font-medium text-slate-700 rounded-md hover:text-amber-600 transition-colors">Contacto</a>
                    </nav>

                    <!-- Acciones Derecha: Búsqueda, Usuario, Carrito -->
                    <div class="flex items-center space-x-4">
                        <button aria-label="Buscar" class="text-slate-600 hover:text-amber-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                        @auth
                            <a href="{{ route('dashboard') }}" aria-label="Mi Cuenta" class="text-slate-600 hover:text-amber-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" aria-label="Cerrar Sesión" class="text-slate-600 hover:text-amber-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 hover:text-amber-600 dark:text-slate-300 dark:hover:text-amber-500">Iniciar Sesión</a>
                            <a href="{{ route('register.form') }}" class="text-sm font-medium text-slate-700 hover:text-amber-600 dark:text-slate-300 dark:hover:text-amber-500">Registrarse</a>
                        @endauth
                        <livewire:cart-counter />
                    </div>
                    <!-- Botón Menú Móvil -->
                    <div class="flex items-center md:hidden">
                        <button class="text-slate-600 hover:text-amber-600 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Navegación Móvil (Placeholder) -->
            {{-- <div class="md:hidden">
                <a href="{{ route('catalog.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-amber-50">Catálogo</a>
                ... más enlaces ...
            </div> --}}
        </header>

        <!-- Page Content -->
        <main class="flex-grow py-8">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="mt-auto bg-slate-800 text-slate-300">
            <div class="container px-4 py-12 mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3 lg:grid-cols-4">
                    <div>
                        <h3 class="mb-4 text-xl font-semibold text-white">{{ config('app.name', 'Orgánica') }}</h3>
                        <p class="mb-4 text-sm">Tu tienda de confianza para productos orgánicos y saludables.</p>
                        <div class="flex space-x-4">
                            <a href="#" class="hover:text-amber-400 transition-colors">FB</a>
                            <a href="#" class="hover:text-amber-400 transition-colors">IG</a>
                            <a href="#" class="hover:text-amber-400 transition-colors">TW</a>
                        </div>
                    </div>
                    <div>
                        <h4 class="mb-4 text-lg font-semibold text-white">Enlaces Rápidos</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-amber-400 transition-colors">Sobre Nosotros</a></li>
                            <li><a href="{{ route('catalog.index') }}" class="hover:text-amber-400 transition-colors">Tienda</a></li>
                            <li><a href="#" class="hover:text-amber-400 transition-colors">Preguntas Frecuentes</a></li>
                            <li><a href="#" class="hover:text-amber-400 transition-colors">Contacto</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 text-lg font-semibold text-white">Categorías</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-amber-400 transition-colors">Frutas y Verduras</a></li>
                            <li><a href="#" class="hover:text-amber-400 transition-colors">Despensa</a></li>
                            <li><a href="#" class="hover:text-amber-400 transition-colors">Cuidado Personal</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 text-lg font-semibold text-white">Contacto</h4>
                        <p class="mb-2 text-sm">123 Calle Falsa, Ciudad, País</p>
                        <p class="mb-2 text-sm">info@example.com</p>
                        <p class="text-sm">+123 456 7890</p>
                    </div>
                </div>
                <div class="pt-8 mt-8 text-sm text-center border-t border-slate-700">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos los derechos reservados.
                    <p class="mt-2">Pagos Seguros con: VISA, MC, AMEX</p>
                </div>
            </div>
        </footer>
    </div>
    <livewire:global-notification />
    @livewireScripts
</body>
</html>