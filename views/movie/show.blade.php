@extends('layouts.app')

@section('title', $movie['title'] . ' - FilmStream')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-[260px,1fr] gap-6 mb-8">
        <div class="space-y-3">
            <div class="overflow-hidden rounded-xl bg-zinc-900 aspect-[2/3]">
                @if($movie['poster_url'])
                    <img
                        src="{{ file_url($movie['poster_url']) }}"
                        alt="{{ $movie['title'] }}"
                        class="w-full h-full object-cover"
                    >
                @else
                    <div class="w-full h-full bg-zinc-800 flex items-center justify-center text-zinc-500 text-xs">
                        Poster đang cập nhật
                    </div>
                @endif
            </div>
            <div class="bg-zinc-900/80 rounded-xl p-3 space-y-1 text-xs text-zinc-300">
                <div class="flex justify-between">
                    <span>Năm phát hành</span>
                    <span class="font-medium">{{ $movie['year'] ?? 'Đang cập nhật' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Thời lượng</span>
                    <span class="font-medium">{{ $movie['duration'] ? $movie['duration'] . ' phút' : 'Đang cập nhật' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Trạng thái</span>
                    <span class="font-medium">
                        {{ $movie['status'] === 'ongoing' ? 'Đang chiếu' : 'Hoàn thành' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Lượt xem</span>
                    <span class="font-medium">{{ number_format($movie['views']) }}</span>
                </div>
                <div class="flex justify-between items-center pt-1">
                    <span>Đánh giá</span>
                    <span class="font-medium text-amber-400">
                        ★ {{ $movie['rating_avg'] }} <span class="text-[10px] text-zinc-400">({{ $movie['rating_count'] }})</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1">
                    {{ $movie['title'] }}
                </h1>
                @if(!empty($movie['original_title']))
                    <p class="text-sm text-zinc-400 mb-1">
                        {{ $movie['original_title'] }}
                    </p>
                @endif
                <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-300 mb-3">
                    @if(!empty($movie['categories']))
                        @foreach($movie['categories'] as $cat)
                            <span class="px-2 py-1 rounded-full bg-zinc-800 border border-zinc-700">
                                {{ $cat }}
                            </span>
                        @endforeach
                    @endif
                    @if(!empty($movie['countries']))
                        <span class="px-2 py-1 rounded-full bg-zinc-800 border border-zinc-700">
                            {{ implode(', ', $movie['countries']) }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-zinc-300 leading-relaxed">
                    {{ $movie['description'] }}
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('/xem/' . $movie['slug'] . '/1') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-sm font-semibold"
                    >
                        ▶ Xem phim
                    </a>
                    <button
                        type="button"
                        id="favorite-toggle-btn"
                        data-movie-id="{{ $movie['id'] }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-md border border-zinc-600 hover:border-primary-500 text-sm"
                    >
                        ☆ Thêm vào yêu thích
                    </button>
                </div>
            </div>

            <div class="bg-zinc-900/70 rounded-2xl border border-zinc-800 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold">Danh sách tập phim</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($episodes as $ep)
                        <a
                            href="{{ route('/xem/' . $movie['slug'] . '/' . $ep['episode_number']) }}"
                            class="px-3 py-1.5 rounded-full text-xs bg-zinc-800 hover:bg-primary-600 hover:text-white transition"
                        >
                            Tập {{ $ep['episode_number'] }}{{ $ep['title'] ? ' - ' . $ep['title'] : '' }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-zinc-900/70 rounded-2xl border border-zinc-800 p-4">
                <h2 class="text-lg font-semibold mb-3">Bình luận</h2>
                <div class="space-y-4">
                    @php($user = auth_user())
                    @if($user)
                        <form id="comment-form" class="space-y-2" data-movie-id="{{ $movie['id'] }}">
                            <textarea
                                name="content"
                                rows="3"
                                class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                placeholder="Chia sẻ cảm nhận của bạn về bộ phim này..."
                            ></textarea>
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-sm font-semibold"
                                >
                                    Gửi bình luận
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-xs text-zinc-400">
                            Bạn cần
                            <button
                                type="button"
                                data-open-auth-modal="login"
                                class="text-primary-400 hover:text-primary-300"
                            >
                                đăng nhập
                            </button>
                            để bình luận.
                        </div>
                    @endif

                    <div id="comments-list" class="space-y-3 text-sm">
                        <p class="text-zinc-500 text-xs">
                            Đang tải bình luận...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h2 class="text-lg md:text-xl font-semibold mb-3">Phim liên quan</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-6 gap-3">
            @foreach($relatedMovies as $rm)
                <a
                    href="{{ route('/phim/' . $rm['slug']) }}"
                    class="group relative bg-zinc-900/80 rounded-md overflow-hidden"
                >
                    <div class="aspect-[2/3] bg-zinc-800 group-hover:scale-[1.03] transition-transform duration-300"></div>
                    <div class="p-2">
                        <div class="text-xs font-medium text-zinc-100 group-hover:text-white line-clamp-2">
                            {{ $rm['title'] }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <script>
        (function () {
            const movieId = {{ (int)$movie['id'] }};

            // Toggle yêu thích
            const favBtn = document.getElementById('favorite-toggle-btn');
            if (favBtn) {
                favBtn.addEventListener('click', async () => {
                    try {
                        const formData = new FormData();
                        formData.append('movie_id', String(movieId));
                        const res = await fetch('{{ route('/api/yeu-thich/toggle') }}', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData,
                        });
                        const data = await res.json();
                        if (data.ok) {
                            showToast(data.message || 'Cập nhật yêu thích thành công');
                            favBtn.textContent = data.isFavorite ? '★ Đã yêu thích' : '☆ Thêm vào yêu thích';
                        } else {
                            showToast(data.error || 'Không thể cập nhật yêu thích', 'error');
                        }
                    } catch (e) {
                        showToast('Không thể kết nối máy chủ', 'error');
                    }
                });
            }

            // Bình luận: tải danh sách + gửi mới
            const commentsListEl = document.getElementById('comments-list');
            async function loadComments() {
                if (!commentsListEl) return;
                try {
                    const res = await fetch('{{ route('/api/phim') }}/' + movieId + '/binh-luan');
                    const data = await res.json();
                    if (!data.ok) {
                        commentsListEl.innerHTML = '<p class="text-xs text-zinc-500">Không thể tải bình luận.</p>';
                        return;
                    }
                    if (!data.comments.length) {
                        commentsListEl.innerHTML = '<p class="text-xs text-zinc-500">Chưa có bình luận nào. Hãy là người đầu tiên!</p>';
                        return;
                    }
                    commentsListEl.innerHTML = '';
                    data.comments.forEach((c) => {
                        const item = document.createElement('div');
                        item.className = 'rounded-md bg-zinc-900/80 border border-zinc-800 p-3';
                        item.innerHTML = `
                            <div class="text-xs font-semibold text-zinc-100 mb-1">${c.user_name ?? 'User'}</div>
                            <div class="text-sm text-zinc-200 mb-1">${c.content}</div>
                            <div class="text-[11px] text-zinc-500">${c.created_at || ''}</div>
                        `;
                        commentsListEl.appendChild(item);
                    });
                } catch (e) {
                    commentsListEl.innerHTML = '<p class="text-xs text-zinc-500">Không thể tải bình luận.</p>';
                }
            }

            loadComments();

            const commentForm = document.getElementById('comment-form');
            if (commentForm) {
                commentForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const textarea = commentForm.querySelector('textarea[name="content"]');
                    if (!textarea || !textarea.value.trim()) {
                        showToast('Vui lòng nhập nội dung bình luận', 'error');
                        return;
                    }
                    try {
                        const formData = new FormData();
                        formData.append('content', textarea.value);
                        const res = await fetch('{{ route('/api/phim') }}/' + movieId + '/binh-luan', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData,
                        });
                        const data = await res.json();
                        if (data.ok) {
                            textarea.value = '';
                            showToast('Đã gửi bình luận');
                            loadComments();
                        } else {
                            showToast(data.error || 'Không thể gửi bình luận', 'error');
                        }
                    } catch (e) {
                        showToast('Không thể kết nối máy chủ', 'error');
                    }
                });
            }
        })();
    </script>
@endsection

