<?php

namespace Emizor\SDK\Models;

use Illuminate\Database\Eloquent\Model;

class BeiGlobalParametric extends Model
{
    protected $table = 'bei_global_parametrics';
    public $timestamps = false;

    protected $fillable = [
        'bei_code',
        'bei_description',
        'bei_type',
    ];
}
