<?php

namespace Emizor\SDK\Traits;

use Emizor\SDK\Models\BeiAccount;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasEmizorCredentials
{
    public function emizorCredential(): MorphOne
    {
        return $this->morphOne(BeiAccount::class, 'owner');
    }

    public function hasEmizorCredential(): bool
    {
        return $this->emizorCredential()->exists();
    }

    public function getEmizorCredentials(): ?BeiAccount
    {
        return $this->emizorCredential;
    }
}