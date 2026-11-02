@extends('layouts.admin')

@section('title', 'Thống kê Hệ thống')
@section('page_title', 'THỐNG KÊ TỔNG QUAN HỆ THỐNG')

@section('content')

<!-- Bảng chi tiết thống kê -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-diagram-3 me-2 text-danger"></i>THỐNG KÊ DỮ LIỆU ĐÀO TẠO</h6>
            <div class="mt-3 fs-7">
                <p class="mb-2 d-flex justify-content-between"><span>Tổng số ngành đào tạo:</span> <strong>{{ $majorCount }}</strong></p>
                <p class="mb-2 d-flex justify-content-between"><span>Tổng số học phần:</span> <strong>{{ $courseCount }}</strong></p>
                <p class="mb-2 d-flex justify-content-between"><span>Tổng số hồ sơ sinh viên:</span> <strong>{{ $studentCount }}</strong></p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-cpu me-2 text-primary"></i>LỊCH SỬ MÔ HÌNH GỢI Ý</h6>
            <div class="mt-3 fs-7">
                <p class="mb-2 d-flex justify-content-between"><span>Tổng số phiên bản mô hình:</span> <strong>{{ $totalModels }}</strong></p>
                <p class="mb-2 d-flex justify-content-between"><span>Mô hình đang hoạt động:</span> <strong class="text-success">{{ $activeModel->MaMoHinh ?? 'N/A' }}</strong></p>
                <p class="mb-0 d-flex justify-content-between"><span>Thời điểm tạo mô hình:</span> <strong>{{ $activeModel ? date('d/m/Y H:i', strtotime($activeModel->ThoiDiemTao)) : 'N/A' }}</strong></p>
            </div>
        </div>
    </div>
</div>
@endsection