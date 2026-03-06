@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1>Quản lý bình luận</h1>
    </div>

    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle"></i> Chức năng quản lý bình luận đang được phát triển.
    </div>

    @if($msg = getFlash('success'))
        <div class="alert alert-success" role="alert">{{ $msg }}</div>
    @endif
    @if($msg = getFlash('error'))
        <div class="alert alert-danger" role="alert">{{ $msg }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            @if(count($comments) > 0)
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người dùng</th>
                            <th>Phim</th>
                            <th>Nội dung</th>
                            <th>Trạng thái</th>
                            <th>Ngày</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comments as $comment)
                            <tr>
                                <td>#{{ $comment['id'] }}</td>
                                <td>{{ $comment['user_name'] ?? 'Ẩn danh' }}</td>
                                <td>{{ truncate($comment['movie_title'] ?? '', 30) }}</td>
                                <td>{{ truncate($comment['content'] ?? '', 50) }}</td>
                                <td>
                                    @if($comment['is_approved'])
                                        <span class="badge badge-success">Duyệt</span>
                                    @else
                                        <span class="badge badge-warning">Chờ</span>
                                    @endif
                                </td>
                                <td>{{ formatDate($comment['created_at'] ?? now()) }}</td>
                                <td>
                                    <form method="POST" action="/admin/binh-luan/{{ $comment['id'] }}/xoa" style="display:inline;">
                                        <button type="submit" onclick="return confirm('Xác nhận xóa?')" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted text-center py-4">Chưa có bình luận nào</p>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($total > $perPage)
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination">
                @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="/admin/binh-luan?page=1">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/admin/binh-luan?page={{ $page - 1 }}">Trước</a>
                    </li>
                @endif

                @for($i = max(1, $page - 2); $i <= min(ceil($total / $perPage), $page + 2); $i++)
                    <li class="page-item {{ $i === $page ? 'active' : '' }}">
                        <a class="page-link" href="/admin/binh-luan?page={{ $i }}">{{ $i }}</a>
                    </li>
                @endfor

                @if($page < ceil($total / $perPage))
                    <li class="page-item">
                        <a class="page-link" href="/admin/binh-luan?page={{ $page + 1 }}">Tiếp</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/admin/binh-luan?page={{ ceil($total / $perPage) }}">Cuối</a>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
</div>
@endsection
