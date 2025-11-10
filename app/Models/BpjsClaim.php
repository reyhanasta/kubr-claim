<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function documents(): HasMany
    {
        return $this->hasMany(ClaimDocument::class, 'bpjs_claims_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function scopeRawatJalan($query)
    {
        return $query->where('jenis_rawatan', 'RJ');
    }

    public function scopeRawatInap($query)
    {
        return $query->where('jenis_rawatan', 'RI');
    }

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->whereMonth('tanggal_rawatan', $month)
            ->whereYear('tanggal_rawatan', $year);
    }
}
