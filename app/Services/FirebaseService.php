<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class FirebaseService
{
    private FirebaseAuth $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials_path'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->auth = $factory->createAuth();
    }

    /**
     * Verify Firebase ID token
     *
     * @param string $idToken
     * @return array|null
     */
    public function verifyIdToken(string $idToken): ?array
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            return [
                'uid' => $verifiedIdToken->claims()->get('sub'),
                'phone_number' => $verifiedIdToken->claims()->get('phone_number'),
            ];
        } catch (FailedToVerifyToken $e) {
            return null;
        }
    }

    /**
     * Get user by phone number
     *
     * @param string $phoneNumber
     * @return array|null
     */
    public function getUserByPhoneNumber(string $phoneNumber): ?array
    {
        try {
            $user = $this->auth->getUserByPhoneNumber($phoneNumber);
            return [
                'uid' => $user->uid,
                'phone_number' => $user->phoneNumber,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create custom token
     *
     * @param string $uid
     * @param array $claims
     * @return string
     */
    public function createCustomToken(string $uid, array $claims = []): string
    {
        return $this->auth->createCustomToken($uid, $claims)->toString();
    }
}
