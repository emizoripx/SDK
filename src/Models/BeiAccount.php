<?php

namespace Emizor\SDK\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class BeiAccount extends Model
{
    use HasFactory;

    protected $table = 'bei_accounts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'bei_enable',
        'bei_verified_setup',
        'bei_client_id',
        'bei_client_secret',
        'bei_token',
        'bei_deadline_token',
        'bei_host',
        'bei_branches',
        'bei_demo',
    ];

    protected $casts = [
        'bei_enable' => 'boolean',
        'bei_verified_setup' => 'boolean',
        'bei_branches' => 'array',
        'bei_demo' => 'boolean',
    ];

    // Generar UUID automáticamente si no se pasa
    protected static function booted()
    {
        static::creating(function ($account) {
            if (empty($account->id)) {
                $account->id = (string) Str::uuid();
            }
        });
    }
    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return \Emizor\SDK\Database\Factories\BeiAccountFactory::new();
    }

}
