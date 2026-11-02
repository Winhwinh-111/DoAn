<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckAdminRole
{
    // Kiểm tra quyền truy cập trang quản trị
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Session::has('user_id')) {
            return redirect()->route('home')->withErrors(['error' => 'Vui lòng đăng nhập tài khoản quản trị!']);
        }

        if (!in_array(Session::get('role'), $roles)) {
            abort(403, 'Bạn không có quyền truy cập vào phân hệ quản trị này!');
        }

        return $next($request);
    }
}