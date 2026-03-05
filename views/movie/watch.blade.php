@extends('layouts.app')

@section('title', 'Xem ' . $movie['title'] . ' - Tập ' . ($currentEpisode['episode_number'] ?? '?'))

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,3fr),minmax(260px,1fr)] gap-6">
        <div>
            <div class="mb-3">
                <p class="text-xs text-zinc-400 mb-1">
                    {{ $movie['title'] }}
                </p>
                <h1 class="text-xl md:text-2xl font-semibold">
                    Tập {{ $currentEpisode['episode_number'] ?? '?' }}
                    @if(!empty($currentEpisode['title']))
                        - {{ $currentEpisode['title'] }}
                    @endif
                </h1>
            </div>

            <div class="aspect-video bg-black rounded-2xl overflow-hidden border border-zinc-800 mb-3">
                <div class="w-full h-full flex items-center justify-center text-zinc-500 text-xs md:text-sm">
                    Player video demo<br>
                    (sau này gắn iframe / HTML5 video từ nguồn thật)
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 mb-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-900 border border-zinc-700 text-xs">
                    <span class="text-zinc-400">Server:</span>
                    <select class="bg-transparent text-zinc-100 outline-none">
                        @foreach($sources as $src)
                            <option class="bg-zinc-900" value="{{ $src['url'] }}">
                                {{ $src['server'] }} - {{ $src['quality'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-900 border border-zinc-700 text-xs"
                >
                    💾 Lưu tiến độ xem
                </button>
            </div>

            <div class="bg-zinc-900/70 rounded-2xl border border-zinc-800 p-4 mb-6">
                <h2 class="text-lg font-semibold mb-2">Mô tả tập phim</h2>
                <p class="text-sm text-zinc-300">
                    Nội dung mô tả tập phim demo. Sau này có thể lưu mô tả riêng cho từng tập, hoặc dùng mô tả chung của phim.
                </p>
            </div>

            <div class="bg-zinc-900/70 rounded-2xl border border-zinc-800 p-4">
                <h2 class="text-lg font-semibold mb-3">Bình luận</h2>
                <p class="text-xs text-zinc-500 mb-2">
                    Phần bình luận AJAX sẽ được nối với API `/api/phim/{id}/binh-luan` ở bước tiếp theo.
                </p>
                <div class="h-24 rounded-md border border-dashed border-zinc-700 flex items-center justify-center text-xs text-zinc-500">
                    Khu vực bình luận (dang skeleton UI).
                </div>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="bg-zinc-900/70 rounded-2xl border border-zinc-800 p-4">
                <h2 class="text-sm font-semibold mb-3">Danh sách tập</h2>
                <div class="max-h-[320px] overflow-y-auto space-y-1 pr-1 text-xs">
                    @foreach($episodes as $ep)
                        <a
                            href="{{ route('/xem/' . $movie['slug'] . '/' . $ep['episode_number']) }}"
                            class="flex items-center justify-between px-3 py-2 rounded-md border
                                {{ ($currentEpisode['episode_number'] ?? null) === $ep['episode_number']
                                    ? 'bg-primary-600/20 border-primary-600 text-primary-200'
                                    : 'bg-zinc-900 border-zinc-800 hover:bg-zinc-800 text-zinc-200' }}"
                        >
                            <span>
                                Tập {{ $ep['episode_number'] }}
                                @if(!empty($ep['title']))
                                    - {{ $ep['title'] }}
                                @endif
                            </span>
                            @if(($currentEpisode['episode_number'] ?? null) === $ep['episode_number'])
                                <span>▶</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-zinc-900/70 rounded-2xl border border-zinc-800 p-4">
                <h2 class="text-sm font-semibold mb-3">Phim đề xuất</h2>
                <div class="space-y-2 text-xs">
                    <p class="text-zinc-500">
                        Sau này có thể gợi ý phim theo thể loại / lịch sử xem.
                    </p>
                </div>
            </div>
        </aside>
    </div>
@endsection

