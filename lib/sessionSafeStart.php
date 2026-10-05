<?php

declare(strict_types=1);

/**
 * Inicia la sesión de PHP de forma defensiva.
 *
 * Si el navegador envía una cookie con un ID de sesión que no cumple
 * el alfabeto o la longitud configurados en php.ini
 * (session.sid_bits_per_character / session.sid_length), la cookie se
 * descarta antes de invocar session_start() para que PHP genere un ID
 * nuevo y emita su Set-Cookie correspondiente.
 *
 * De esta forma se evitan los warnings:
 *   - "Session ID is too long or contains illegal characters..."
 *   - "Failed to read session data: files (path: ...)"
 *
 * Devuelve true si la sesión queda activa al regresar; false si las
 * sesiones están deshabilitadas o session_start() falla.
 */
function sessionSafeStart(array $options = []): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return true;
    }

    if (session_status() === PHP_SESSION_DISABLED) {
        return false;
    }

    $sessionName = session_name();
    if (is_string($sessionName) && $sessionName !== '' && isset($_COOKIE[$sessionName])) {
        $rawId = (string) $_COOKIE[$sessionName];
        if (!sessionSafeIsValidId($rawId)) {
            // Descartamos la cookie entrante. PHP no intentará leer el
            // archivo de sesión asociado y, al iniciar, generará un ID
            // nuevo enviando su Set-Cookie correspondiente.
            unset($_COOKIE[$sessionName]);
        }
    }

    return session_start($options);
}

/**
 * Valida un ID de sesión contra el alfabeto y longitud configurados
 * en php.ini (session.sid_bits_per_character y session.sid_length).
 */
function sessionSafeIsValidId(string $id): bool
{
    if ($id === '') {
        return false;
    }

    $bits = (int) ini_get('session.sid_bits_per_character');
    switch ($bits) {
        case 4:
            $pattern = '/\A[0-9a-f]+\z/';
            break;
        case 5:
            $pattern = '/\A[0-9a-v]+\z/';
            break;
        case 6:
        default:
            // PHP también admite "," y "-" cuando bits_per_character = 6.
            $pattern = '/\A[0-9a-zA-Z,\-]+\z/';
            break;
    }

    $maxLen = (int) ini_get('session.sid_length');
    if ($maxLen > 0 && strlen($id) > $maxLen) {
        return false;
    }

    return (bool) preg_match($pattern, $id);
}
