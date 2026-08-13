<?php

declare(strict_types=1);

final class Crypto
{
    private static function key(): string
    {
        if (!defined('APP_KEY') || strlen((string)APP_KEY) < 32) {
            throw new RuntimeException('APP_KEY não configurada.');
        }
        return hash('sha256', (string)APP_KEY, true);
    }

    public static function encrypt(?string $plain): ?string
    {
        $plain = trim((string)$plain);
        if ($plain === '') return null;
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) throw new RuntimeException('Falha ao criptografar credencial.');
        return base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(?string $encoded): string
    {
        $encoded = trim((string)$encoded);
        if ($encoded === '') return '';
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 29) return '';
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        return $plain === false ? '' : $plain;
    }
}
