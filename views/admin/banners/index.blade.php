@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1>Quản lý banner</h1>
    </div>

    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle"></i> Chức năng quản lý banner đang được phát triển.
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
                <a href="/admin/banner/them" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Thêm banner
                </a>
            </h5>
        </div>
        <div class="card-body">
            @if(count($banners) > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề</th>
                            <th>Vị trí</th>
                            <th>Hình ảnh</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($banners as $banner)
                            <tr>
                                <td>#{{ $banner['id'] }}</td>
                                <td>{{ $banner['title'] }}</td>
                                <td><span class="badge badge-primary">{{ $banner['position'] ?? 'main' }}</span></td>
                                <td>
                                    @if($banner['image_url'])
                                        <img src="{{ $banner['image_url'] }}" alt="{{ $banner['title'] }}" style="max-height: 50px;">
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($banner['is_active'])
                                        <span class="badge badge-success">Hoạt động</span>
                                    @else
                                        <span class="badge badge-secondary">Tắt</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="/admin/banner/{{ $banner['id'] }}/sua" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="/admin/banner/{{ $banner['id'] }}/xoa" style="display:inline;">
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
                <p class="text-muted text-center py-4">Chưa có banner nào</p>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($total > $perPage)
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination">
                @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="/admin/banner?page=1">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/admin/banner?page={{ $page - 1 }}">Trước</a>
                    </li>
                @endif

                @for($i = max(1, $page - 2); $i <= min(ceil($total / $perPage), $page + 2); $i++)
                    <li class="page-item {{ $i === $page ? 'active' : '' }}">
                        <a class="page-link" href="/admin/banner?page={{ $i }}">{{ $i }}</a>
                    </li>
                @endfor

                @if($page < ceil($total / $perPage))
                    <li class="page-item">
                        <a class="page-link" href="/admin/banner?page={{ $page + 1 }}">Tiếp</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="/admin/banner?page={{ ceil($total / $perPage) }}">Cuối</a>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
</div>
@endsection
