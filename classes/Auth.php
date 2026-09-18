<?php

declare(strict_types=1);

/**
 * Session-based authentication using a 6-digit PIN checked against a bcrypt hash.
 */
class Auth
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Verifies the PIN against every stored login and starts an authenticated
     * session on the first match. Checking all rows (rather than assuming a
     * single admin row) avoids silently ignoring a valid PIN if the users
     * table ever ends up with more than one row (e.g. a re-seed that inserted
     * a new row instead of updating the existing one).
     */
    public function login(string $pin): bool
    {
        $stmt = $this->db->query('SELECT id, pin_hash FROM users');
        $matchedUser = null;

        foreach ($stmt as $user) {
            if (password_verify($pin, $user['pin_hash'])) {
                $matchedUser = $user;
                break;
            }
        }

        if ($matchedUser === null) {
            // Slow down brute-force attempts.
            usleep(300000);
            return false;
        }

        // Prevent session fixation.
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $matchedUser['id'];
        $_SESSION['logged_in_at'] = time();

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Middleware-style guard for API endpoints. Halts the request with a 401 JSON
     * response when the caller is not authenticated.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            Response::error('Session expired. Please log in again.', [], 401);
        }
    }

    /**
     * Guard for page controllers (redirects instead of returning JSON).
     */
    public static function requireAuthPage(): void
    {
        if (!self::check()) {
            header('Location: login.php');
            exit;
        }
    }
}
