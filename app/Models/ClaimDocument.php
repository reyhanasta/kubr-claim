<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimDocument extends Model
{
    protected $fillable = [
        'bpjs_claims_id',
        'filename',
        'order',
        'disk',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'bpjs_claims_id' => 'integer',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(BpjsClaim::class, 'bpjs_claims_id');
    }
}
