<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CauHinhGoiY extends Model
{
    protected $table = 'CauHinhGoiY';
    protected $primaryKey = 'MaCauHinh';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'MaCauHinh',
        'MaTaiKhoan',
        'TopN',
        'NguongCosine',
        'Created_at',
        'updated_at'
    ];

    public function taiKhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'MaTaiKhoan', 'MaTaiKhoan');
    }

    public function moHinhGoiYs()
    {
        return $this->hasMany(MoHinhGoiY::class, 'MaCauHinh', 'MaCauHinh');
    }
}