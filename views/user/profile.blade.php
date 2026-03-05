@extends('layouts.app')

@section('title', 'Tài khoản của bạn - FilmStream')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-semibold mb-2">Tài khoản của bạn</h1>
        <p class="text-xs text-zinc-400">
            Quản lý thông tin cá nhân, phim yêu thích và lịch sử xem.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[260px,1fr] gap-6">
        <div class="space-y-4">
            <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-primary-600 flex items-center justify-center text-xl font-semibold mb-2">
                    {{ strtoupper(substr($user['name'] ?? $user['email'], 0, 1)) }}
                </div>
                <div class="font-semibold text-sm mb-1">
                    {{ $user['name'] ?? $user['email'] }}
                </div>
                <div class="text-xs text-zinc-400 mb-2">{{ $user['email'] }}</div>
                <div class="text-[11px] px-2 py-1 rounded-full bg-zinc-800 border border-zinc-700">
                    Vai trò: {{ ($user['role'] ?? 'user') === 'admin' ? 'Quản trị viên' : 'Người dùng' }}
                </div>
            </div>

            <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4 text-xs space-y-2">
                <div class="font-semibold text-zinc-200 mb-1">Tùy chọn</div>
                <button
                    type="button"
                    class="w-full text-left px-3 py-2 rounded-md bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-xs"
                >
                    Đổi mật khẩu (UI placeholder)
                </button>
                <button
                    type="button"
                    class="w-full text-left px-3 py-2 rounded-md bg-zinc-950 hover:bg-zinc-800 border border-zinc-800 text-xs"
                >
                    Cập nhật avatar (UI placeholder)
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold">Phim yêu thích</h2>
                </div>
                @if(empty($favoriteMovies))
                    <p class="text-xs text-zinc-500">
                        Bạn chưa lưu phim nào. Hãy nhấn nút <span class="text-primary-400">☆ Thêm vào yêu thích</span> ở trang phim.
                    </p>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($favoriteMovies as $fav)
                            <a
                                href="{{ route('/phim/' . $fav['slug']) }}"
                                class="group relative bg-zinc-900 rounded-md overflow-hidden"
                            >
                                <div class="aspect-[2/3] bg-zinc-800 group-hover:scale-[1.03] transition-transform duration-300"></div>
                                <div class="p-2">
                                    <div class="text-xs font-medium text-zinc-100 group-hover:text-white line-clamp-2">
                                        {{ $fav['title'] }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold">Lịch sử xem gần đây</h2>
                </div>
                @if(empty($watchHistory))
                    <p class="text-xs text-zinc-500">
                        Chưa có lịch sử xem. Khi bạn xem phim, hệ thống sẽ lưu lại tập và tiến độ.
                    </p>
                @else
                    <div class="space-y-3 text-xs">
                        @foreach($watchHistory as $item)
                            <a
                                href="{{ route('/phim/' . $item['slug']) }}"
                                class="flex items-center justify-between px-3 py-2 rounded-md bg-zinc-950 hover:bg-zinc-800 border border-zinc-800"
                            >
                                <div class="flex-1">
                                    <div class="font-medium text-zinc-100 mb-0.5">
                                        {{ $item['title'] }}
                                    </div>
                                    <div class="text-[11px] text-zinc-400">
                                        Tập {{ $item['episode'] }} · Đã xem {{ $item['progress'] }}%
                                    </div>
                                </div>
                                <div class="w-20 h-1.5 rounded-full bg-zinc-800 overflow-hidden">
                                    <div
                                        class="h-full bg-primary-600"
                                        style="width: {{ $item['progress'] }}%;"
                                    ></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

