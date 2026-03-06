@extends('layouts.app')

@section('title', 'Xem ' . $movie['title'] . ' - Tập ' . ($currentEpisode['episode_number'] ?? '?'))

@section('content')
    @php
        $prevEp = null;
        $nextEp = null;
        if (!empty($episodes) && !empty($currentEpisode['episode_number'])) {
            $numbers = array_column($episodes, 'episode_number');
            $idx = array_search($currentEpisode['episode_number'], $numbers, true);
            if ($idx !== false) {
                $prevEp = $episodes[$idx - 1]['episode_number'] ?? null;
                $nextEp = $episodes[$idx + 1]['episode_number'] ?? null;
            }
        }
    @endphp

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
                @if(!empty($sources[0]['url']))
                    <video
                        id="movie-player"
                        controls
                        class="w-full h-full"
                        poster="{{ $movie['banner_url'] ? file_url($movie['banner_url']) : '' }}"
                    >
                        <source src="{{ $sources[0]['url'] }}" type="video/mp4">
                        Trình duyệt của bạn không hỗ trợ thẻ video.
                    </video>
                @else
                    <div class="w-full h-full flex items-center justify-center text-zinc-500 text-xs md:text-sm">
                        Player video demo<br>
                        (chưa có nguồn video cho tập này)
                    </div>
                @endif
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
                    id="save-progress-btn"
                    data-movie-id="{{ $movie['id'] }}"
                    data-episode-number="{{ $currentEpisode['episode_number'] ?? 0 }}"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-900 border border-zinc-700 text-xs"
                >
                    💾 Lưu tiến độ xem
                </button>

                <div class="flex items-center gap-2 text-xs">
                    @if($prevEp)
                        <a
                            href="{{ route('/xem/' . $movie['slug'] . '/' . $prevEp) }}"
                            class="px-3 py-1.5 rounded-full bg-zinc-900 border border-zinc-700 hover:bg-zinc-800"
                        >
                            ⏮ Tập trước
                        </a>
                    @endif
                    @if($nextEp)
                        <a
                            href="{{ route('/xem/' . $movie['slug'] . '/' . $nextEp) }}"
                            class="px-3 py-1.5 rounded-full bg-zinc-900 border border-zinc-700 hover:bg-zinc-800"
                        >
                            Tập sau ⏭
                        </a>
                    @endif
                </div>
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

    <script>
        (function () {
            const btn = document.getElementById('save-progress-btn');
            if (!btn) return;

            const movieId = parseInt(btn.dataset.movieId || '0', 10);
            const episodeNumber = parseInt(btn.dataset.episodeNumber || '0', 10);
            const player = document.getElementById('movie-player');

            btn.addEventListener('click', async () => {
                let progress = 0;
                if (player && player.duration) {
                    progress = Math.round((player.currentTime / player.duration) * 100);
                } else {
                    progress = 100;
                }

                try {
                    const formData = new FormData();
                    formData.append('movie_id', String(movieId));
                    formData.append('episode_number', String(episodeNumber));
                    formData.append('progress', String(progress));

                    const res = await fetch('{{ route('/api/lich-su-xem/upsert') }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    });
                    const data = await res.json();
                    if (data.ok) {
                        showToast(data.message || 'Đã lưu tiến độ xem');
                    } else {
                        showToast(data.error || 'Không thể lưu tiến độ', 'error');
                    }
                } catch (e) {
                    showToast('Không thể kết nối máy chủ', 'error');
                }
            });
        })();
    </script>
@endsection

