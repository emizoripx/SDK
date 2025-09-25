<?php

namespace Emizor\SDK\Models;

use Illuminate\Database\Eloquent\Model;

class BeiSpecificParametric extends Model
{
    protected $table = 'bei_specific_parametrics';
    public $timestamps = false;

    protected $fillable = [
        'bei_code',
        'bei_description',
        'bei_activity_code',
        'bei_type',
        'bei_account_id',
    ];
}
