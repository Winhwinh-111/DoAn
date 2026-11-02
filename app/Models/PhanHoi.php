<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhanHoi extends Model
{
    protected $table = 'PhanHoi';
    protected $primaryKey = 'MaPhanHoi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaPhanHoi',
        'MSSV',
        'MaTaiLieu',
        'MucDanhGia',
        'ThoiDiem'
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