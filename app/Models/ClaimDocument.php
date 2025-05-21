<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimDocument extends Model
{
    //
    protected $fillable = [
        'bpjs_claims_id',
        'filename',
        'order',
    ];
}
