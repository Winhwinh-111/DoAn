<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinhVien extends Model
{
    protected $table = 'SinhVien';
    protected $primaryKey = 'MSSV';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['MSSV', 'HoTen', 'NgaySinh', 'GioiTinh', 'SoDienThoai', 'HanThe', 'MaNganh', 'MaTaiKhoan'];

    public function hocPhans()
    {
        return $this->belongsToMany(HocPhan::class, 'SinhVienHocPhan', 'MSSV', 'MaHocPhan')
                    ->withPivot('TrangThai');
    }

    public function nganh()
    {
        return $this->belongsTo(NganhHoc::class, 'MaNganh', 'MaNganh');
    }
}