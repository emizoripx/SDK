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

    public function findByClientId(string $clientId): ?BeiAccount
    {
        return BeiAccount::where('bei_client_id', $clientId)->first();
    }

    // Métodos relacionados con el token
    public function saveToken(string $clientId, string $token, \DateTime $expiresAt): void
    {
        $account = $this->findByClientId($clientId) ?? new BeiAccount(['bei_client_id' => $clientId]);
        $account->bei_token = $token;
        $account->bei_deadline_token = $expiresAt->format('Y-m-d H:i:s');
        $account->save();
    }

    public function getToken(string $clientId): ?array
    {
        $account = $this->findByClientId($clientId);

        if (!$account || !$account->bei_token) {
            return null;
        }

        return [
            'token' => $account->bei_token,
            'expires_at' => Carbon::parse($account->bei_deadline_token),
        ];
    }
}
