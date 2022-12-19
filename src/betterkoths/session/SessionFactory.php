<?php

declare(strict_types=1);

namespace betterkoths\session;

use pocketmine\player\Player;

final class SessionFactory {

    /** @var Session[] */
    static private array $sessions = [];

    static public function get(Player $player): ?Session {
        return self::$sessions[$player->getXuid()] ?? null;
    }

    static public function create(Player $player, string $name, int $time): void {
        self::$sessions[$player->getXuid()] = new Session($name, $time);
    }

    static public function remove(Player $player): void {
        if (self::get($player) === null) {
            return;
        }
        unset(self::$sessions[$player->getXuid()]);
    }
}