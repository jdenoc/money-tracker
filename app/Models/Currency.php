<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model {

    const DEFAULT_CURRENCY_CODE = 'USD';
    protected $fillable = [
        'label', 'code', 'class', 'html',
    ];

}
