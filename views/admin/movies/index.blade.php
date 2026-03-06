@extends('layouts.admin')

@section('title', 'Quản lý phim - Admin')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl md:text-2xl font-semibold mb-1">Quản lý phim</h1>
            <p class="text-xs text-zinc-400">
                Thêm, chỉnh sửa và xóa phim trong hệ thống.
            </p>
        </div>
        <a
            href="{{ route('/admin/phim/them') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary-600 hover:bg-primary-500 text-xs md:text-sm font-semibold"
        >
            + Thêm phim mới
        </a>
    </div>

    @if($msg = getFlash('success'))
        <div class="mb-3 text-xs px-3 py-2 rounded-md bg-emerald-600/20 text-emerald-300 border border-emerald-600/50">
            {{ $msg }}
        </div>
    @endif
    @if($msg = getFlash('error'))
        <div class="mb-3 text-xs px-3 py-2 rounded-md bg-red-600/20 text-red-300 border border-red-600/50">
            {{ $msg }}
        </div>
    @endif

    <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl overflow-hidden">
        <table class="min-w-full text-xs md:text-sm">
            <thead class="bg-zinc-950/80 border-b border-zinc-800">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-300">Tiêu đề</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-300 hidden md:table-cell">Slug</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-300 w-16">Năm</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-300 w-20 hidden md:table-cell">Quốc gia</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-300 w-24">Trạng thái</th>
                    <th class="px-3 py-2 text-right font-semibold text-zinc-300 w-24 hidden md:table-cell">Lượt xem</th>
                    <th class="px-3 py-2 text-right font-semibold text-zinc-300 w-32">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movies as $movie)
                    <tr class="border-b border-zinc-800/80 hover:bg-zinc-900/80">
                        <td class="px-3 py-2">
                            <div class="font-medium text-zinc-100">
                                {{ $movie['title'] }}
                            </div>
                        </td>
                        <td class="px-3 py-2 text-[11px] text-zinc-400 hidden md:table-cell">
                            {{ $movie['slug'] }}
                        </td>
                        <td class="px-3 py-2 text-[11px]">
                            {{ $movie['year'] ?? '-' }}
                        </td>
                        <td class="px-3 py-2 text-[11px] text-zinc-400 hidden md:table-cell">
                            {{ $movie['country'] ?? '-' }}
                        </td>
                        <td class="px-3 py-2 text-[11px]">
                            @if($movie['is_published'])
                                <span class="px-2 py-0.5 rounded-full bg-emerald-600/20 text-emerald-300 border border-emerald-600/40">Đang hiển thị</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-zinc-800 text-zinc-300 border border-zinc-700">Nháp</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-[11px] text-right text-zinc-400 hidden md:table-cell">
                            {{ number_format($movie['views_count'] ?? 0) }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a
                                    href="{{ route('/admin/phim/' . $movie['id'] . '/sua') }}"
                                    class="px-2 py-1 rounded-md bg-zinc-800 hover:bg-zinc-700 text-[11px]"
                                >
                                    Sửa
                                </a>
                                <form
                                    action="{{ route('/admin/phim/' . $movie['id'] . '/xoa') }}"
                                    method="post"
                                    onsubmit="return confirm('Bạn chắc chắn muốn xóa phim này?');"
                                >
                                    <button
                                        type="submit"
                                        class="px-2 py-1 rounded-md bg-red-600/80 hover:bg-red-600 text-[11px]"
                                    >
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-xs text-zinc-500">
                            Chưa có phim nào trong hệ thống.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @php
        $totalPages = max(1, ceil($total / $perPage));
    @endphp
    @if($totalPages > 1)
        <div class="mt-4 flex justify-center">
            <div class="inline-flex items-center gap-1 text-xs text-zinc-400">
                @for($p = 1; $p <= $totalPages; $p++)
                    <a
                        href="{{ route('/admin/phim') . '?page=' . $p }}"
                        class="px-2 py-1 rounded-md border border-zinc-800 {{ $p === $page ? 'bg-zinc-900' : 'hover:bg-zinc-900' }}"
                    >
                        {{ $p }}
                    </a>
                @endfor
            </div>
        </div>
    @endif
@endsection

