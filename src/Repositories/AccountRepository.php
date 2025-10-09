<?php

namespace Emizor\SDK\Repositories;

use Emizor\SDK\Models\BeiAccount;
use Carbon\Carbon;

class AccountRepository
{
    public function create(array $data): BeiAccount
    {
        return BeiAccount::create($data);
    }

    public function updateOrCreate(array $attributes, array $values): BeiAccount
    {
        return BeiAccount::updateOrCreate($attributes, $values);
    }

    public function getAccount(string $account_id): BeiAccount
    {
        return BeiAccount::find($account_id);
    }

    // Métodos relacionados con el token
    public function saveToken(string $accountId, string $token, \DateTime $expiresAt): void
    {
        $account = $this->getAccount($accountId);
        $account->bei_token = $token;
        $account->bei_deadline_token = $expiresAt->format('Y-m-d H:i:s');
        $account->save();
    }

    public function getToken(string $clientId): ?array
    {
        $account = $this->getAccount($clientId);

        return [
            'token' => $account->bei_token??"",
            'expires_at' => $account->bei_deadline_token ?: Carbon::parse($account->bei_deadline_token),
        ];
    }

    public function saveDefaults(string $accountId, $defaults):void
    {
        $account = $this->getAccount($accountId);
        $account->bei_defaults = $defaults;
        $account->save();
    }

    public function getDefaults(string $accountId): array
    {
        $account = $this->getAccount($accountId);
        return $account->bei_defaults??[];
    }
}
