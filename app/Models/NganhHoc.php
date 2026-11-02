<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NganhHoc extends Model
{
    protected $table = 'NganhHoc';
    protected $primaryKey = 'MaNganh';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    public function hocPhans()
    {
        return $this->belongsToMany(HocPhan::class, 'ChuongTrinhDaoTao', 'MaNganh', 'MaHocPhan');
    }
}