@extends('layouts.admin')

@section('title', 'Admin Dashboard - FilmStream')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl md:text-2xl font-semibold mb-2">Bảng điều khiển</h1>
        <p class="text-xs text-zinc-400">
            Tổng quan hệ thống: phim, người dùng và lượt xem (số liệu demo nếu bảng chưa tồn tại).
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4">
            <div class="text-xs text-zinc-400 mb-1">Tổng số phim</div>
            <div class="text-2xl font-semibold mb-1">{{ number_format($stats['movies']) }}</div>
            <div class="text-[11px] text-zinc-500">Bao gồm phim lẻ và phim bộ.</div>
        </div>
        <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4">
            <div class="text-xs text-zinc-400 mb-1">Tổng số người dùng</div>
            <div class="text-2xl font-semibold mb-1">{{ number_format($stats['users']) }}</div>
            <div class="text-[11px] text-zinc-500">User đã đăng ký tài khoản.</div>
        </div>
        <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4">
            <div class="text-xs text-zinc-400 mb-1">Tổng lượt xem</div>
            <div class="text-2xl font-semibold mb-1">{{ number_format($stats['views']) }}</div>
            <div class="text-[11px] text-zinc-500">Cộng dồn từ cột <code>views_count</code> của bảng <code>movies</code>.</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.2fr),minmax(0,1.2fr)] gap-4">
        <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold">Phim mới thêm gần đây</h2>
                <a href="{{ route('/admin/phim') }}" class="text-[11px] text-zinc-400 hover:text-zinc-200">
                    Xem tất cả
                </a>
            </div>
            <div class="border border-dashed border-zinc-700 rounded-lg p-4 text-xs text-zinc-500">
                Bảng danh sách phim mới sẽ hiển thị ở đây sau khi kết nối với cơ sở dữ liệu.
            </div>
        </div>

        <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold">Hoạt động gần đây</h2>
            </div>
            <div class="space-y-2 text-xs text-zinc-400">
                <p>- Thống kê đang dùng dữ liệu demo nếu chưa có bảng.</p>
                <p>- Sau này có thể log hoạt động admin (thêm/sửa/xóa phim, xóa bình luận, ...).</p>
            </div>
        </div>
    </div>
@endsection

