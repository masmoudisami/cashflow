<?php
class Session {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    public static function redirect($url) {
        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        } else {
            echo "<script>window.location.href='" . $url . "';</script>";
            exit;
        }
    }
}