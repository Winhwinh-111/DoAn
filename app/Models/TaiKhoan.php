<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class TaiKhoan extends Authenticatable
{
    protected $table = 'TaiKhoan';
    protected $primaryKey = 'MaTaiKhoan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['MaTaiKhoan', 'TenDangNhap', 'MatKhau', 'VaiTro', 'NgayTao'];
    protected $hidden = ['MatKhau'];

    public function sinhVien()
    {
        return $this->hasOne(SinhVien::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }
}