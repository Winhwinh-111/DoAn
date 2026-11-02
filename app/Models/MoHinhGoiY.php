<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoHinhGoiY extends Model
{
    protected $table = 'MoHinhGoiY';
    protected $primaryKey = 'MaMoHinh';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaMoHinh',
        'MaTaiKhoan',
        'MaCauHinh',
        'ThoiDiemTao',
        'DuongDanTep',
        'LaHienHanh'
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }

    public function cauHinhGoiY()
    {
        return $this->belongsTo(CauHinhGoiY::class, 'MaCauHinh', 'MaCauHinh');
    }
}