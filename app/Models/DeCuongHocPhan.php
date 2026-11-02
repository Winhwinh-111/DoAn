<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeCuongHocPhan extends Model
{
    protected $table = 'DeCuongHocPhan';
    public $timestamps = false;
    protected $fillable = ['MaHocPhan', 'MaTaiLieu', 'NoiDungDeCuong', 'LoaiTaiLieu'];
}