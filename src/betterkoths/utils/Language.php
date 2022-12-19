<?php

declare(strict_types=1);

namespace betterkoths\utils;

use betterkoths\BetterKoths;
use pocketmine\utils\TextFormat;

final class Language {

    private static array $messages;
    private static string $prefix;

    public static function init(): void {
        self::$messages = BetterKoths::getInstance()->getConfig()->get('messages');
        self::$prefix = BetterKoths::getInstance()->getConfig()->get('prefix');
    }

    public static function get(string $key, array $params = [], bool $colorize = true, bool $prefix = true): string {

        if ($prefix) {
            $message = self::$prefix . ' ' . self::$messages[$key];
        } else {
            $message = self::$messages[$key];
        }

        foreach ($params as $search => $replace) {
            $message = str_replace($search, $replace, $message);
        }

        if ($colorize) {
            $message = TextFormat::colorize($message);
        }
        return $message;
    }
}