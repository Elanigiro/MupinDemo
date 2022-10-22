<?php
declare(strict_types=1);
namespace App\Utils;

class MyPasswordUtils {

    // passwords:
    // - cointain between MIN_LEN and MAX_LEN characters
    // - only characters contained in ACCEPTED are valid
    // - must contain at least one of each category (lowercase, uppercase, digits, SYMBOLS)
    const MIN_LEN = 10;
    const MAX_LEN = 30;
    const SYMBOLS = '\\\\\/\(\)&%\$"\?^!@\[\]#\*\-\+\=';
    const ACCEPTED = 'a-zA-Z0-9' . self::SYMBOLS;
    const VALIDATION_PATTERN = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[' . self::SYMBOLS . '])[' . self::ACCEPTED . ']{' . self::MIN_LEN . ',' . self::MAX_LEN . '}$/';
    // html version apparently needs UNICODE escaping
    const SYMBOLS_HTML = '\x5c\/\(\)&%\$"\?^!@\[\]#\*\x2d\+\x3d';
    const ACCEPTED_HTML = 'a-zA-Z0-9' . self::SYMBOLS_HTML;
    const VALIDATION_PATTERN_HTML = '^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[' . self::SYMBOLS_HTML . '])[' . self::ACCEPTED_HTML . ']{' . self::MIN_LEN . ',' . self::MAX_LEN . '}$';

    public static function validPwd(string $pwd) : bool {

        return (bool)(preg_match(self::VALIDATION_PATTERN, $pwd));
    }

    public static function hashPwd(string $pwd) : string {

        return password_hash($pwd, PASSWORD_DEFAULT);
    }
}