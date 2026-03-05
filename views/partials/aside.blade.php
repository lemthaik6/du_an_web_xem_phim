<div class="p-4 border-b border-zinc-800">
    <div class="text-xs uppercase text-zinc-500 mb-1">Admin</div>
    <div class="font-semibold text-zinc-100 text-sm">Bảng điều khiển</div>
</div>

<nav class="flex-1 p-3 space-y-1 text-sm">
    <a href="{{ route('/admin') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-zinc-200 hover:bg-zinc-800/80">
        <span>📊</span>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('/admin/phim') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-zinc-200 hover:bg-zinc-800/80">
        <span>🎬</span>
        <span>Quản lý phim</span>
    </a>
    <a href="{{ route('/admin/tap-phim') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-zinc-200 hover:bg-zinc-800/80">
        <span>📺</span>
        <span>Quản lý tập phim</span>
    </a>
    <a href="{{ route('/admin/the-loai') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-zinc-200 hover:bg-zinc-800/80">
        <span>🏷️</span>
        <span>Thể loại</span>
    </a>
    <a href="{{ route('/admin/nguoi-dung') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-zinc-200 hover:bg-zinc-800/80">
        <span>👤</span>
        <span>Người dùng</span>
    </a>
    <a href="{{ route('/admin/binh-luan') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-zinc-200 hover:bg-zinc-800/80">
        <span>💬</span>
        <span>Bình luận</span>
    </a>
    <a href="{{ route('/admin/banner') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-zinc-200 hover:bg-zinc-800/80">
        <span>📢</span>
        <span>Banner / Slider</span>
    </a>
</nav>
