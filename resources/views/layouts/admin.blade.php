<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cổng Quản trị Thư viện HUB')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --blue-main: #003366;
            --red-main: #a71d2a;
        }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background-color: var(--blue-main); min-height: 100vh; color: white; }
        .sidebar .nav-link { color: #cfd8dc; padding: 12px 20px; font-weight: 500; font-size: 0.95rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: rgba(255, 255, 255, 0.12); border-left: 4px solid var(--red-main); }
        .top-navbar { background-color: #ffffff; border-bottom: 2px solid var(--red-main); }
        .card-custom { border: none; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .admin-profile-dropdown .dropdown-menu {
            display: none;
            opacity: 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
            transform: translateY(10px);
        }
        .admin-profile-dropdown:hover .dropdown-menu {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar p-0 sticky-top" style="height: 100vh; overflow-y: auto;">
                <div class="p-3 text-center border-bottom border-secondary">
                    <img src="{{ asset('images/logo_hub.png') }}" height="42" alt="HUB Logo" onerror="this.onerror=null; this.src='https://via.placeholder.com/150x42?text=HUB+ADMIN';">
                    <div class="mt-2 text-uppercase text-light fw-bold fs-8">HỆ THỐNG QUẢN TRỊ HUB</div>
                </div>
                
                <ul class="nav flex-column mt-3">
                    @if(session('role') === 'QuanTriThuVien')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.library.dashboard') ? 'active' : '' }}" href="{{ route('admin.library.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Thống kê tổng quan hệ thống (Thư viện)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.library.documents*') ? 'active' : '' }}" href="{{ route('admin.library.documents.index') }}">
                                <i class="bi bi-journal-album me-2"></i>Quản lý Tài liệu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.library.borrow*') ? 'active' : '' }}" href="{{ route('admin.library.borrow.index') }}">
                                <i class="bi bi-arrow-left-right me-2"></i>Quản lý Mượn trả
                            </a>
                        </li>
                    @elseif(session('role') === 'QuanTriHeThong')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.system.dashboard') ? 'active' : '' }}" href="{{ route('admin.system.dashboard') }}">
                                <i class="bi bi-cpu me-2"></i>Thống kê tổng quan hệ thống (IT)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.system.import*') ? 'active' : '' }}" href="{{ route('admin.system.import.view') }}">
                                <i class="bi bi-file-earmark-arrow-up me-2"></i>Nhập dữ liệu Đào tạo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.system.config*') ? 'active' : '' }}" href="{{ route('admin.system.config.view') }}">
                                <i class="bi bi-sliders me-2"></i>Cấu hình & Mô Hình
                            </a>
                        </li>
                        
                    @endif
                    
                    <li class="nav-item mt-4 border-top border-secondary pt-2">
                        <a class="nav-link text-warning" href="{{ route('home') }}">
                            <i class="bi bi-globe me-2"></i>Xem Trang người dùng
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-9 col-lg-10 ms-sm-auto p-0">
                <!-- THANH TOP-NAVBAR -->
                <div class="top-navbar p-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark">@yield('page_title', 'Bảng điều khiển')</h5>
                    
                    <!-- Phần Profile Quản trị viên góc phải -->
                    <div class="dropdown admin-profile-dropdown position-relative">
                        
                        <!-- 1. Hiển thị mặc định (Tên và Vai trò) -->
                        <div class="d-flex align-items-center" style="cursor: pointer;">
                            <div class="text-end me-2">
                                <div class="fw-bold fs-6 text-dark">
                                    {{ Auth::user()->HoTen ?? session('admin_name', 'Chưa cập nhật') }}
                                </div>
                                <div class="fs-8 text-muted">
                                    {{ Auth::user()->VaiTro ?? session('role', 'Chưa phân quyền') }}
                                </div>
                            </div>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->HoTen ?? session('admin_name', 'Admin')) }}&background=0D8ABC&color=fff" alt="Avatar" class="rounded-circle shadow-sm" width="40" height="40">
                        </div>

                        <!-- 2. Bảng thông tin chi tiết hiện khi Hover (Lấy 100% từ DB) -->
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-3 mt-1" style="min-width: 270px; right: 0; left: auto; z-index: 1050;">
                            <li class="border-bottom pb-2 mb-2 text-center">
                                <h6 class="fw-bold mb-0 text-primary">
                                    {{ Auth::user()->HoTen ?? session('admin_name', 'Chưa cập nhật') }}
                                </h6>
                                <small class="text-muted">
                                    {{ Auth::user()->VaiTro ?? session('role', 'Chưa phân quyền') }}
                                </small>
                            </li>
                            
                            <!-- Mã quản trị viên -->
                            <li class="fs-7 mb-2 text-nowrap">
                                <i class="bi bi-person-vcard text-primary me-2"></i>
                                <strong>Mã QTV:</strong> 
                                {{ Auth::user()->MaQuanTri ?? Auth::user()->MaQuanTri ?? session('MaQuanTri', 'N/A') }}
                            </li>

                            <!-- Chức vụ -->
                            <li class="fs-7 mb-2 text-nowrap">
                                <i class="bi bi-briefcase text-success me-2"></i>
                                <strong>Chức vụ:</strong> 
                                {{ Auth::user()->ChucVu ?? session('ChucVu', 'Chưa cập nhật') }}
                            </li>

                            <!-- Số điện thoại -->
                            <li class="fs-7 mb-2 text-nowrap">
                                <i class="bi bi-telephone text-warning me-2"></i>
                                <strong>SĐT:</strong> 
                                {{ Auth::user()->SoDienThoai ?? session('SoDienThoai', 'Chưa cập nhật') }}
                            </li>

                            <!-- Email -->
                            <li class="fs-7 mb-2 text-nowrap">
                                <i class="bi bi-envelope text-danger me-2"></i>
                                <strong>Email:</strong> 
                                {{ Auth::user()->Email ?? session('Email', 'Chưa cập nhật') }}
                            </li>
                            
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Nút Đăng xuất -->
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger text-center fw-bold rounded py-1">
                                        <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="px-4 pt-3">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                </div>

                <div class="p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>