@extends('layouts.admin')

@section('title', 'Tổng quan & Thống kê Thư viện')
@section('page_title', 'TỔNG QUAN VÀ THỐNG KÊ THƯ VIỆN HUB')

@section('content')
<!-- Các thẻ chỉ số tổng quan -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-custom p-3 bg-white border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1 fs-7">TỔNG TÀI LIỆU KHO</h6>
                    <h3 class="fw-bold text-primary m-0">{{ $totalBooks }}</h3>
                </div>
                <i class="bi bi-journal-album fs-1 text-primary opacity-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-custom p-3 bg-white border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1 fs-7">SÁCH ĐANG CHO MƯỢN</h6>
                    <h3 class="fw-bold text-warning m-0">{{ $totalBorrowing }}</h3>
                </div>
                <i class="bi bi-journal-arrow-up fs-1 text-warning opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom p-3 bg-white border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1 fs-7">TỔNG LƯỢT PHẢN HỒI</h6>
                    <h3 class="fw-bold text-success m-0">{{ $totalFeedback }}</h3>
                </div>
                <i class="bi bi-chat-left-heart fs-1 text-success opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Bảng thống kê Mượn/Trả và Danh sách giao dịch mới -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="bi bi-bar-chart-steps me-2 text-info"></i>THỐNG KÊ LỊCH SỬ MƯỢN TRẢ</h6>
            <table class="table table-bordered text-center align-middle mt-3">
                <thead class="table-light">
                    <tr>
                        <th class="text-primary">Tổng số lượng đã mượn</th>
                        <th class="text-success">Tổng số lượng đã trả</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fs-4 fw-bold text-primary">{{ $totalBorrowedHistory }}</td>
                        <td class="fs-4 fw-bold text-success">{{ $totalReturned }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="bi bi-clock-history me-2 text-danger"></i>GIAO DỊCH MƯỢN SÁCH MỚI NHẤT</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="table-light">
                        <tr>
                            <th>Mã GD</th>
                            <th>Sinh viên (MSSV)</th>
                            <th>Tên sách</th>
                            <th>Ngày mượn</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBorrows as $b)
                            <tr>
                                <td><strong class="text-danger">{{ $b->MaGiaoDich }}</strong></td>
                                <td>{{ $b->sinhVien->HoTen ?? $b->MSSV }} ({{ $b->MSSV }})</td>
                                <td>{{ $b->taiLieu->NhanDe ?? $b->MaTaiLieu }}</td>
                                <td>{{ date('d/m/Y H:i', strtotime($b->NgayMuon)) }}</td>
                                <td>
                                    @if($b->TrangThai === 'DaTra')
                                        <span class="badge bg-success">Đã trả</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Đang mượn</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Chưa có giao dịch mượn sách.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-pie-chart-fill me-2 text-danger"></i>ĐÁNH GIÁ CHẤT LƯỢNG GỢI Ý</h6>
            <div class="mt-3">
                @if($totalFeedback > 0)
                    <div style="max-width: 280px; margin: 15px auto 0 auto;">
                        <canvas id="feedbackPieChart"></canvas>
                    </div>
                @else
                    <div class="alert alert-light text-muted text-center my-4">Chưa có dữ liệu phản hồi từ sinh viên.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-search me-2 text-primary"></i>TOP TỪ KHÓA TÌM KIẾM NHIỀU NHẤT</h6>
            <ul class="list-group list-group-flush fs-7 mt-2">
                @forelse($topKeywords as $k)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-tag-fill me-2 text-secondary"></i>{{ $k->TuKhoa }}</span>
                        <span class="badge bg-primary rounded-pill">{{ $k->count }} lượt</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Chưa có dữ liệu từ khóa tìm kiếm.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@if($totalFeedback > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('feedbackPieChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Phù hợp', 'Chưa phù hợp'],
                datasets: [{
                    data: [{{ $goodFeedback }}, {{ $badFeedback }}],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endif
@endsection