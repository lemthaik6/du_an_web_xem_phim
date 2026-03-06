@extends('layouts.admin')

@section('title', 'Thêm phim mới - Admin')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-semibold mb-1">Thêm phim mới</h1>
        <p class="text-xs text-zinc-400">
            Nhập thông tin cơ bản cho bộ phim, slug dùng cho URL SEO.
        </p>
    </div>

    @if($msg = getFlash('error'))
        <div class="mb-3 text-xs px-3 py-2 rounded-md bg-red-600/20 text-red-300 border border-red-600/50">
            {{ $msg }}
        </div>
    @endif

    <form action="{{ route('/admin/phim/them') }}" method="post" enctype="multipart/form-data" class="space-y-4 max-w-3xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Tiêu đề</label>
                <input
                    type="text"
                    name="title"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    required
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Slug (URL)</label>
                <input
                    type="text"
                    name="slug"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    placeholder="vd: avengers-endgame"
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
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    placeholder="vd: Mỹ, Việt Nam..."
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Trạng thái</label>
                <select
                    name="status"
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
                    <option value="ongoing">Đang chiếu</option>
                    <option value="completed">Hoàn thành</option>
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
            ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Poster</label>
                <input
                    type="file"
                    name="poster"
                    accept="image/*"
                    class="w-full text-xs text-zinc-300"
                />
            </div>
            <div class="space-y-2">
                <label class="block text-xs text-zinc-300">Banner</label>
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
                    class="w-full bg-zinc-950 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                    placeholder="https://..."
                />
            </div>
        </div>

        <div class="space-y-2">
            <label class="block text-xs text-zinc-300">Thể loại</label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                @forelse($categories as $cat)
                    <label class="inline-flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="category_ids[]"
                            value="{{ $cat['id'] }}"
                            class="bg-zinc-900 border-zinc-700 rounded"
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

        <div class="flex items-center justify-between pt-2">
            <label class="flex items-center gap-2 text-xs text-zinc-300">
                <input
                    type="checkbox"
                    name="is_published"
                    value="1"
                    class="bg-zinc-900 border-zinc-700 rounded"
                    checked
                />
                <span>Hiển thị ngay trên website</span>
            </label>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('/admin/phim') }}"
                    class="px-4 py-2 rounded-md border border-zinc-700 text-xs hover:bg-zinc-900"
                >
                    Hủy
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-xs font-semibold"
                >
                    Lưu phim
                </button>
            </div>
        </div>
    </form>
@endsection

