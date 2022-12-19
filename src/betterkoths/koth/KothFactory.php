<?php

declare(strict_types=1);

namespace betterkoths\koth;

use betterkoths\BetterKoths;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;

final class KothFactory {

    /** @var Koth[] */
    static private array $koths = [];
    static private ?Koth $kothActive = null;

    static public function getKothActive(): ?Koth {
        return self::$kothActive;
    }

    static public function getAll(): array {
        return self::$koths;
    }

    static public function get(string $name): ?Koth {
        return self::$koths[$name] ?? null;
    }

    static public function setKothActive(?Koth $kothActive = null): void {
        self::$kothActive = $kothActive;
    }

    static public function create(string $name, int $time, Position $firstPosition, Position $secondPosition, World $world): void {
        self::$koths[$name] = new Koth($name, $time, $firstPosition, $secondPosition, $world);
    }

    static public function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$koths[$name]);
    }

    static public function saveAll(): void {
        $config = new Config(BetterKoths::getInstance()->getDataFolder() . 'koths.yml', Config::YAML);
        $koths = [];

        foreach (self::$koths as $name => $koth) {
            $koths[$name] = $koth->serializeData();
        }
        $config->setAll($koths);
        $config->save();
    }

    static public function loadAll(): void {
        $config = new Config(BetterKoths::getInstance()->getDataFolder() . 'koths.yml', Config::YAML);

        foreach ($config->getAll() as $name => $data) {
            try {
                self::$koths[$name] = Koth::deserializeData($name, $data);
            } catch (\RuntimeException $exception) {
                BetterKoths::getInstance()->getLogger()->error('Koth ' . $name . ' ' . $exception->getMessage());
            }
        }
    }
}