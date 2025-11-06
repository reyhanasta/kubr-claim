<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'pasien';

    protected $primaryKey = 'no_rkm_medis';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_rkm_medis',
        'nm_pasien',
        'no_peserta',
    ];

    public function bpjsClaims(): HasMany
    {
        return $this->hasMany(BpjsClaim::class, 'no_rkm_medis', 'no_rkm_medis');
    }
}
