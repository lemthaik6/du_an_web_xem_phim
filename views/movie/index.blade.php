@extends('layouts.app')

@section('title', 'Danh sách phim - FilmStream')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-2">Danh sách phim</h1>
        <p class="text-zinc-400">Tìm và lọc phim yêu thích của bạn</p>
    </div>

    {{-- Thanh tìm kiếm và lọc --}}
    <div class="bg-zinc-900/80 rounded-xl p-4 md:p-6 mb-6 border border-zinc-800">
        <form method="GET" action="{{ route('/tim-kiem') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Tìm kiếm theo tên --}}
            <div>
                <label class="block text-xs uppercase tracking-widest text-zinc-400 mb-2">Tên phim</label>
                <input
                    type="text"
                    name="q"
                    placeholder="Nhập tên phim..."
                    value="{{ $filters['q'] ?? '' }}"
                    class="w-full px-3 py-2 rounded-lg bg-zinc-800 border border-zinc-700 text-white placeholder-zinc-500 focus:outline-none focus:border-primary-500"
                >
            </div>

            {{-- Lọc theo năm --}}
            <div>
                <label class="block text-xs uppercase tracking-widest text-zinc-400 mb-2">Năm phát hành</label>
                <input
                    type="number"
                    name="year"
                    placeholder="VD: 2024"
                    value="{{ $filters['year'] ?? '' }}"
                    min="1900"
                    max="2099"
                    class="w-full px-3 py-2 rounded-lg bg-zinc-800 border border-zinc-700 text-white placeholder-zinc-500 focus:outline-none focus:border-primary-500"
                >
            </div>

            {{-- Nút tìm kiếm --}}
            <div class="flex items-end">
                <button
                    type="submit"
                    class="w-full px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-500 text-white font-medium text-sm transition-colors"
                >
                    🔍 Tìm kiếm
                </button>
            </div>

            {{-- Nút reset --}}
            <div class="flex items-end">
                <a
                    href="{{ route('/tim-kiem') }}"
                    class="w-full px-4 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white font-medium text-sm transition-colors text-center"
                >
                    ✕ Xóa lọc
                </a>
            </div>
        </form>
    </div>

    {{-- Thanh hiển thị kết quả --}}
    <div class="mb-4 text-sm text-zinc-400">
        @if(!empty($filters['q']))
            <p>
                Kết quả tìm kiếm cho "<strong>{{ $filters['q'] }}</strong>"
                <span class="text-zinc-500">({{ $total }} kết quả)</span>
            </p>
        @else
            <p>
                Tổng cộng <strong>{{ $total }}</strong> phim
            </p>
        @endif
    </div>

    {{-- Danh sách phim --}}
    @if(!empty($movies))
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 mb-6">
            @foreach($movies as $movie)
                <div class="group" data-movie-id="{{ $movie['id'] }}">
                    <a href="/phim/{{ $movie['slug'] }}" class="block relative rounded-lg overflow-hidden bg-zinc-900 aspect-[2/3]">
                        {{-- Poster --}}
                        @if(!empty($movie['poster_url']))
                            <img
                                src="{{ file_url($movie['poster_url']) }}"
                                alt="{{ $movie['title'] }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            >
                        @else
                            <div class="w-full h-full bg-zinc-800 flex items-center justify-center text-zinc-500 text-xs text-center p-2">
                                {{ $movie['title'] ?? 'Phim' }}
                            </div>
                        @endif

                        {{-- Overlay khi hover --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
                            <span class="text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity">▶</span>
                        </div>

                        {{-- Badge --}}
                        <div class="absolute top-2 right-2 px-2 py-1 rounded-md bg-primary-600/90 text-white text-xs font-semibold">
                            <span title="Lượt xem">👁 {{ number_format($movie['views_count'] ?? 0) }}</span>
                        </div>
                    </a>

                    {{-- Thông tin phim --}}
                    <div class="mt-2">
                        <h3 class="font-medium text-xs md:text-sm line-clamp-2 text-white group-hover:text-primary-400">
                            {{ $movie['title'] ?? 'Unknown' }}
                        </h3>
                        <p class="text-[10px] text-zinc-500 mt-1">
                            {{ $movie['year'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Phân trang --}}
        @if($totalPages > 1)
            <div class="flex items-center justify-center gap-2 mb-6">
                {{-- Nút trang trước --}}
                @if($page > 1)
                    <a
                        href="{{ route('/tim-kiem') }}?page={{ $page - 1 }}{{ !empty($filters['q']) ? '&q=' . urlencode($filters['q']) : '' }}"
                        class="px-3 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-sm transition-colors"
                    >
                        ← Trước
                    </a>
                @endif

                {{-- Số trang --}}
                @for($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++)
                    @if($p === $page)
                        <span class="px-3 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium">
                            {{ $p }}
                        </span>
                    @else
                        <a
                            href="{{ route('/tim-kiem') }}?page={{ $p }}{{ !empty($filters['q']) ? '&q=' . urlencode($filters['q']) : '' }}"
                            class="px-3 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-sm transition-colors"
                        >
                            {{ $p }}
                        </a>
                    @endif
                @endfor

                {{-- Nút trang sau --}}
                @if($page < $totalPages)
                    <a
                        href="{{ route('/tim-kiem') }}?page={{ $page + 1 }}{{ !empty($filters['q']) ? '&q=' . urlencode($filters['q']) : '' }}"
                        class="px-3 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-sm transition-colors"
                    >
                        Sau →
                    </a>
                @endif
            </div>
        @endif
    @else
        {{-- Không có kết quả --}}
        <div class="text-center py-12">
            <p class="text-zinc-400 text-lg mb-2">😞 Không tìm thấy phim nào</p>
            <p class="text-zinc-500 text-sm mb-4">
                Hãy thử tìm kiếm với từ khóa khác hoặc xóa các bộ lọc.
            </p>
            <a
                href="{{ route('/tim-kiem') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium transition-colors"
            >
                ✕ Xóa lọc
            </a>
        </div>
    @endif
@endsection
