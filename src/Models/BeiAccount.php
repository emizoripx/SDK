<?php

namespace Emizor\SDK\Models;

use Emizor\SDK\Repositories\ParametricRepository;
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
        'bei_defaults',
        'owner_id',
        'owner_type',
    ];

    protected $casts = [
        'bei_enable' => 'boolean',
        'bei_verified_setup' => 'boolean',
        'bei_branches' => 'array',
        'bei_defaults' => 'array',
        'bei_client_secret' => 'encrypted',
    ];

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

    public function owner()
    {
        return $this->morphTo();
    }

    public function checkSetup(): void
    {
        $this->checkToken();

        $this->checkParametric();
    }

    public function checkParametric(): void
    {
        if (!ParametricRepository::hasParametricSynced($this->id))
            throw new \Exception("No parametrics found, please complete parametrics sync.");
    }

    public function checkToken(): void
    {
        if (empty($this->bei_token))
            throw new \Exception("No token found, please refresh token before continue.");
    }
}
