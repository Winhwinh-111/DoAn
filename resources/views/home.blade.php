<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện - Trường Đại học Ngân hàng TP.HCM</title>
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
        .hero-banner {
            background: linear-gradient(135deg, var(--blue-main) 0%, var(--red-main) 100%);
            color: white;
            padding: 40px 0 50px 0;
            margin-bottom: 30px;
        }
        .btn-red {
            background-color: var(--red-main);
            color: white;
            font-weight: 600;
            border: none;
        }
        .btn-red:hover {
            background-color: var(--red-hover);
            color: white;
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
        .book-cover-wrapper {
            cursor: pointer;
        }
        .match-badge {
            background-color: #27ae60;
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
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
                                <a class="dropdown-item py-2" href="{{ route('bookmarks') }}">
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
                @else
                    <button class="btn btn-red rounded-pill px-3 py-1 fs-7" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                    </button>
                @endif
            </div>
        </div>
    </nav>

    <!-- SEARCH BANNER HỖ TRỢ TÌM KIẾM -->
    <section class="hero-banner shadow-sm">
        <div class="container text-center">
            <h3 class="fw-bold mb-2"><i class="bi bi-search me-2"></i>HÃY TÌM NHỮNG GÌ BẠN CẦN!</h3>
            <p class="text-light opacity-75 mb-4">Tra cứu tài liệu, sách giáo trình, đề cương học phần và tài nguyên số HUB</p>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- TÌM KIẾM SÁCH CÓ AUTOCOMPLETE -->
                    <form action="{{ route('home') }}" method="GET">
                        <div class="input-group input-group-lg shadow-sm">
                            <select name="category" class="form-select border-0 bg-light text-dark fw-semibold" style="max-width: 160px; font-size: 0.95rem;">
                                <option value="Tất cả danh mục" {{ ($category ?? '') == 'Tất cả danh mục' ? 'selected' : '' }}>Tất cả danh mục</option>
                                <option value="Sách in" {{ ($category ?? '') == 'Sách in' ? 'selected' : '' }}>Sách in</option>
                                <option value="Sách điện tử" {{ ($category ?? '') == 'Sách điện tử' ? 'selected' : '' }}>Sách điện tử</option>
                                <option value="Đề cương HP" {{ ($category ?? '') == 'Đề cương HP' ? 'selected' : '' }}>Đề cương HP</option>
                            </select>
                            <input type="text" id="searchInput" list="searchSuggestions" name="keyword" value="{{ $keyword ?? '' }}" class="form-control border-0 fs-6" placeholder="Nhập nhan đề sách, tác giả, từ khóa chủ đề..." autocomplete="off" required>
                            <datalist id="searchSuggestions"></datalist>
                            <button class="btn btn-red px-4" type="submit">
                                <i class="bi bi-search me-1"></i> Tìm kiếm
                            </button>
                        </div>
                    </form>

                    <!-- HIỂN THỊ TỪ KHÓA NỔI BẬT -->
                    @if(isset($topKeywords) && count($topKeywords) > 0)
                        <div class="mt-3 text-white-50 fs-7 text-start ms-1">
                            <i class="bi bi-fire text-warning me-1"></i><strong>Từ khóa nổi bật:</strong>
                            @foreach($topKeywords as $kw)
                                <a href="{{ route('home', ['keyword' => $kw]) }}" class="badge bg-light text-dark text-decoration-none me-1 px-2 py-1 shadow-sm">
                                    {{ $kw }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <div class="container my-4">

        <!-- THÔNG BÁO FLASH MESSAGE -->
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

        @if(!empty($keyword))
            <!-- HIỂN THỊ KẾT QUẢ TÌM KIẾM -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="section-title m-0">KẾT QUẢ TÌM KIẾM: "{{ $keyword }}"</h4>
                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-x-circle me-1"></i>Xóa tìm kiếm
                </a>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mb-4">
                @forelse($searchResults as $book)
                    <div class="col">
                        <div class="card h-100 book-card position-relative">
                            
                            <!-- NÚT ĐÁNH DẤU TÀI LIỆU -->
                            @if(session()->has('user_id') && session('role') === 'SinhVien')
                                <form action="{{ route('bookmark.toggle') }}" method="POST" class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                    @csrf
                                    <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                    <button type="submit" class="btn btn-sm {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'btn-warning text-dark' : 'btn-light text-secondary' }} shadow-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="{{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'Bỏ đánh dấu' : 'Đánh dấu tài liệu' }}">
                                        <i class="bi {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'bi-bookmark-star-fill' : 'bi-bookmark' }}"></i>
                                    </button>
                                </form>
                            @endif

                            <div class="text-center p-3 bg-light border-bottom book-cover-wrapper" style="height: 220px;" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                <img src="{{ $book->AnhBia }}" class="img-fluid h-100 rounded shadow-sm" alt="Bìa sách" style="object-fit: contain;">
                            </div>
                            <div class="card-body d-flex flex-column p-3">
                                <h6 class="card-title fw-bold text-dark fs-7 text-truncate-2 mb-2" style="height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $book->NhanDe }}">
                                    {{ $book->NhanDe }}
                                </h6>
                                <p class="card-text fs-8 text-muted mb-1"><i class="bi bi-person me-1"></i>{{ $book->TacGia ?? 'Nhiều tác giả' }}</p>
                                <p class="card-text fs-8 text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i>{{ $book->NoiLuuGiu }}</p>
                                <div class="mt-auto d-grid gap-2">
                                    <button class="btn btn-sm btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                        <i class="bi bi-journal-bookmark me-1"></i>Xem Chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL CHI TIẾT SÁCH  -->
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
                                                                @if(!empty($book->ThuatNgucChuDe))
                                                                    <div class="d-flex flex-column gap-1">
                                                                        @foreach(array_filter(array_map('trim', explode(',', $book->ThuatNgucChuDe))) as $term)
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
                                                        <!-- HIỂN THỊ HỌC PHẦN LIÊN QUAN -->
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
                                    @if(session()->has('user_id') && session('role') === 'SinhVien')
                                        <form action="{{ route('bookmark.toggle') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                            <button type="submit" class="btn btn-sm {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'btn-warning' : 'btn-outline-warning' }} fw-semibold fs-8">
                                                <i class="bi {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'bi-bookmark-star-fill' : 'bi-bookmark' }} me-1"></i>
                                                {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'Đã đánh dấu' : 'Đánh dấu' }}
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-secondary fs-8" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center py-4">
                            <i class="bi bi-search fs-2 d-block mb-2"></i>
                            Không tìm thấy tài liệu nào phù hợp với từ khóa <strong>"{{ $keyword }}"</strong>.
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- PHÂN TRANG KẾT QUẢ TÌM KIẾM -->
            @if(method_exists($searchResults, 'links'))
                <div class="d-flex justify-content-center mb-5">
                    {{ $searchResults->links() }}
                </div>
            @endif
        @else
            <!-- HIỂN THỊ DANH SÁCH GỢI Ý  -->
            @if($sinhVien)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="section-title m-0">TÀI LIỆU GỢI Ý CHO BẠN</h4>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mb-5">
                    @forelse($recommendedBooks as $book)
                        <div class="col">
                            <div class="card h-100 book-card position-relative">
                                
                                <!-- NÚT ĐÁNH DẤU TÀI LIỆU -->
                                @if(session()->has('user_id') && session('role') === 'SinhVien')
                                    <form action="{{ route('bookmark.toggle') }}" method="POST" class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                        @csrf
                                        <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                        <button type="submit" class="btn btn-sm {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'btn-warning text-dark' : 'btn-light text-secondary' }} shadow-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="{{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'Bỏ đánh dấu' : 'Đánh dấu tài liệu' }}">
                                            <i class="bi {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'bi-bookmark-star-fill' : 'bi-bookmark' }}"></i>
                                        </button>
                                    </form>
                                @endif

                                <div class="position-relative text-center p-3 bg-light border-bottom book-cover-wrapper" style="height: 220px;" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                    <img src="{{ $book->AnhBia }}" class="img-fluid h-100 rounded shadow-sm" alt="Bìa sách" style="object-fit: contain;">
                                    <span class="position-absolute top-0 end-0 m-2 match-badge shadow-sm">
                                        <i class="bi bi-lightning-charge-fill me-1"></i>{{ round(($book->Score ?? 0) * 100, 1) }}% Phù hợp
                                    </span>
                                </div>
                                <div class="card-body d-flex flex-column p-3">
                                    <h6 class="card-title fw-bold text-dark fs-7 text-truncate-2 mb-2" style="height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $book->NhanDe }}">
                                        {{ $book->NhanDe }}
                                    </h6>
                                    <p class="card-text fs-8 text-muted mb-1"><i class="bi bi-person me-1"></i>{{ $book->TacGia ?? 'Nhiều tác giả' }}</p>
                                    <p class="card-text fs-8 text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i>{{ $book->NoiLuuGiu }}</p>
                                    <div class="mt-auto d-grid gap-2">
                                        <button class="btn btn-sm btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                            <i class="bi bi-journal-bookmark me-1"></i>Xem Chi tiết
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL CHI TIẾT SÁCH GỢI Ý -->
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
                                                <div class="badge bg-success p-2 w-100 fs-7">
                                                    <i class="bi bi-lightning-charge-fill me-1"></i>Độ tương thích: {{ round(($book->Score ?? 0) * 100, 1) }}%
                                                </div>
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
                                                                <td>{{ $book->Isbn ?? 'N/A' }}</td>
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
                                                            <!-- HIỂN THỊ HỌC PHẦN LIÊN QUAN -->
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
                                    <!-- MODAL FOOTER CHỨA ĐÁNH DẤU VÀ PHẢN HỒI -->
                                    <div class="modal-footer border-top pt-2">
                                        @if(session()->has('user_id') && session('role') === 'SinhVien')
                                            <!-- Phản hồi về mức độ phù hợp -->
                                            <form action="{{ route('feedback.submit') }}" method="POST" class="me-auto d-flex gap-1">
                                                @csrf
                                                <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                                <button type="submit" name="MucDanhGia" value="PhuHop" class="btn btn-sm btn-outline-success fs-8">
                                                    <i class="bi bi-hand-thumbs-up me-1"></i>Phù hợp
                                                </button>
                                                <button type="submit" name="MucDanhGia" value="KhongPhuHop" class="btn btn-sm btn-outline-secondary fs-8">
                                                    <i class="bi bi-hand-thumbs-down me-1"></i>Chưa phù hợp
                                                </button>
                                            </form>

                                            <!-- Đánh dấu tài liệu -->
                                            <form action="{{ route('bookmark.toggle') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                                <button type="submit" class="btn btn-sm {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'btn-warning' : 'btn-outline-warning' }} fw-semibold fs-8">
                                                    <i class="bi {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'bi-bookmark-star-fill' : 'bi-bookmark' }} me-1"></i>
                                                    {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'Đã đánh dấu' : 'Đánh dấu' }}
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-secondary fs-8" data-bs-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning text-center">Chưa tìm thấy sách gợi ý phù hợp cho học phần hiện tại.</div>
                        </div>
                    @endforelse
                </div>
            @else
                <!-- NẾU CHƯA ĐĂNG NHẬP (KHÁCH) -->
                <div class="alert alert-danger d-flex align-items-center shadow-sm mb-4" role="alert" style="background-color: #fdf2f2; border-color: #f8b4b4;">
                    <i class="bi bi-info-circle-fill fs-4 me-3 text-danger"></i>
                    <div class="text-dark">
                        <strong>Bạn đang xem thư viện với tư cách Khách.</strong> Hãy <a href="#" class="alert-link text-danger" data-bs-toggle="modal" data-bs-target="#loginModal">Đăng nhập tài khoản sinh viên</a> để hệ thống tự động gợi ý tài liệu phù hợp nhất với ngành học của bạn!
                    </div>
                </div>

                <h4 class="section-title mb-3">TÀI LIỆU NỔI BẬT TẠI THƯ VIỆN HUB</h4>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mb-5">
                    @foreach($allBooks as $book)
                        <div class="col">
                            <div class="card h-100 book-card position-relative">
                                
                                @if(session()->has('user_id') && session('role') === 'SinhVien')
                                    <form action="{{ route('bookmark.toggle') }}" method="POST" class="position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                        @csrf
                                        <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                        <button type="submit" class="btn btn-sm {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'btn-warning text-dark' : 'btn-light text-secondary' }} shadow-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="{{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'Bỏ đánh dấu' : 'Đánh dấu tài liệu' }}">
                                            <i class="bi {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'bi-bookmark-star-fill' : 'bi-bookmark' }}"></i>
                                        </button>
                                    </form>
                                @endif

                                <div class="text-center p-3 bg-light border-bottom book-cover-wrapper" style="height: 220px;" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                    <img src="{{ $book->AnhBia }}" class="img-fluid h-100 rounded shadow-sm" alt="Bìa sách" style="object-fit: contain;">
                                </div>
                                <div class="card-body d-flex flex-column p-3">
                                    <h6 class="card-title fw-bold text-dark fs-7 text-truncate-2 mb-2" style="height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="{{ $book->NhanDe }}">
                                        {{ $book->NhanDe }}
                                    </h6>
                                    <p class="card-text fs-8 text-muted mb-1"><i class="bi bi-person me-1"></i>{{ $book->TacGia ?? 'Nhiều tác giả' }}</p>
                                    <p class="card-text fs-8 text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i>{{ $book->NoiLuuGiu }}</p>
                                    <div class="mt-auto d-grid gap-2">
                                        <button class="btn btn-sm btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#bookModal-{{ $book->MaTaiLieu }}">
                                            <i class="bi bi-journal-bookmark me-1"></i>Xem Chi tiết
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL CHI TIẾT SÁCH TÀI LIỆU NỔI BẬT -->
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
                                                            <!-- HIỂN THỊ HỌC PHẦN LIÊN QUAN -->
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
                                        @if(session()->has('user_id') && session('role') === 'SinhVien')
                                            <form action="{{ route('bookmark.toggle') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="MaTaiLieu" value="{{ $book->MaTaiLieu }}">
                                                <button type="submit" class="btn btn-sm {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'btn-warning' : 'btn-outline-warning' }} fw-semibold fs-8">
                                                    <i class="bi {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'bi-bookmark-star-fill' : 'bi-bookmark' }} me-1"></i>
                                                    {{ in_array($book->MaTaiLieu, $markedBookIds ?? []) ? 'Đã đánh dấu' : 'Đánh dấu' }}
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-secondary fs-8" data-bs-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

    </div>

    <!-- MODAL POPUP ĐĂNG NHẬP -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content border-0 shadow-lg" style="border-top: 5px solid #a71d2a; border-radius: 12px;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/logo_hub.png') }}" alt="Logo Trường HUB" class="img-fluid mb-2" style="max-height: 75px;" onerror="this.onerror=null; this.src='https://via.placeholder.com/180x50?text=HUB+UNIVERSITY';">
                        <p class="text-muted fs-7 mb-0 fw-semibold text-uppercase" style="color: #003366 !important;">CỔNG THÔNG TIN THƯ VIỆN ĐIỆN TỬ</p>
                    </div>

                    @if($errors->has('error'))
                        <div class="alert alert-danger p-2 fs-7 text-center mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first('error') }}
                        </div>
                    @endif

                    
                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-semibold text-secondary">
                                Tài khoản (MSSV):
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input
                                    type="text"
                                    name="TenDangNhap"
                                    class="form-control @error('TenDangNhap') is-invalid @enderror"
                                    placeholder="Nhập tài khoản"
                                    value="{{ old('TenDangNhap') }}"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('TenDangNhap')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fs-7 fw-semibold text-secondary">
                                Mật khẩu:
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input
                                    type="password"
                                    name="MatKhau"
                                    class="form-control @error('MatKhau') is-invalid @enderror"
                                    placeholder="Nhập mật khẩu"
                                    required
                                >
                            </div>
                            @error('MatKhau')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-red w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> ĐĂNG NHẬP
                        </button>
                    </form>
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SCRIPT AUTOCOMPLETE AJAX CHO KHUNG TÌM KIẾM -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById('searchInput');
            const suggestionsList = document.getElementById('searchSuggestions');

            if (searchInput && suggestionsList) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    if (query.length < 2) {
                        suggestionsList.innerHTML = '';
                        return;
                    }

                    fetch(`/api/autocomplete?query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestionsList.innerHTML = '';
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item;
                                suggestionsList.appendChild(option);
                            });
                        })
                        .catch(err => console.error('Lỗi gợi ý tìm kiếm:', err));
                });
            }
        });
    </script>

    @if(session('show_login_modal') || $errors->has('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        });
    </script>
    @endif

</body>
</html>