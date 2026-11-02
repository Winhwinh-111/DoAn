<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaiKhoan;
use App\Models\SinhVien;
use App\Models\QuanTriVien;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    
public function login(Request $request)
{
    // Kiểm tra dữ liệu đầu vào
    $request->validate([
        'TenDangNhap' => 'required',
        'MatKhau' => 'required',
    ], [
        'TenDangNhap.required' => 'Vui lòng nhập tài khoản!',
        'MatKhau.required' => 'Vui lòng nhập mật khẩu!',
    ]);

    $hashedInput = hash('sha256', $request->MatKhau);

    $user = TaiKhoan::where('TenDangNhap', $request->TenDangNhap)
        ->where('MatKhau', $hashedInput)
        ->first();

    if ($user) {
        Session::put('user_id', $user->MaTaiKhoan);
        Session::put('username', $user->TenDangNhap);
        Session::put('role', $user->VaiTro);

        if ($user->VaiTro === 'SinhVien') {
            $sv = SinhVien::where('MaTaiKhoan', $user->MaTaiKhoan)->first();

            Session::put('MSSV', $sv ? $sv->MSSV : $user->TenDangNhap);
            Session::put('student_name', $sv ? $sv->HoTen : 'Sinh viên');

            return redirect()->route('home');
        }
        elseif (in_array($user->VaiTro, ['QuanTriThuVien', 'QuanTriHeThong'])) {
            $qtv = QuanTriVien::where('MaTaiKhoan', $user->MaTaiKhoan)->first();

            Session::put('admin_name', $qtv ? $qtv->HoTen : 'Cán bộ');
            Session::put('MaQuanTri', $qtv ? $qtv->MaQuanTri : 'Chưa có');
            Session::put('ChucVu', $qtv ? $qtv->ChucVu : 'Chưa cập nhật');
            Session::put('SoDienThoai', $qtv ? $qtv->SoDienThoai : 'Chưa cập nhật');
            Session::put('Email', $qtv ? $qtv->Email : 'Chưa cập nhật');

            if ($user->VaiTro === 'QuanTriThuVien') {
                return redirect()->route('admin.library.dashboard');
            } else {
                return redirect()->route('admin.system.dashboard');
            }
        }
    }

    // Sai tài khoản hoặc mật khẩu
    return redirect()->route('home')
        ->withErrors([
            'error' => 'Tên đăng nhập hoặc mật khẩu không chính xác!'
        ])
        ->withInput($request->only('TenDangNhap'))
        ->with('show_login_modal', true);
}
public function logout()
{
    Session::flush();
    return redirect()->route('home');
}
}