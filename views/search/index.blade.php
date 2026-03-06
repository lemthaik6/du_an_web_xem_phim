@extends('layouts.app')

@section('title', 'Tìm kiếm phim - FilmStream')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-semibold mb-2">Tìm kiếm phim</h1>
        <p class="text-xs text-zinc-400">
            Lọc theo tên phim, thể loại, năm phát hành và quốc gia.
        </p>
    </div>

    <form method="get" action="{{ route('/tim-kiem') }}" class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4 mb-5 text-xs md:text-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block mb-1 text-zinc-300">Từ khóa</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    placeholder="Tên phim, diễn viên..."
                />
            </div>
            <div>
                <label class="block mb-1 text-zinc-300">Thể loại</label>
                <select
                    name="genre"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Tất cả</option>
                    <option value="hanh-dong" @if(($filters['genre'] ?? '') === 'hanh-dong') selected @endif>Hành động</option>
                    <option value="tinh-cam" @if(($filters['genre'] ?? '') === 'tinh-cam') selected @endif>Tình cảm</option>
                    <option value="hai" @if(($filters['genre'] ?? '') === 'hai') selected @endif>Hài</option>
                </select>
            </div>
            <div>
                <label class="block mb-1 text-zinc-300">Năm</label>
                <select
                    name="year"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Tất cả</option>
                    @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                        <option value="{{ $y }}" @if(($filters['year'] ?? '') == $y) selected @endif>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block mb-1 text-zinc-300">Quốc gia</label>
                <select
                    name="country"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Tất cả</option>
                    <option value="vn" @if(($filters['country'] ?? '') === 'vn') selected @endif>Việt Nam</option>
                    <option value="us" @if(($filters['country'] ?? '') === 'us') selected @endif>Mỹ</option>
                    <option value="kr" @if(($filters['country'] ?? '') === 'kr') selected @endif>Hàn Quốc</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between">
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-xs md:text-sm font-semibold"
            >
                🔍 Tìm kiếm
            </button>
            <div class="text-[11px] text-zinc-500">
                Kết quả demo sẽ xuất hiện khi bạn nhập từ khóa hoặc chọn bộ lọc.
            </div>
        </div>
    </form>

    @if(empty($movies))
        <div class="text-xs text-zinc-500">
            Chưa có kết quả. Hãy thử nhập tên phim hoặc chọn bộ lọc khác.
        </div>
    @else
        <div class="flex items-center justify-between mb-3 text-xs text-zinc-400">
            <span>
                Đã tìm thấy {{ $total }} kết quả.
            </span>
            @if($total > $perPage)
                <span>Trang {{ $page }}</span>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($movies as $movie)
                <a
                    href="{{ route('/phim/' . $movie['slug']) }}"
                    class="group relative bg-zinc-900/80 rounded-md overflow-hidden"
                >
                    <div class="aspect-[2/3] bg-zinc-800 group-hover:scale-[1.03] transition-transform duration-300"></div>
                    <div class="p-2">
                        <div class="text-xs font-medium text-zinc-100 group-hover:text-white line-clamp-2 mb-1">
                            {{ $movie['title'] }}
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-zinc-400">
                            <span>{{ $movie['year'] ?? 'Đang cập nhật' }}</span>
                            <span>{{ $movie['country'] ?? '' }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @php
            $totalPages = max(1, ceil($total / $perPage));
        @endphp
        @if($totalPages > 1)
            <div class="mt-4 flex justify-center">
                <div class="inline-flex items-center gap-1 text-xs text-zinc-400">
                    @for($p = 1; $p <= $totalPages; $p++)
                        <a
                            href="{{ route('/tim-kiem') . '?' . http_build_query(array_merge($filters, ['page' => $p])) }}"
                            class="px-2 py-1 rounded-md border border-zinc-800 {{ $p === $page ? 'bg-zinc-900' : 'hover:bg-zinc-900' }}"
                        >
                            {{ $p }}
                        </a>
                    @endfor
                </div>
            </div>
        @endif
    @endif
@endsection

