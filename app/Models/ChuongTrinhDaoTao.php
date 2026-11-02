<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChuongTrinhDaoTao extends Model
{
    protected $table = 'ChuongTrinhDaoTao';
    // Bảng trung gian sử dụng khóa chính phức hợp (ma_nganh, ma_hoc_phan)
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['MaNganh', 'MaHocPhan'];

    public function nganh()
    {
        return $this->belongsTo(NganhHoc::class, 'MaNganh', 'MaNganh');
    }

    public function hocPhan()
    {
        return $this->belongsTo(HocPhan::class, 'MaHocPhan', 'MaHocPhan');
    }
}