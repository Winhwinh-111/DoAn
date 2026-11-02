<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MuonTra extends Model
{
    protected $table = 'MuonTra';
    protected $primaryKey = 'MaGiaoDich';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaGiaoDich', 
        'MSSV', 
        'MaTaiLieu', 
        'NgayMuon', 
        'NgayTra', 
        'TrangThai', 
        'GhiChu'
    ];

    // Quan hệ lấy thông tin tài liệu
    public function taiLieu()
    {
        return $this->belongsTo(TaiLieu::class, 'MaTaiLieu', 'MaTaiLieu');
    }

    // Quan hệ lấy thông tin sinh viên
    public function sinhVien()
    {
        return $this->belongsTo(SinhVien::class, 'MSSV', 'MSSV');
    }
}