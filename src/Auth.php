<?php

declare(strict_types=1);

final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $stmt = Database::connection()->prepare('SELECT * FROM administradores WHERE email = :email AND ativo = 1 LIMIT 1');
        $stmt->execute([':email' => strtolower(trim($email))]);
        $admin = $stmt->fetch();
        if (!$admin || !password_verify($password, (string)$admin['senha'])) return false;
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['idAdministrador'];
        $_SESSION['admin_nome'] = (string)$admin['nome'];
        Database::connection()->prepare('UPDATE administradores SET ultimo_login = NOW() WHERE idAdministrador = :id')
            ->execute([':id' => (int)$admin['idAdministrador']]);
        return true;
    }

    public static function check(): bool { return !empty($_SESSION['admin_id']); }
    public static function require(): void { if (!self::check()) Support::redirect('/admin/login.php'); }
    public static function logout(): void { unset($_SESSION['admin_id'], $_SESSION['admin_nome']); session_regenerate_id(true); }
}
