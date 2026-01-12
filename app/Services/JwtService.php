<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $key;

    public function __construct()
    {
        $this->key = config('app.jwt_secret');
    }

    public function generate(array $payload): string
    {
        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function decode(string $token): array
    {
        return (array) JWT::decode($token, new Key($this->key, 'HS256'));
    }
}