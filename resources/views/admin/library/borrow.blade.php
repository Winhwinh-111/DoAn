@extends('layouts.admin')

@section('title', 'Quản lý Mượn trả')
@section('page_title', 'QUẢN LÝ GIAO DỊCH MƯỢN TRẢ TÀI LIỆU')

@section('content')
<div class="card card-custom bg-white p-4 mb-4">
    <!-- Hiển thị thông báo phản hồi (Flash Message) -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 fs-7" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 fs-7" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold m-0 text-dark"><i class="bi bi-list-check me-2"></i>DANH SÁCH MƯỢN TRẢ</h6>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#newBorrowModal">
            <i class="bi bi-plus-circle me-1"></i>Tạo Phiếu Mượn
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0 fs-7">
            <thead class="table-light text-center">
                <tr>
                    <th>Mã GD</th>
                    <th>MSSV</th>
                    <th>Mã sách</th>
                    <th>Ngày mượn</th>
                    <th>Ngày trả thực tế</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($borrowList as $b)
                    <tr>
                        <td class="fw-bold text-center text-primary">{{ $b->MaGiaoDich }}</td>
                        <td class="text-center">{{ $b->MSSV }}</td>
                        <td>{{ $b->taiLieu->NhanDe ?? $b->MaTaiLieu }}</td>
                        <td class="text-center">{{ date('d/m/Y H:i', strtotime($b->NgayMuon)) }}</td>
                        <td class="text-center">{{ $b->NgayTra ? date('d/m/Y H:i', strtotime($b->NgayTra)) : '-' }}</td>
                        <td class="text-center">
                            @if($b->TrangThai === 'DaTra')
                                <span class="badge bg-success">Đã trả</span>
                            @else
                                <span class="badge bg-warning text-dark">Đang mượn</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($b->TrangThai !== 'DaTra')
                                <form action="{{ route('admin.library.borrow.return', $b->MaGiaoDich) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2">
                                        <i class="bi bi-check2-circle"></i> Xác nhận trả
                                    </button>
                                </form>
                            @else
                                <span class="text-muted fs-8">Hoàn tất</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Chưa có bản ghi mượn trả nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $borrowList->links() }}</div>
</div>

<!-- Modal tạo phiếu mượn -->
<div class="modal fade" id="newBorrowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.library.borrow.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">TẠO PHIẾU MƯỢN MỚI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body fs-7">
                    <div class="mb-3">
                        <label class="form-label">Mã số sinh viên (MSSV) (*):</label>
                        <input type="text" name="mssv" class="form-control form-control-sm" required placeholder="Nhập MSSV">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã tài liệu sách (*):</label>
                        <input type="text" name="MaTaiLieu" class="form-control form-control-sm" required placeholder="Nhập mã tài liệu">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú:</label>
                        <textarea name="GhiChu" class="form-control form-control-sm" rows="2" placeholder="Nhập ghi chú (nếu có)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">Xác nhận Mượn</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection