<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Emizor\SDK\Traits\HasEmizorCredentials;

class Company extends Model
{
    use HasEmizorCredentials;

    protected $fillable = ['name'];
}