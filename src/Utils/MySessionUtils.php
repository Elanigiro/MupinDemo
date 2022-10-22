<?php

declare(strict_types=1);

namespace App\Utils;

class MySessionUtils {

    protected const EXPIRATION = 60 * 60;

    protected static bool $set = false;

    /** first time set-up */  
    public static function start(): void {

        if (!self::$set) {

            session_start(['use_strict_mode' => true, 'cookie_httponly' => true, 'cookie_lifetime' => 0, 'cookie_samesite' => 'Lax']);

            // new session
            if (!isset($_SESSION['timestamp'])) {

                self::refresh();
            }
            // session expired
            else if ((time() - $_SESSION["timestamp"]) > self::EXPIRATION) {

                MyLogUtils::info('SESSION', 'Session expired', ['session' => session_id(), 'user' => self::currentUser()]);
                self::reset();
            }

            self::$set = true;
        }
    }

    public static function reset(): void {

        session_unset();
        session_regenerate_id(true);
        self::refresh();
    }

    /** it resets the timestamp timeout */
    public static function refresh(): void {

        $_SESSION["timestamp"] = time();
    }

    public static function logout(): ?string {

        $user = self::currentUser();
        unset($_SESSION['logged']);

        return $user;
    }

    public static function login(string $user): void {

        $_SESSION['logged'] = $user;
        self::refresh();
    }

    public static function currentUser(): ?string {

        return $_SESSION['logged']?? null;
    }
}