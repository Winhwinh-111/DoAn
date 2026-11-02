<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaiLieu extends Model
{
    protected $table = 'TaiLieu';
    protected $primaryKey = 'MaTaiLieu';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaTaiLieu', 'NhanDe', 'TacGia', 'NhaXuatBan',
        'NamXuatBan', 'NgonNgu', 'ISBN', 'ThuatNguChuDe',
        'MoTa', 'NoiLuuGiu', 'TrangThaiSuDung', 'AnhBia'
    ];
}