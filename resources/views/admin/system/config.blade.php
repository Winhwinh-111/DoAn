@extends('layouts.admin')

@section('title', 'Cấu hình & Mô Hình')
@section('page_title', 'CẤU HÌNH THAM SỐ GỢI Ý & MÔ HÌNH GỢI Ý')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-sliders me-2 text-primary"></i>CẤU HÌNH THAM SỐ GỢI Ý</h6>
            <form action="{{ route('admin.system.config.save') }}" method="POST" class="mt-3 fs-7">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Số lượng tài liệu gợi ý tối đa (Top-N):</label>
                    <input type="number" name="TopN" class="form-control form-control-sm" value="{{ $config->TopN ?? 10 }}" required min="1">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ngưỡng lọc Cosine Similarity tối thiểu (0 đến 1):</label>
                    <input type="number" step="0.01" name="NguongCosine" class="form-control form-control-sm" value="{{ $config->NguongCosine ?? 0.15 }}" required>
                </div>

                <button type="submit" class="btn btn-sm btn-primary px-3 py-2"><i class="bi bi-save me-1"></i>Lưu Cấu hình</button>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom bg-white p-4 h-100">
            <h6 class="fw-bold text-dark border-bottom pb-2"><i class="bi bi-cpu me-2 text-success"></i>CẬP NHẬT MÔ HÌNH GỢI Ý (RETRAIN)</h6>
            <div class="mt-3 fs-7">
                <p class="mb-2">Mô hình hiện hành: <strong class="text-success">{{ $activeModel->MaMoHinh ?? 'MH_001' }}</strong></p>
                <p class="mb-3 text-muted">Đường dẫn tệp: <code>{{ $activeModel->DuongDanTep ?? 'models/tfidf_vectorizer_v1.pkl' }}</code></p>
                
                <form action="{{ route('admin.system.model.retrain') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn chạy lại quá trình tiền xử lý và tính toán lại Vector TF-IDF không?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success px-3 py-2">
                        <i class="bi bi-arrow-repeat me-1"></i>Chạy lại Huấn luyện Mô hình gợi ý (Retrain)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection