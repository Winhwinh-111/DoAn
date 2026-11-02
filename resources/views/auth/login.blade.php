<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Thư viện Đại học Ngân hàng TP.HCM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --blue-hub: #003366;
            --blue-button: #0f4c81;
            --blue-button-hover: #082d50;
        }
        body {
            background: linear-gradient(135deg, #003366 0%, #0f4c81 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            border-top: 5px solid var(--blue-hub);
        }
        .btn-blue-hub {
            background-color: var(--blue-button);
            border-color: var(--blue-button);
            color: white;
            font-weight: 600;
        }
        .btn-blue-hub:hover {
            background-color: var(--blue-button-hover);
            border-color: var(--blue-button-hover);
            color: white;
        }
    </style>
</head>
<body>

    <div class="card login-card shadow-lg p-4 bg-white">
        <!-- Logo Trường / Thư viện nằm trên cùng -->
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo_hub.png') }}" alt="Logo Trường Đại học Ngân hàng TP.HCM" class="img-fluid mb-2" style="max-height: 75px;">
            <p class="text-muted fs-7 mb-0">CỔNG THÔNG TIN THƯ VIỆN ĐIỆN TỬ (OPAC)</p>
        </div>

        @if($errors->has('error'))
            <div class="alert alert-danger p-2 fs-7 text-center mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first('error') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            
            <!-- Nhập Tài khoản -->
            <div class="mb-3">
                <label class="form-label fs-7 fw-semibold text-secondary">Tài khoản (MSSV):</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="TenDangNhap" class="form-control" placeholder="Nhập tài khoản">
                </div>
            </div>

            <!-- Nhập Mật khẩu -->
            <div class="mb-4">
                <label class="form-label fs-7 fw-semibold text-secondary">Mật khẩu:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="MatKhau" class="form-control" placeholder="Nhập mật khẩu" >
                </div>
            </div>

            <button type="submit" class="btn btn-blue-hub w-100 py-2 mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> ĐĂNG NHẬP
            </button>
        </form>

        <div class="text-center text-muted fs-8 border-top pt-3">
            Trường Đại học Ngân hàng TP.HCM (HUB)
        </div>
    </div>

</body>
</html>