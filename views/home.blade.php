@extends('layouts.app')

@section('title', 'FilmStream - Xem phim online')

@section('content')
    {{-- Banner slider (placeholder, sau này nối với DB/banner table) --}}
    <section class="mb-8">
        <div class="relative h-[220px] md:h-[360px] rounded-2xl overflow-hidden bg-gradient-to-r from-zinc-900 via-zinc-800 to-zinc-900">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_60%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
            <div class="relative h-full flex flex-col justify-end p-6 md:p-10">
                <div class="max-w-lg">
                    <p class="text-xs uppercase tracking-[0.2em] text-zinc-300 mb-2">Phim nổi bật</p>
                    <h1 class="text-2xl md:text-4xl font-bold mb-3">Khám phá kho phim trực tuyến chất lượng cao</h1>
                    <p class="text-sm text-zinc-300 mb-4 hidden md:block">
                        Xem phim bom tấn, phim bộ, hoạt hình, và nhiều hơn nữa với giao diện hiện đại, tốc độ nhanh, trải nghiệm mượt mà.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('/tim-kiem') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-sm font-semibold">
                            ▶ Xem ngay
                        </a>
                        <a href="{{ route('/tim-kiem') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-zinc-600 hover:border-zinc-400 text-sm">
                            ⓘ Danh sách phim
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Sections phim (new/hot/genre/etc.) --}}
    @foreach($sections as $section)
        <section class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg md:text-xl font-semibold">{{ $section['title'] }}</h2>
                <a href="{{ route('/tim-kiem') . '?section=' . $section['slug'] }}" class="text-xs text-zinc-400 hover:text-zinc-200">
                    Xem tất cả
                </a>
            </div>

            {{-- Skeleton khi chưa có dữ liệu (demo) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @for($i = 0; $i < 6; $i++)
                    <div class="group relative bg-zinc-900/80 rounded-md overflow-hidden">
                        <div class="aspect-[2/3] bg-zinc-800 animate-pulse"></div>
                        <div class="p-2 space-y-1">
                            <div class="h-3 bg-zinc-800 rounded w-3/4 animate-pulse"></div>
                            <div class="h-2 bg-zinc-800 rounded w-1/2 animate-pulse"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </section>
    @endforeach
@endsection

