<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin sinh viên - Thư viện HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --blue-main: #003366;
            --red-main: #a71d2a;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 3px solid var(--red-main);
        }
        .section-title {
            color: var(--blue-main);
            font-weight: 700;
            border-left: 4px solid var(--red-main);
            padding-left: 10px;
        }
    </style>
</head>
<body>

    <!-- HEADER TRẮNG THƯ VIỆN HUB -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" title="Trang chủ Thư viện HUB">
                <img src="{{ asset('images/logo_lib.jpg') }}" alt="Thư viện HUB Logo" height="80" class="me-2" onerror="this.onerror=null; this.src='https://via.placeholder.com/180x48?text=HUB+LIBRARY';">
            </a>

            <div class="ms-auto">
                <a href="{{ route('home') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại trang chủ
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- KHỐI 1: THÔNG TIN CÁ NHÂN SINH VIÊN (DẠNG BẢNG 2 CỘT FULL THUỘC TÍNH + TÊN NGÀNH) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h4 class="section-title m-0">THÔNG TIN CÁ NHÂN SINH VIÊN</h4>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold" style="width: 35%;">
                                            <i class="bi bi-card-heading text-danger me-2"></i>Mã số sinh viên (MSSV)
                                        </th>
                                        <td class="fw-bold text-danger fs-6">{{ $sinhVien->MSSV ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold">
                                            <i class="bi bi-person text-primary me-2"></i>Họ và tên
                                        </th>
                                        <td class="fw-bold text-dark">{{ $sinhVien->HoTen ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold">
                                            <i class="bi bi-calendar-event text-primary me-2"></i>Ngày sinh
                                        </th>
                                        <td>
                                            {{ $sinhVien->NgaySinh ? date('d/m/Y', strtotime($sinhVien->NgaySinh)) : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold">
                                            <i class="bi bi-gender-ambiguous text-primary me-2"></i>Giới tính
                                        </th>
                                        <td>{{ $sinhVien->GioiTinh ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold">
                                            <i class="bi bi-telephone text-primary me-2"></i>Số điện thoại
                                        </th>
                                        <td>{{ $sinhVien->SoDienThoai ?? 'Chưa cập nhật' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold">
                                            <i class="bi bi-person-vcard text-primary me-2"></i>Hạn thẻ thư viện
                                        </th>
                                        <td>
                                            <span class="badge bg-light text-dark border border-secondary px-2 py-1">
                                                <i class="bi bi-clock me-1 text-danger"></i>
                                                {{ $sinhVien->HanThe ? date('d/m/Y', strtotime($sinhVien->HanThe)) : 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold">
                                            <i class="bi bi-journal-bookmark text-primary me-2"></i>Mã ngành đào tạo
                                        </th>
                                        <td>
                                            <span class="badge bg-primary px-2 py-1">{{ $sinhVien->MaNganh ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary fw-semibold">
                                            <i class="bi bi-building text-primary me-2"></i>Tên ngành đào tạo
                                        </th>
                                        <td>
                                            {{ $sinhVien->nganh->TenNganh ?? 'Tài chính - Ngân hàng' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- KHỐI 2: HỒ SƠ HỌC TẬP SINH VIÊN -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h4 class="section-title m-0">HỒ SƠ HỌC TẬP & DANH SÁCH HỌC PHẦN</h4>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <p class="text-muted fs-7 mb-3">Các học phần sinh viên đang đăng ký học kỳ này (Dùng làm cơ sở gợi ý tài liệu học tập phù hợp):</p>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã HP</th>
                                        <th>Tên học phần</th>
                                        <th>Số tín chỉ</th>
                                        <th>Trạng thái học tập</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($sinhVien && $sinhVien->hocPhans->count() > 0)
                                        @foreach($sinhVien->hocPhans as $hp)
                                            <tr>
                                                <td class="fw-bold text-primary">{{ $hp->MaHocPhan }}</td>
                                                <td>{{ $hp->TenHocPhan }}</td>
                                                <td>{{ $hp->SoTinChi ?? '-' }}</td>
                                                <td>
                                                    @php
                                                        $trangThai = $hp->pivot->TrangThai ?? 'DangHoc';
                                                    @endphp

                                                    @if($trangThai === 'DaHoanThanh')
                                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                            <i class="bi bi-check-circle me-1"></i>Đã hoàn thành
                                                        </span>
                                                    @else
                                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                                            <i class="bi bi-clock-history me-1"></i>Đang học
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu học phần nào được đăng ký.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <footer style="background-color: #8F0728;" class="text-white pt-4 pb-3 mt-5 border-top border-danger border-3">
        <div class="container text-center fs-7">
            <p class="mb-1 fw-bold">THƯ VIỆN TRƯỜNG ĐẠI HỌC NGÂN HÀNG THÀNH PHỐ HỒ CHÍ MINH</p>
            <p class="text-white opacity-75 mb-0">Địa chỉ: 56 Hoàng Diệu 2, P. Thủ Đức, TP.HCM</p>
            <p class="text-white opacity-75 mb-0">Điện thoại: (028) 38971651 - Email: <a href="mailto:thuvien@hub.edu.vn" class="text-white text-decoration-underline">thuvien@hub.edu.vn</a></p>
            <p class="text-white opacity-75 mb-0">Fanpage: <a href="https://www.facebook.com/thuvien.hub" target="_blank" class="text-white text-decoration-underline">https://www.facebook.com/thuvien.hub</a></p>
            <small class="text-white-50 d-block mt-2">© 2026 HUB Library System - Hệ thống Gợi ý Tài liệu Thông minh</small>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>