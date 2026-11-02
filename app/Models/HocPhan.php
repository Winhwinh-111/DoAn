<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HocPhan extends Model
{
    protected $table = 'HocPhan';
    protected $primaryKey = 'MaHocPhan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Cho phép lưu tất cả các cột
    protected $guarded = [];

    public function nganh()
    {
        return $this->belongsToMany(NganhHoc::class, 'ChuongTrinhDaoTao', 'MaHocPhan', 'MaNganh');
    }
}