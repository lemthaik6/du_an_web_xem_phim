@php($user = auth())

<header class="sticky top-0 z-30 bg-gradient-to-b from-black/80 via-black/70 to-transparent backdrop-blur border-b border-zinc-900">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <a href="{{ route('/') }}" class="flex items-center gap-2">
                <span class="w-8 h-8 bg-primary-600 rounded-sm flex items-center justify-center font-bold text-white text-xl">F</span>
                <span class="hidden sm:inline font-semibold text-lg tracking-wide">FilmStream</span>
            </a>

            <nav class="hidden md:flex items-center gap-4 text-sm text-gray-300">
                <a href="{{ route('/') }}" class="hover:text-white">Trang chủ</a>
                <a href="{{ route('/tim-kiem') }}" class="hover:text-white">Phim lẻ</a>
                <a href="{{ route('/tim-kiem') }}" class="hover:text-white">Phim bộ</a>
                <a href="{{ route('/tim-kiem') }}" class="hover:text-white">Thể loại</a>
            </nav>
        </div>

        <div class="flex-1 max-w-xl mx-4 hidden md:block">
            <form action="{{ route('/tim-kiem') }}" method="get" class="relative">
                <input
                    type="text"
                    name="q"
                    placeholder="Tìm kiếm phim, diễn viên..."
                    class="w-full bg-zinc-800/80 border border-zinc-700 rounded-full pl-10 pr-4 py-2 text-sm text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    🔍
                </span>
            </form>
        </div>

        <div class="flex items-center gap-3">
            @if($user)
                <button
                    class="hidden sm:flex items-center gap-2 bg-zinc-800/80 hover:bg-zinc-700 text-sm px-3 py-1.5 rounded-full transition"
                >
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-600 text-xs font-semibold">
                        {{ strtoupper(substr($user['name'] ?? $user['email'], 0, 1)) }}
                    </span>
                    <span class="max-w-[120px] truncate">{{ $user['name'] ?? $user['email'] }}</span>
                </button>
                <form action="{{ route('/dang-xuat') }}" method="post">
                    <button
                        class="text-xs px-3 py-1.5 rounded-full border border-zinc-600 hover:border-primary-500 hover:text-primary-400 transition"
                    >
                        Đăng xuất
                    </button>
                </form>
            @else
                <button
                    type="button"
                    data-open-auth-modal="login"
                    class="text-sm px-3 py-1.5 rounded-full border border-zinc-600 hover:border-primary-500 hover:text-primary-400 transition"
                >
                    Đăng nhập
                </button>
                <button
                    type="button"
                    data-open-auth-modal="register"
                    class="hidden sm:inline-flex text-sm px-3 py-1.5 rounded-full bg-primary-600 hover:bg-primary-500 text-white transition"
                >
                    Đăng ký
                </button>
            @endif
        </div>
    </div>
</header>
