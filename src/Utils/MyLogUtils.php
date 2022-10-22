<?php

declare(strict_types=1);

namespace App\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;

class MyLogUtils {

    public const DIRECTORY = __DIR__ . "/../../data/logs/monolog/mono.log";

    protected static array $loggers = [];

    protected static function getLogger(string $channel): Logger {
        if (!isset(self::$loggers[$channel])) {
            self::configureLogger($channel);
        }

        return self::$loggers[$channel];
    }

    protected static function configureLogger(string $channel): void {

        self::$loggers[$channel]= new Logger($channel);
        self::$loggers[$channel]->pushHandler(new StreamHandler(self::DIRECTORY, Level::Info));
    }

    public static function info(string $channel, string $msg, array $context = []) {
        
        self::getLogger($channel)->info($msg, $context);
    }
    public static function notice(string $channel, string $msg, array $context = []) {
        
        self::getLogger($channel)->notice($msg, $context);
    }
    public static function warning(string $channel, string $msg, array $context = []) {
        
        self::getLogger($channel)->warning($msg, $context);
    }
    public static function error(string $channel, string $msg, array $context = []) {
        
        self::getLogger($channel)->error($msg, $context);
    }
    public static function critical(string $channel, string $msg, array $context = []) {

        self::getLogger($channel)->critical($msg, $context);
    }
}