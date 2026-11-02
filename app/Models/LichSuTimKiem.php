<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuTimKiem extends Model
{
    protected $table = 'LichSuTimKiem';
    protected $primaryKey = 'MaLuotTimKiem';
    public $incrementing = false; //
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'MaLuotTimKiem',
        'MSSV',
        'TuKhoa',
        'ThoiDiemTimKiem'
    ];
}