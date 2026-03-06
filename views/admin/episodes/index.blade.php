@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1>Quản lý tập phim</h1>
    </div>

    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle"></i> Chức năng quản lý tập phim đang được phát triển.
    </div>

    @if($msg = getFlash('success'))
        <div class="alert alert-success" role="alert">{{ $msg }}</div>
    @endif
    @if($msg = getFlash('error'))
        <div class="alert alert-danger" role="alert">{{ $msg }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <a href="/admin/tap-phim/them" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Thêm tập phim
                </a>
            </h5>
        </div>
        <div class="card-body">
            @if(count($episodes) > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Phim</th>
                            <th>Tập số</th>
                            <th>Tiêu đề</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($episodes as $episode)
                            <tr>
                                <td>#{{ $episode['id'] }}</td>
                                <td>{{ $episode['movie_title'] ?? 'N/A' }}</td>
                                <td>{{ $episode['episode_number'] }}</td>
                                <td>{{ truncate($episode['title'] ?? '', 50) }}</td>
                                <td>{{ formatDate($episode['created_at'] ?? now()) }}</td>
                                <td>
                                    <a href="/admin/tap-phim/{{ $episode['id'] }}/sua" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="/admin/tap-phim/{{ $episode['id'] }}/xoa" style="display:inline;">
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
                <p class="text-muted text-center py-4">Chưa có tập phim nào</p>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($total > $perPage)
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination">
                @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="/admin/tap-phim?page=1">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/admin/tap-phim?page={{ $page - 1 }}">Trước</a>
                    </li>
                @endif

                @for($i = max(1, $page - 2); $i <= min(ceil($total / $perPage), $page + 2); $i++)
                    <li class="page-item {{ $i === $page ? 'active' : '' }}">
                        <a class="page-link" href="/admin/tap-phim?page={{ $i }}">{{ $i }}</a>
                    </li>
                @endfor

                @if($page < ceil($total / $perPage))
                    <li class="page-item">
                        <a class="page-link" href="/admin/tap-phim?page={{ $page + 1 }}">Tiếp</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/admin/tap-phim?page={{ ceil($total / $perPage) }}">Cuối</a>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
</div>
@endsection
