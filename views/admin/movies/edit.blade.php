@extends('layouts.admin')

@section('title', 'Sửa phim - Admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-semibold mb-1">Sửa phim</h1>
        <p class="text-xs text-zinc-400">
            Cập nhật thông tin cho bộ phim <span class="text-zinc-200 font-semibold">{{ $movie['title'] }}</span>.
        </p>
    </div>

    @if($msg = getFlash('error'))
        <div class="mb-3 text-xs px-3 py-2 rounded-md bg-red-600/20 text-red-300 border border-red-600/50">
            {{ $msg }}
        </div>
    @endif

    <form action="{{ route('/admin/phim/' . $movie['id'] . '/sua') }}" method="post" enctype="multipart/form-data" class="space-y-4 max-w-3xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Tiêu đề</label>
                <input
                    type="text"
                    name="title"
                    value="{{ $movie['title'] }}"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    required
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Slug (URL)</label>
                <input
                    type="text"
                    name="slug"
                    value="{{ $movie['slug'] }}"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    required
                />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Năm</label>
                <input
                    type="number"
                    name="year"
                    value="{{ $movie['year'] }}"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    min="1900"
                    max="{{ date('Y') + 1 }}"
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Quốc gia</label>
                <input
                    type="text"
                    name="country"
                    value="{{ $movie['country'] }}"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Trạng thái</label>
                <select
                    name="status"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
                    <option value="ongoing" @if($movie['status'] === 'ongoing') selected @endif>Đang chiếu</option>
                    <option value="completed" @if($movie['status'] === 'completed') selected @endif>Hoàn thành</option>
                </select>
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-xs text-zinc-300">Mô tả</label>
            <textarea
                name="description"
                rows="4"
                class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                required
            >{{ $movie['description'] }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Poster hiện tại</label>
                <div class="aspect-[2/3] rounded-md overflow-hidden bg-zinc-900 border border-zinc-800 mb-2">
                    @if($movie['poster_url'])
                        <img
                            src="{{ file_url($movie['poster_url']) }}"
                            alt="{{ $movie['title'] }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[11px] text-zinc-500">
                            Chưa có poster
                        </div>
                    @endif
                </div>
                <input type="hidden" name="poster_url_old" value="{{ $movie['poster_url'] }}">
                <input
                    type="file"
                    name="poster"
                    accept="image/*"
                    class="w-full text-xs text-zinc-300"
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Banner hiện tại</label>
                <div class="aspect-[16/9] rounded-md overflow-hidden bg-zinc-900 border border-zinc-800 mb-2">
                    @if($movie['banner_url'])
                        <img
                            src="{{ file_url($movie['banner_url']) }}"
                            alt="{{ $movie['title'] }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[11px] text-zinc-500">
                            Chưa có banner
                        </div>
                    @endif
                </div>
                <input type="hidden" name="banner_url_old" value="{{ $movie['banner_url'] }}">
                <input
                    type="file"
                    name="banner"
                    accept="image/*"
                    class="w-full text-xs text-zinc-300"
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Trailer (URL video)</label>
                <input
                    type="text"
                    name="trailer_url"
                    value="{{ $movie['trailer_url'] }}"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    placeholder="https://..."
                />

                <div class="mt-4 space-y-2">
                    <label class="block text-xs text-zinc-300">Thể loại</label>
                    <div class="grid grid-cols-2 gap-2 text-xs max-h-40 overflow-y-auto pr-1">
                        @forelse($categories as $cat)
                            <label class="inline-flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="category_ids[]"
                                    value="{{ $cat['id'] }}"
                                    class="bg-zinc-900 border-zinc-700 rounded"
                                    @if(in_array($cat['id'], $selectedCategories)) checked @endif
                                />
                                <span>{{ $cat['name'] }}</span>
                            </label>
                        @empty
                            <p class="text-[11px] text-zinc-500">
                                Chưa có bảng thể loại hoặc chưa thêm thể loại nào.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-center gap-2 text-xs text-zinc-300">
                        <input
                            type="checkbox"
                            name="is_published"
                            value="1"
                            class="bg-zinc-900 border-zinc-700 rounded"
                            @if($movie['is_published']) checked @endif
                        />
                        <span>Hiển thị trên website</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a
                href="{{ route('/admin/phim') }}"
                class="px-4 py-2 rounded-md border border-zinc-700 text-xs hover:bg-zinc-900"
            >
                Quay lại danh sách
            </a>
            <button
                type="submit"
                class="px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-xs font-semibold"
            >
                Lưu thay đổi
            </button>
        </div>
    </form>
@endsection

