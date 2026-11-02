
@extends('layouts.admin')

@section('title', 'Quản lý Tài liệu')
@section('page_title', 'DANH MỤC TÀI LIỆU SÁCH')

@section('content')

<div class="card card-custom bg-white p-4 mb-4">

    {{-- 1. THÔNG BÁO KẾT QUẢ --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- LỖI VALIDATION CỦA LARAVEL --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-circle me-1"></i>
                Có lỗi xảy ra:
            </strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 2. THỐNG KÊ KẾT QUẢ IMPORT --}}
    @if(session('import_summary'))
        @php
            $summary = session('import_summary');
            $totalSkipped = array_sum($summary);
        @endphp

        <div class="alert alert-info mt-2">
            <h6 class="fw-bold mb-3">
                <i class="bi bi-clipboard-data me-1"></i>
                THỐNG KÊ DỮ LIỆU IMPORT
            </h6>

            <div class="row g-2">
                <div class="col-md-6">
                    <div class="border rounded p-2 bg-white">
                        <div class="text-muted small">Trùng mã trong CSDL</div>
                        <div class="fw-bold text-danger">
                            {{ $summary['duplicate_db'] ?? 0 }} dòng
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-2 bg-white">
                        <div class="text-muted small">Trùng mã trong file</div>
                        <div class="fw-bold text-danger">
                            {{ $summary['duplicate_file'] ?? 0 }} dòng
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-2 bg-white">
                        <div class="text-muted small">Thiếu mã tài liệu</div>
                        <div class="fw-bold text-warning">
                            {{ $summary['missing_code'] ?? 0 }} dòng
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-2 bg-white">
                        <div class="text-muted small">Thiếu nhan đề</div>
                        <div class="fw-bold text-warning">
                            {{ $summary['missing_title'] ?? 0 }} dòng
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-2 bg-white">
                        <div class="text-muted small">Năm xuất bản không hợp lệ</div>
                        <div class="fw-bold text-warning">
                            {{ $summary['invalid_year'] ?? 0 }} dòng
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-2 bg-white">
                        <div class="text-muted small">Vượt giới hạn độ dài</div>
                        <div class="fw-bold text-warning">
                            {{ $summary['invalid_length'] ?? 0 }} dòng
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded p-2 bg-white">
                        <div class="text-muted small">Lỗi lưu cơ sở dữ liệu</div>
                        <div class="fw-bold text-danger">
                            {{ $summary['database_error'] ?? 0 }} dòng
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 fw-semibold">
                Tổng số dòng bị bỏ qua: {{ $totalSkipped }}
            </div>
        </div>
    @endif

    {{-- 3. CHI TIẾT DÒNG BỊ LỖI --}}
    @if(session('import_details') && count(session('import_details')) > 0)
        <div class="alert alert-warning mt-2">
            <h6 class="fw-bold">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                CHI TIẾT CÁC DÒNG BỊ BỎ QUA
            </h6>

            <div style="max-height: 250px; overflow-y: auto;">
                <ul class="mb-0">
                    @foreach(session('import_details') as $detail)
                        <li class="mb-1">{{ $detail }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- 4. THANH TÌM KIẾM VÀ TÁC VỤ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

        <form action="{{ route('admin.library.documents.index') }}"
              method="GET"
              class="d-flex gap-2">

            <input
                type="text"
                name="keyword"
                value="{{ $keyword ?? request('keyword') }}"
                class="form-control form-control-sm"
                placeholder="Tìm theo tên, tác giả, mã..."
                style="width: 280px;"
            >

            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-search"></i> Tìm
            </button>
        </form>

        <div class="d-flex gap-2">
            <button
                type="button"
                class="btn btn-sm btn-danger"
                data-bs-toggle="modal"
                data-bs-target="#addBookModal">

                <i class="bi bi-plus-lg me-1"></i>
                Thêm Sách mới
            </button>

            <button
                type="button"
                class="btn btn-sm btn-success"
                data-bs-toggle="modal"
                data-bs-target="#importBookModal">

                <i class="bi bi-file-earmark-excel me-1"></i>
                Nhập CSV (Import)
            </button>
        </div>
    </div>

    {{-- 5. BẢNG DANH MỤC TÀI LIỆU --}}
    <div class="table-responsive">

        <table class="table table-bordered table-hover align-middle mb-0 fs-8"
               style="min-width: 1400px;">

            <thead class="table-light text-center">
                <tr>
                    <th style="width: 100px;">Mã TL</th>
                    <th style="width: 60px;">Ảnh bìa</th>
                    <th style="width: 200px;">Nhan đề</th>
                    <th style="width: 130px;">Tác giả</th>
                    <th style="width: 120px;">Nhà xuất bản</th>
                    <th style="width: 70px;">Năm XB</th>
                    <th style="width: 80px;">Ngôn ngữ</th>
                    <th style="width: 100px;">Mã ISBN</th>
                    <th style="width: 150px;">Thuật ngữ chủ đề</th>
                    <th style="width: 120px;">Nơi lưu giữ</th>
                    <th style="width: 110px;">Trạng thái</th>
                    <th>Mô tả nội dung</th>
                    <th style="width: 100px;">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                @forelse($documents as $doc)

                    <tr>
                        <td class="fw-bold text-danger text-center">
                            {{ $doc->MaTaiLieu }}
                        </td>

                        <td class="text-center p-1">
                            @if($doc->AnhBia)
                                <img
                                    src="{{ $doc->AnhBia }}"
                                    style="height: 40px; width: 30px; object-fit: cover;"
                                    alt="Bìa tài liệu"
                                    onerror="this.onerror=null; this.src='https://via.placeholder.com/30x40?text=No+Img';"
                                >
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="fw-semibold text-dark">
                            {{ $doc->NhanDe }}
                        </td>

                        <td>{{ $doc->TacGia ?? '-' }}</td>

                        <td>{{ $doc->NhaXuatBan ?? '-' }}</td>

                        <td class="text-center">
                            {{ $doc->NamXuatBan ?? '-' }}
                        </td>

                        <td class="text-center">
                            {{ $doc->NgonNgu ?? 'Tiếng Việt' }}
                        </td>

                        <td class="text-center">
                            {{ $doc->ISBN ?? '-' }}
                        </td>

                        <td>
                            <small class="text-secondary">
                                {{ $doc->ThuatNguChuDe ?? '-' }}
                            </small>
                        </td>

                        <td>
                            {{ $doc->NoiLuuGiu?? 'Thư viện HUB' }}
                        </td>

                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success px-1 py-1">
                                {{ $doc->TrangThaiSuDung ?? 'Sẵn sàng' }}
                            </span>
                        </td>

                        <td>
                            <div
                                class="text-muted"
                                style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                title="{{ $doc->MoTa }}">

                                {{ $doc->MoTa ?? '-' }}
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-warning py-0 px-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal-{{ $loop->index }}"
                                    title="Sửa">

                                    <i class="bi bi-pencil"></i>
                                </button>

                                <form
                                    action="{{ route('admin.library.documents.delete', $doc->MaTaiLieu) }}"
                                    method="POST"
                                    onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này?');"
                                    class="m-0">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="Xóa">

                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="13" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Không tìm thấy tài liệu nào trong CSDL.
                        </td>
                    </tr>

                @endforelse
            </tbody>

        </table>
    </div>

    {{-- 6. PHÂN TRANG --}}
    <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">

        <div class="text-muted fs-8">
            Hiển thị
            <strong>{{ $documents->firstItem() ?? 0 }}</strong>
            -
            <strong>{{ $documents->lastItem() ?? 0 }}</strong>
            trên tổng số
            <strong>{{ $documents->total() }}</strong>
            tài liệu
        </div>

        <div>
            {{ $documents->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

    </div>

</div>


{{-- ===================================================== --}}
{{-- 7. MODAL THÊM TÀI LIỆU MỚI --}}
{{-- ===================================================== --}}

<div class="modal fade" id="addBookModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <form action="{{ route('admin.library.documents.store') }}"
                  method="POST">

                @csrf

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-1"></i>
                        THÊM TÀI LIỆU MỚI
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body fs-7">

                    <div class="row g-2">

                        <div class="col-md-6 mb-2">
                            <label class="form-label">
                                Mã tài liệu (*):
                            </label>

                            <input
                                type="text"
                                name="MaTaiLieu"
                                class="form-control form-control-sm"
                                maxlength="30"
                                required>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">
                                Nhan đề sách (*):
                            </label>

                            <input
                                type="text"
                                name="NhanDe"
                                class="form-control form-control-sm"
                                maxlength="300"
                                required>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Tác giả:</label>

                            <input
                                type="text"
                                name="TacGia"
                                class="form-control form-control-sm"
                                maxlength="250">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nhà xuất bản:</label>

                            <input
                                type="text"
                                name="NhaXuatBan"
                                class="form-control form-control-sm"
                                maxlength="150"
                                value="Chưa cập nhật">
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label">Năm xuất bản:</label>

                            <input
                                type="number"
                                name="NamXuatBan"
                                class="form-control form-control-sm"
                                min="1000"
                                max="{{ date('Y') }}">
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label">Ngôn ngữ:</label>

                            <input
                                type="text"
                                name="NgonNgu"
                                class="form-control form-control-sm"
                                maxlength="30"
                                value="Tiếng Việt">
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="form-label">Mã ISBN:</label>

                            <input
                                type="text"
                                name="ISBN"
                                class="form-control form-control-sm"
                                maxlength="30">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nơi lưu giữ:</label>

                            <input
                                type="text"
                                name="NoiLuuGiu"
                                class="form-control form-control-sm"
                                maxlength="150"
                                value="Thư viện HUB">
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Trạng thái:</label>

                            <select
                                name="TrangThaiSuDung"
                                class="form-select form-select-sm">

                                <option value="Co_San" selected>Có sẵn</option>
                                <option value="DangMuon">Đang mượn</option>
                                <option value="NgungSuDung">Ngừng sử dụng</option>

                            </select>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">
                                Thuật ngữ chủ đề:
                            </label>

                            <textarea
                                name="ThuatNguChuDe"
                                class="form-control form-control-sm"
                                rows="2"></textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">URL ảnh bìa:</label>

                            <input
                                type="text"
                                name="AnhBia"
                                class="form-control form-control-sm"
                                maxlength="255">
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">
                                Mô tả nội dung:
                            </label>

                            <textarea
                                name="MoTa"
                                class="form-control form-control-sm"
                                rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit"
                            class="btn btn-sm btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Lưu tài liệu
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


{{-- ===================================================== --}}
{{-- 8. MODAL IMPORT CSV / EXCEL --}}
{{-- ===================================================== --}}

<div class="modal fade" id="importBookModal" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                action="{{ route('admin.library.documents.import') }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        IMPORT DANH MỤC TÀI LIỆU
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body fs-7">

                    <div class="alert alert-info py-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Hệ thống hỗ trợ tệp CSV, XLSX và XLS.
                    </div>

                    <label class="form-label fw-semibold">
                        Chọn tệp dữ liệu:
                    </label>

                    <input
                        type="file"
                        name="file_import"
                        class="form-control form-control-sm"
                        accept=".csv,.xlsx,.xls"
                        required>

                    <small class="text-muted d-block mt-2">
                        - Dung lượng tối đa: 5MB.
                        <br>
                        - Dòng đầu tiên phải chứa tên các cột dữ liệu.
                        <br>
                        - Bắt buộc có cột Mã tài liệu và Nhan đề.
                        <br>
                        - Mã tài liệu không được trùng với dữ liệu đã có.
                        <br>
                        - Nhà xuất bản để trống sẽ được điền là
                        "Chưa cập nhật".
                    </small>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit"
                            class="btn btn-sm btn-success">

                        <i class="bi bi-file-earmark-arrow-up me-1"></i>
                        Bắt đầu Import
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


{{-- ===================================================== --}}
{{-- 9. MODAL CHỈNH SỬA TÀI LIỆU --}}
{{-- Đặt ngoài TABLE để tránh lỗi cấu trúc HTML --}}
{{-- ===================================================== --}}

@foreach($documents as $index => $doc)

    <div
        class="modal fade"
        id="editModal-{{ $index }}"
        tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">

                <form
                    action="{{ route('admin.library.documents.update', $doc->MaTaiLieu) }}"
                    method="POST">

                    @csrf

                    <div class="modal-header">

                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-square me-1"></i>
                            CẬP NHẬT TÀI LIỆU
                        </h5>

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body fs-7">

                        <div class="row g-2">

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Mã tài liệu:</label>

                                <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->MaTaiLieu }}"
                                    disabled>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">
                                    Nhan đề sách (*):
                                </label>

                                <input
                                    type="text"
                                    name="NhanDe"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->NhanDe }}"
                                    maxlength="300"
                                    required>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Tác giả:</label>

                                <input
                                    type="text"
                                    name="TacGia"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->TacGia }}"
                                    maxlength="250">
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Nhà xuất bản:</label>

                                <input
                                    type="text"
                                    name="NhaXuatBan"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->NhaXuatBan ?? 'Chưa cập nhật' }}"
                                    maxlength="150">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Năm xuất bản:</label>

                                <input
                                    type="number"
                                    name="NamXuatBan"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->NamXuatBan }}"
                                    min="1000"
                                    max="{{ date('Y') }}">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Ngôn ngữ:</label>

                                <input
                                    type="text"
                                    name="NgonNgu"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->NgonNgu }}"
                                    maxlength="30">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Mã ISBN:</label>

                                <input
                                    type="text"
                                    name="ISBN"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->ISBN }}"
                                    maxlength="30">
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Nơi lưu giữ:</label>

                                <input
                                    type="text"
                                    name="NoiLuuGiu"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->NoiLuuGiu }}"
                                    maxlength="150">
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Trạng thái:</label>

                                <select
                                    name="TrangThaiSuDung"
                                    class="form-select form-select-sm">

                                    <option value="Co_San"
                                        {{ $doc->TrangThaiSuDung == 'Co_San' ? 'selected' : '' }}>
                                        Có sẵn
                                    </option>

                                    <option value="DangMuon"
                                        {{ $doc->TrangThaiSuDung == 'DangMuon' ? 'selected' : '' }}>
                                        Đang mượn
                                    </option>

                                    <option value="NgungSuDung"
                                        {{ $doc->TrangThaiSuDung == 'NgungSuDung' ? 'selected' : '' }}>
                                        Ngừng sử dụng
                                    </option>

                                </select>
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="form-label">
                                    Thuật ngữ chủ đề:
                                </label>

                                <textarea
                                    name="ThuatNguChuDe"
                                    class="form-control form-control-sm"
                                    rows="2">{{ $doc->ThuatNguChuDe }}</textarea>
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="form-label">URL ảnh bìa:</label>

                                <input
                                    type="text"
                                    name="AnhBia"
                                    class="form-control form-control-sm"
                                    value="{{ $doc->AnhBia }}"
                                    maxlength="255">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="form-label">
                                    Mô tả nội dung:
                                </label>

                                <textarea
                                    name="MoTa"
                                    class="form-control form-control-sm"
                                    rows="3">{{ $doc->MoTa }}</textarea>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">
                            Hủy
                        </button>

                        <button
                            type="submit"
                            class="btn btn-sm btn-primary">

                            <i class="bi bi-save me-1"></i>
                            Lưu thay đổi
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>

@endforeach

@endsection
 