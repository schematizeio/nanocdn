<?php
namespace NanoCDN;

class Auth
{
    private const SESSION_KEY = 'nanocdn_admin';
    private const SESSION_USER = 'nanocdn_admin_user';
    private const SESSION_CSRF = 'nanocdn_csrf';

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function login(string $email, string $password): bool
    {
        self::init();
        $user = Database::fetchOne('SELECT id, email, password_hash, name FROM admin_users WHERE email = ?', [$email]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = true;
        $_SESSION[self::SESSION_USER] = [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
        ];
        return true;
    }

    public static function logout(): void
    {
        self::init();
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_USER]);
    }

    public static function check(): bool
    {
        self::init();
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    public static function user(): ?array
    {
        self::init();
        return $_SESSION[self::SESSION_USER] ?? null;
    }

    public static function requireAdmin(): void
    {
        if (!self::check()) {
            if (self::isApiRequest()) {
                json_response(['error' => 'Unauthorized', 'code' => 401], 401);
            } else {
                redirect(base_url('admin/login?expired=1'));
            }
            exit;
        }
    }

    public static function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false
            || (isset($_GET['__path']) && strpos($_GET['__path'], '/api/') === 0);
    }

    public static function csrfToken(): string
    {
        self::init();
        if (empty($_SESSION[self::SESSION_CSRF])) {
            $_SESSION[self::SESSION_CSRF] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_CSRF];
    }

    public static function csrfVerify(): bool
    {
        self::init();
        $token = $_POST['csrf_token'] ?? '';
        return $token !== '' && hash_equals($_SESSION[self::SESSION_CSRF] ?? '', $token);
    }

    public static function changePassword(int $userId, string $currentPassword, string $newPassword): ?string
    {
        self::init();
        $user = Database::fetchOne('SELECT id, password_hash FROM admin_users WHERE id = ?', [$userId]);
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return 'Senha atual incorreta.';
        }
        if (strlen($newPassword) < 6) {
            return 'Nova senha deve ter pelo menos 6 caracteres.';
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        Database::run('UPDATE admin_users SET password_hash = ? WHERE id = ?', [$hash, $userId]);
        return null;
    }
}
