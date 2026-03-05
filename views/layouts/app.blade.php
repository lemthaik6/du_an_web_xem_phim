<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FilmStream')</title>

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

<main class="min-h-screen bg-gradient-to-b from-black via-surface to-black pt-4 pb-10">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        @yield('content')
    </div>
</main>

@include('partials.footer')

{{-- Toast notification --}}
<div id="toast-root" class="fixed inset-x-0 top-4 flex flex-col items-center gap-2 pointer-events-none z-40"></div>

{{-- Modal auth (login/register) --}}
@include('partials.auth-modal')

<script>
    const toastRoot = document.getElementById('toast-root');
    function showToast(message, variant = 'success') {
        if (!toastRoot) return;
        const el = document.createElement('div');
        el.className = `pointer-events-auto px-4 py-2 rounded-md shadow-lg text-sm ${
            variant === 'error'
                ? 'bg-red-600/90 text-white'
                : 'bg-zinc-800/90 text-white border border-zinc-700'
        }`;
        el.textContent = message;
        toastRoot.appendChild(el);
        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => el.remove(), 200);
        }, 3000);
    }

    // Simple auth modal toggling
    const authModal = document.getElementById('auth-modal');
    if (authModal) {
        document.querySelectorAll('[data-open-auth-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.getAttribute('data-open-auth-modal');
                authModal.dataset.mode = mode;
                authModal.classList.remove('hidden');
            });
        });
        authModal.addEventListener('click', (e) => {
            if (e.target.dataset.closeAuthModal === 'true') {
                authModal.classList.add('hidden');
            }
        });
    }
</script>
</body>
</html>

