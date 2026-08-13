<?php

declare(strict_types=1);

final class Support
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function csrf(): string
    {
        if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        return (string)$_SESSION['_csrf'];
    }

    public static function checkCsrf(?string $token): bool
    {
        return is_string($token) && isset($_SESSION['_csrf']) && hash_equals((string)$_SESSION['_csrf'], $token);
    }

    public static function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    public static function slug(string $text): string
    {
        $text = trim($text);
        if (function_exists('transliterator_transliterate')) {
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        } else {
            $text = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text);
        }
        $text = preg_replace('/[^a-z0-9]+/', '-', strtolower($text)) ?? '';
        return trim($text, '-') ?: 'oferta-' . date('YmdHis');
    }

    public static function decimal(string|float|int $value): float
    {
        if (is_string($value)) {
            $value = trim(str_replace(['R$', ' '], '', $value));
            if (str_contains($value, ',') && str_contains($value, '.')) $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        return round((float)$value, 2);
    }

    public static function cpfDigits(string $cpf): string
    {
        return preg_replace('/\D+/', '', $cpf) ?? '';
    }

    public static function validCpf(string $cpf): bool
    {
        $cpf = self::cpfDigits($cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) $sum += (int)$cpf[$i] * (($t + 1) - $i);
            $digit = ((10 * $sum) % 11) % 10;
            if ((int)$cpf[$t] !== $digit) return false;
        }
        return true;
    }

    public static function phoneDigits(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    public static function redirect(string $path): never
    {
        if (preg_match('#^https?://#i', $path)) $url = $path;
        else $url = APP_URL . '/' . ltrim($path, '/');
        header('Location: ' . $url);
        exit;
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function flashes(): array
    {
        $items = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return is_array($items) ? $items : [];
    }

    public static function clientIp(): string
    {
        return (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public static function randomCode(string $prefix = ''): string
    {
        return $prefix . rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
    }
}
