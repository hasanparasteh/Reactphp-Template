<?php

namespace App\Helpers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    protected string $jwtKey;
    protected string $alg;

    public function __construct(string $jwtKey, string $alg = 'HS256')
    {
        $this->jwtKey = $jwtKey;
        $this->alg = $alg;
    }

    public function toJwt($array): array
    {
        if (is_array($array)) {
            $coded = JWT::encode($array, $this->jwtKey, $this->alg);
            return ['result' => true, 'value' => $coded];
        }
        return ['result' => false];
    }

    public function toArray($jwt): array
    {
        if (is_string($jwt)) {
            try {
                $coded = JWT::decode($jwt, new Key($this->jwtKey, $this->alg));
                return ['result' => true, 'value' => (array)$coded];
            } catch (Exception $e) {
                return ['result' => false, 'error' => $e->getMessage()];
            }
        }
        return ['result' => false];
    }

}