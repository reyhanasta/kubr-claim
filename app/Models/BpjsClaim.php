<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsClaim extends Model
{
    protected $table = 'bpjs_claims';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_rawatan' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeRawatJalan($query)
    {
        return $query->where('jenis_rawatan', 'R.Jalan');
    }

    public function scopeRawatInap($query)
    {
        return $query->where('jenis_rawatan', 'R.Inap');
    }

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->whereMonth('tanggal_rawatan', $month)
            ->whereYear('tanggal_rawatan', $year);
    }
}
