<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            500: '#e50914',
                            600: '#b20710',
                        },
                        surface: '#141414',
                        'surface-soft': '#181818',
                    }
                }
            }
        }
    </script>
</head>
<body class="dark bg-black text-gray-100">

@include('partials.header')

<div class="min-h-screen flex">
    <aside class="w-64 bg-surface-soft border-r border-zinc-800 hidden md:flex md:flex-col">
        @include('partials.aside')
    </aside>

    <main class="flex-1 min-h-screen bg-surface p-4 md:p-6">
        <div class="max-w-7xl mx-auto">
            @yield('content')
        </div>
    </main>
</div>

@include('partials.footer')

<script>
    // Toggle mobile sidebar (nếu sau này cần)
</script>
</body>
</html>
