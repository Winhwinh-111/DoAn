<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sách đã đánh dấu - Thư viện HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --blue-main: #003366;
            --blue-light: #0f4c81;
            --red-main: #a71d2a;
            --red-hover: #881420;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 3px solid var(--red-main);
        }
        .book-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
            background: #ffffff;
        }
        .book-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        .section-title {
            color: var(--blue-main);
            font-weight: 700;
            border-left: 4px solid var(--red-main);
            padding-left: 10px;
        }
        .book-detail-table {
            table-layout: fixed;
            width: 100%;
        }
        .book-detail-table th {
            width: 32%;
            background-color: #f8f9fa !important;
            color: #495057;
            font-weight: 600;
            vertical-align: top;
            padding: 9px 12px;
            white-space: nowrap;
        }
        .book-detail-table td {
            vertical-align: top;
            padding: 9px 12px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>

    <!-- HEADER THƯ VIỆN HUB -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" title="Trang chủ Thư viện HUB">
                <img src="{{ asset('images/logo_lib.jpg') }}" alt="Thư viện HUB Logo" height="80" class="me-2" onerror="this.onerror=null; this.src='https://via.placeholder.com/180x48?text=HUB+LIBRARY';">
            </a>

            <div class="ms-auto">
                @if(session()->has('user_id'))
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center text-dark fw-semibold px-2 py-1 rounded" href="#" role="button" id="studentMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-4 text-primary me-2"></i>
                            <span>{{ session('student_name', 'Sinh viên') }}</span>
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="studentMenu" style="min-width: 230px;">
                            <li class="px-3 py-2 border-bottom bg-light">
                                <small class="text-muted d-block">Mã số sinh viên</small>
                                <strong class="text-dark">{{ session('MSSV', 'N/A') }}</strong>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('profile') }}">
                                    <i class="bi bi-person-vcard text-danger me-2 fs-6"></i>Xem thông tin
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('history') }}">
                                    <i class="bi bi-clock-history text-primary me-2 fs-6"></i>Lịch sử mượn trả
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 active bg-light text-primary fw-bold" href="{{ route('bookmarks') }}">
                                    <i class="bi bi-bookmark-star text-warning me-2 fs-6"></i>Sách đã đánh dấu
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="bi bi-box-arrow-right me-2 fs-6"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="container my-4" style="min-height: 70vh;">
        <!-- BREADCRUMB -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-4 fs-7">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Danh sách tài liệu đã đánh dấu</li>
            </ol>
        </nav>

        <!-- FLASH MESSAGE -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="section-title m-0">TÀI LIỆU DẠNG YÊU THÍCH ĐÃ ĐÁNH DẤU</h4>
            <span class="badge bg-warning text-dark border px-3 py-2 fs-7">
                <i class="bi bi-bookmark-star-fill me-1"></i>Tổng cộng: {{ count($markedBooks) }} tài liệu
            </span>
        </div>

        @if(count($markedBooks) > 0)
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mb-5">
                @foreach($markedBooks as $book)
                    <div class="col">
                        <div class="card h-100 book-card position-relative">
                            
                            <!-- NÚT BỎ ĐÁNH DẤU -->
                            <form action="{{ route('bookmark.toggle') }}" method="POST" class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                @csrf
                                <input type="hidden" name="ma_tai_lieu" value="{{ $book->MaTaiLieu }}">
                                <button type="submit" class="btn btn-sm btn-warning text-dark shadow-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="Bỏ đánh dấu">
                                    <i class="bi bi-bookmark-star-fill"></i>
                                </button>
                            </form>

                            <div class="text-center p-3 bg-light border-bottom cursor-pointer" style="height: 220px;" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                <img src="{{ $book->AnhBia }}" class="img-fluid h-100 rounded shadow-sm" alt="Bìa sách" style="object-fit: contain;">
                            </div>
                            <div class="card-body d-flex flex-column p-3">
                                <h6 class="card-title fw-bold text-dark fs-7 text-truncate-2 mb-2" style="height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $book->NhanDe }}">
                                    {{ $book->NhanDe }}
                                </h6>
                                <p class="card-text fs-8 text-muted mb-1"><i class="bi bi-person me-1"></i>{{ $book->TacGia ?? 'Nhiều tác giả' }}</p>
                                <p class="card-text fs-8 text-secondary mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $book->NoiLuuGiu }}</p>
                                <p class="card-text fs-8 text-warning mb-3">
                                    <i class="bi bi-clock me-1"></i>{{ !empty($book->ThoiDiemDanhDau) ? date('H:i d/m/Y', strtotime($book->ThoiDiemDanhDau)) : '' }}
                                </p>
                                <div class="mt-auto d-grid gap-2">
                                    <button class="btn btn-sm btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                        <i class="bi bi-journal-bookmark me-1"></i>Xem Chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL CHI TIẾT SÁCH (UC05) -->
                    <div class="modal fade" id="bookModal-{{ $book->MaTaiLieu }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-top: 5px solid #a71d2a; border-radius: 12px;">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold text-uppercase" style="color: #003366;">
                                        <i class="bi bi-journal-text me-2 text-danger"></i>THÔNG TIN CHI TIẾT TÀI LIỆU
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-4 text-center border-end">
                                            <img src="{{ $book->AnhBia }}" class="img-fluid rounded shadow-sm mb-3" style="max-height: 300px; object-fit: contain;" alt="{{ $book->NhanDe }}">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover mb-0 fs-7 book-detail-table">
                                                    <tbody>
                                                        <tr>
                                                            <th>Mã tài liệu</th>
                                                            <td class="fw-bold text-danger">{{ $book->MaTaiLieu }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nhan đề sách</th>
                                                            <td class="fw-bold text-dark">{{ $book->NhanDe }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Tác giả</th>
                                                            <td>{{ $book->TacGia ?? 'Nhiều tác giả' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nhà xuất bản</th>
                                                            <td>{{ $book->NhaXuatBan ?? 'Chưa cập nhật' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Năm xuất bản</th>
                                                            <td>{{ $book->NamXuatBan ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Ngôn ngữ</th>
                                                            <td>{{ $book->NgonNgu ?? 'Tiếng Việt' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Mã ISBN</th>
                                                            <td>{{ $book->ISBN ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Thuật ngữ / Chủ đề</th>
                                                            <td>
                                                                @if(!empty($book->ThuatNguChuDe))
                                                                    <div class="d-flex flex-column gap-1">
                                                                        @foreach(array_filter(array_map('trim', explode(',', $book->ThuatNguChuDe))) as $term)
                                                                            <div class="text-dark">
                                                                                <i class="bi bi-tag-fill text-danger me-1 fs-8"></i>{{ $term }}
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <span class="text-muted fs-8">Chưa cập nhật</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Học phần liên quan</th>
                                                            <td>
                                                                @php
                                                                    $relCourses = \App\Models\HocPhan::join('DeCuongHocPhan', 'HocPhan.MaHocPhan', '=', 'DeCuongHocPhan.MaHocPhan')
                                                                        ->where('DeCuongHocPhan.MaTaiLieu', $book->MaTaiLieu)
                                                                        ->select('HocPhan.*', 'DeCuongHocPhan.LoaiTaiLieu')
                                                                        ->get();
                                                                @endphp
                                                                @if(count($relCourses) > 0)
                                                                    @foreach($relCourses as $hp)
                                                                        <div class="mb-1">
                                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1">
                                                                                <i class="bi bi-book me-1"></i>[{{ $hp->MaHocPhan }}] {{ $hp->TenHocPhan }}
                                                                            </span>
                                                                            <small class="text-muted fs-8">({{ $hp->LoaiTaiLieu === 'GiaoTrinhChinh' ? 'Giáo trình chính' : 'Tham khảo' }})</small>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted fs-8">Chưa gắn với học phần cụ thể</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Nơi lưu giữ</th>
                                                            <td class="text-primary fw-semibold"><i class="bi bi-geo-alt me-1"></i>{{ $book->NoiLuuGiu ?? 'Thư viện HUB' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Trạng thái sử dụng</th>
                                                            <td>
                                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fw-normal">
                                                                    {{ $book->TrangThaiSuDung ?? 'Sẵn sàng phục vụ' }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Mô tả nội dung</th>
                                                            <td class="text-muted fs-8">{{ $book->MoTa ?? 'Chưa có mô tả chi tiết.' }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-top pt-2">
                                    <form action="{{ route('bookmark.toggle') }}" method="POST" class="m-0">
                                        @csrf
                                        <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                        <button type="submit" class="btn btn-sm btn-warning fw-semibold fs-8">
                                            <i class="bi bi-bookmark-star-fill me-1"></i>Đã đánh dấu (Bỏ đánh dấu)
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-secondary fs-8" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-light border text-center py-5 shadow-sm my-4">
                <i class="bi bi-bookmark-star fs-1 text-muted d-block mb-3"></i>
                <h6 class="fw-bold text-secondary">Bạn chưa đánh dấu tài liệu nào vào danh sách yêu thích.</h6>
                <p class="text-muted fs-7 mb-3">Khi tìm kiếm hoặc xem danh sách gợi ý, hãy bấm biểu tượng ngọn cờ/ngôi sao đánh dấu để lưu lại xem sau.</p>
                <a href="{{ route('home') }}" class="btn btn-sm btn-primary px-4">
                    <i class="bi bi-arrow-left me-1"></i>Quay về Trang chủ tìm tài liệu
                </a>
            </div>
        @endif
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>