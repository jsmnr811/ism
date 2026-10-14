<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <div class="mx-auto max-w-4xl px-4 py-8 lg:px-6 lg:py-12">
            {{ $slot }}
        </div>
    </body>
</html>
