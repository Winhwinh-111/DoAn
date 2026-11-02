<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhDau extends Model
{
    protected $table = 'DanhDau';
    protected $primaryKey = 'MaDanhDau';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaDanhDau',
        'MSSV',
        'MaTaiLieu',
        'ThoiDiemDanhDau'
    ];

    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MSSV', 'MSSV');
    }

    public function taiLieu()
    {
        return $this->belongsTo(TaiLieu::class, 'MaTaiLieu', 'MaTaiLieu');
    }
}