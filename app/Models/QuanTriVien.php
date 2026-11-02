<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuanTriVien extends Model
{
    protected $table = 'QuanTriVien';
    protected $primaryKey = 'MaQuanTri';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['MaQuanTri', 'HoTen', 'Email', 'SoDienThoai', 'ChucVu', 'MaTaiKhoan'];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }
}