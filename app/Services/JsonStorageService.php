<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class JsonStorageService
{
    protected $storagePath = 'json/';
    protected $userData = [];

    public function __construct()
    {
        $this->ensureStorageDirectory();
    }

    private function ensureStorageDirectory()
    {
        if (!Storage::exists($this->storagePath)) {
            Storage::makeDirectory($this->storagePath);
        }
    }

    public function getUserFilePath($userId)
    {
        return $this->storagePath . "user_{$userId}.json";
    }

    public function loadUserData($userId)
    {
        $filePath = $this->getUserFilePath($userId);
        
        if (Storage::exists($filePath)) {
            $content = Storage::get($filePath);
            return json_decode($content, true) ?: $this->getDefaultDataStructure();
        }
        
        return $this->getDefaultDataStructure();
    }

    public function saveUserData($userId, $data)
    {
        $filePath = $this->getUserFilePath($userId);
        Storage::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
        return true;
    }

    private function getDefaultDataStructure()
    {
        return [
            'user' => [
                'id' => null,
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'created_at' => null
            ],
            'classes' => [],
            'tasks' => [],
            'notes' => [],
            'reminders' => [],
            'classColors' => [],
            'preferences' => [
                'darkMode' => false,
                'defaultView' => 'week'
            ],
            'profile' => [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com'
            ]
        ];
    }

    public function getAllUsers()
    {
        $files = Storage::files($this->storagePath);
        $users = [];
        
        foreach ($files as $file) {
            if (preg_match('/user_([^\.]+)\.json/', $file, $matches)) {
                $userId = $matches[1];
                $data = $this->loadUserData($userId);
                $users[$userId] = $data['user'];
            }
        }
        
        return $users;
    }

    public function createUser($email, $name, $passwordHash)
    {
        $userId = md5($email . time());
        
        $data = $this->getDefaultDataStructure();
        $data['user'] = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $data['profile'] = [
            'name' => $name,
            'email' => $email
        ];
        
        $this->saveUserData($userId, $data);
        return $userId;
    }

    public function findUserByEmail($email)
    {
        $users = $this->getAllUsers();
        
        foreach ($users as $userId => $userData) {
            if ($userData['email'] === $email) {
                $fullData = $this->loadUserData($userId);
                return [
                    'id' => $userId,
                    'data' => $fullData
                ];
            }
        }
        
        return null;
    }

    // CRUD Operations
    public function addItem($userId, $type, $item)
    {
        $data = $this->loadUserData($userId);
        $item['id'] = uniqid();
        $item['created_at'] = date('Y-m-d H:i:s');
        $data[$type][] = $item;
        $this->saveUserData($userId, $data);
        return $item;
    }

    public function updateItem($userId, $type, $itemId, $updatedItem)
    {
        $data = $this->loadUserData($userId);
        
        foreach ($data[$type] as &$item) {
            if ($item['id'] === $itemId) {
                $item = array_merge($item, $updatedItem);
                $item['updated_at'] = date('Y-m-d H:i:s');
                $this->saveUserData($userId, $data);
                return $item;
            }
        }
        
        return null;
    }

    public function deleteItem($userId, $type, $itemId)
    {
        $data = $this->loadUserData($userId);
        $data[$type] = array_filter($data[$type], function($item) use ($itemId) {
            return $item['id'] !== $itemId;
        });
        $this->saveUserData($userId, $data);
        return true;
    }

    public function getItems($userId, $type)
    {
        $data = $this->loadUserData($userId);
        return $data[$type] ?? [];
    }

    public function updatePreferences($userId, $preferences)
    {
        $data = $this->loadUserData($userId);
        $data['preferences'] = array_merge($data['preferences'], $preferences);
        $this->saveUserData($userId, $data);
        return $data['preferences'];
    }

    public function updateProfile($userId, $profile)
    {
        $data = $this->loadUserData($userId);
        $data['profile'] = array_merge($data['profile'], $profile);
        $data['user']['name'] = $profile['name'] ?? $data['user']['name'];
        $data['user']['email'] = $profile['email'] ?? $data['user']['email'];
        $this->saveUserData($userId, $data);
        return $data['profile'];
    }
}