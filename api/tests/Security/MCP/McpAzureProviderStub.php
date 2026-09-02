<?php

declare(strict_types=1);

namespace App\Tests\Security\MCP;

use Firebase\JWT\Key;
use TheNetworg\OAuth2\Client\Provider\Azure;

class McpAzureProviderStub extends Azure
{
    public const KEY_ID = 'test-key-id';

    private static ?\OpenSSLAsymmetricKey $privateKey = null;
    private static ?array $verificationKeys = null;

    public static function getTestPrivateKey(): \OpenSSLAsymmetricKey
    {
        self::boot();

        return self::$privateKey;
    }

    public function getJwtVerificationKeys(): array
    {
        self::boot();

        return self::$verificationKeys;
    }

    private static function boot(): void
    {
        if (null !== self::$privateKey) {
            return;
        }

        $privateKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => \OPENSSL_KEYTYPE_RSA]);
        \assert($privateKey instanceof \OpenSSLAsymmetricKey);
        self::$privateKey = $privateKey;

        $details = openssl_pkey_get_details($privateKey);
        \assert(false !== $details);
        $publicKey = openssl_pkey_get_public($details['key']);
        \assert($publicKey instanceof \OpenSSLAsymmetricKey);

        self::$verificationKeys = [self::KEY_ID => new Key($publicKey, 'RS256')];
    }
}
