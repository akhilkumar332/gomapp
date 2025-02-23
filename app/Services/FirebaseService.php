<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Google\Cloud\Core\ServiceBuilder;

class FirebaseService
{
    protected $auth;
    protected $firebase;

    public function __construct()
    {
        $serviceAccountPath = storage_path('app/firebase/service-account.json');

        if (file_exists($serviceAccountPath)) {
            $this->firebase = (new Factory)
                ->withServiceAccount($serviceAccountPath)
                ->create();

            $this->auth = $this->firebase->getAuth();
        }
    }

    /**
     * Verify Firebase ID token
     *
     * @param string $idToken
     * @return object|null
     */
    public function verifyIdToken($idToken)
    {
        try {
            if (!$this->auth) {
                throw new \Exception('Firebase Auth not initialized');
            }

            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            
            // Get the user details
            $uid = $verifiedIdToken->claims()->get('sub');
            $user = $this->auth->getUser($uid);

            return (object) [
                'uid' => $user->uid,
                'email' => $user->email,
                'phoneNumber' => $user->phoneNumber,
                'displayName' => $user->displayName,
                'photoUrl' => $user->photoUrl,
                'emailVerified' => $user->emailVerified,
            ];
        } catch (FailedToVerifyToken $e) {
            \Log::error('Firebase token verification failed: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            \Log::error('Firebase error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a custom token for a user
     *
     * @param string $uid
     * @param array $claims
     * @return string
     */
    public function createCustomToken($uid, array $claims = [])
    {
        try {
            if (!$this->auth) {
                throw new \Exception('Firebase Auth not initialized');
            }

            return $this->auth->createCustomToken($uid, $claims);
        } catch (\Exception $e) {
            \Log::error('Failed to create custom token: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user by phone number
     *
     * @param string $phoneNumber
     * @return object|null
     */
    public function getUserByPhone($phoneNumber)
    {
        try {
            if (!$this->auth) {
                throw new \Exception('Firebase Auth not initialized');
            }

            $users = $this->auth->listUsers();
            foreach ($users as $user) {
                if ($user->phoneNumber === $phoneNumber) {
                    return (object) [
                        'uid' => $user->uid,
                        'email' => $user->email,
                        'phoneNumber' => $user->phoneNumber,
                        'displayName' => $user->displayName,
                        'photoUrl' => $user->photoUrl,
                        'emailVerified' => $user->emailVerified,
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Failed to get user by phone: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create or update Firebase user
     *
     * @param array $properties
     * @return object|null
     */
    public function createOrUpdateUser(array $properties)
    {
        try {
            if (!$this->auth) {
                throw new \Exception('Firebase Auth not initialized');
            }

            // Check if user exists by phone number
            $existingUser = $this->getUserByPhone($properties['phoneNumber']);

            if ($existingUser) {
                // Update existing user
                $updatedUser = $this->auth->updateUser($existingUser->uid, $properties);
                return (object) [
                    'uid' => $updatedUser->uid,
                    'email' => $updatedUser->email,
                    'phoneNumber' => $updatedUser->phoneNumber,
                    'displayName' => $updatedUser->displayName,
                    'photoUrl' => $updatedUser->photoUrl,
                    'emailVerified' => $updatedUser->emailVerified,
                ];
            }

            // Create new user
            $newUser = $this->auth->createUser($properties);
            return (object) [
                'uid' => $newUser->uid,
                'email' => $newUser->email,
                'phoneNumber' => $newUser->phoneNumber,
                'displayName' => $newUser->displayName,
                'photoUrl' => $newUser->photoUrl,
                'emailVerified' => $newUser->emailVerified,
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to create/update Firebase user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete Firebase user
     *
     * @param string $uid
     * @return bool
     */
    public function deleteUser($uid)
    {
        try {
            if (!$this->auth) {
                throw new \Exception('Firebase Auth not initialized');
            }

            $this->auth->deleteUser($uid);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete Firebase user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification
     *
     * @param string $token
     * @param array $notification
     * @param array $data
     * @return bool
     */
    public function sendPushNotification($token, array $notification, array $data = [])
    {
        try {
            if (!$this->firebase) {
                throw new \Exception('Firebase not initialized');
            }

            $messaging = $this->firebase->getMessaging();

            $message = [
                'token' => $token,
                'notification' => $notification,
                'data' => $data,
            ];

            $messaging->send($message);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send push notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification to multiple tokens
     *
     * @param array $tokens
     * @param array $notification
     * @param array $data
     * @return array
     */
    public function sendMulticastPushNotification(array $tokens, array $notification, array $data = [])
    {
        try {
            if (!$this->firebase) {
                throw new \Exception('Firebase not initialized');
            }

            $messaging = $this->firebase->getMessaging();

            $message = [
                'notification' => $notification,
                'data' => $data,
            ];

            $response = $messaging->sendMulticast($message, $tokens);

            return [
                'success' => $response->successes()->count(),
                'failure' => $response->failures()->count(),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to send multicast push notification: ' . $e->getMessage());
            return [
                'success' => 0,
                'failure' => count($tokens),
            ];
        }
    }
}
