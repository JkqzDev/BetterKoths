<?php

declare(strict_types=1);

namespace betterkoths\session;

use betterkoths\koth\KothFactory;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;

final class Session {

    public function __construct(
        private string    $name,
        private int       $time,
        private ?Position $firstPosition = null,
        private ?Position $secondPosition = null,
        private ?World    $world = null
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getWorld(): ?World {
        return $this->world;
    }

    public function setFirstPosition(?Position $firstPosition): void {
        $this->firstPosition = $firstPosition;
    }

    public function setSecondPosition(?Position $secondPosition): void {
        $this->secondPosition = $secondPosition;
    }

    public function setWorld(?World $world): void {
        $this->world = $world;
    }

    public function create(Player $player): bool {
        if ($this->firstPosition === null) {
            $player->sendMessage(TextFormat::colorize('&cPlease, select first position'));
            return false;
        }

        if ($this->secondPosition === null) {
            $player->sendMessage(TextFormat::colorize('&cPlease, select second position'));
            return false;
        }

        if ($this->world === null) {
            $player->sendMessage(TextFormat::colorize('&cWorld is null'));
            return false;
        }
        KothFactory::create($this->name, $this->time, $this->firstPosition, $this->secondPosition, $this->world);
        $player->sendMessage(TextFormat::colorize('&aKoth created successfully.'));
        SessionFactory::remove($player);
        return true;
    }
}