<?php

declare(strict_types=1);

namespace betterkoths\koth;

use betterkoths\BetterKoths;
use betterkoths\utils\KothPosition;
use betterkoths\utils\Language;
use JetBrains\PhpStorm\ArrayShape;
use kitmap\claim\Claim;
use kitmap\session\SessionFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use RuntimeException;

final class Koth {

    private AxisAlignedBB $alignedBB;

    public function __construct(
        private string   $name,
        private int      $time,
        private Position $firstPosition,
        private Position $secondPosition,
        private World    $world,
        private ?Claim   $claim = null,
        private ?Player  $currentCapturer = null,
        private int      $currentTime = 0
    ) {
        $this->currentTime = $this->time;

        $minX = min($this->firstPosition->getFloorX(), $this->secondPosition->getFloorX());
        $maxX = max($this->firstPosition->getFloorX(), $this->secondPosition->getFloorX());
        $minY = min($this->firstPosition->getFloorY(), $this->secondPosition->getFloorY());
        $maxY = max($this->firstPosition->getFloorY(), $this->secondPosition->getFloorY());
        $minZ = min($this->firstPosition->getFloorZ(), $this->secondPosition->getFloorZ());
        $maxZ = max($this->firstPosition->getFloorZ(), $this->secondPosition->getFloorZ());

        $this->alignedBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX + 1, $maxY, $maxZ + 1);
    }

    static public function deserializeData(string $name, array $data): self {
        $worldName = $data['world'];

        if (!Server::getInstance()->getWorldManager()->isWorldGenerated($worldName)) {
            throw new RuntimeException('World not found.');
        }

        if (!Server::getInstance()->getWorldManager()->isWorldLoaded($worldName)) {
            Server::getInstance()->getWorldManager()->loadWorld($worldName);
        }
        $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
        $claim = null;

        if ($data['claim'] !== null) {
            $claimWorld = $data['claim']['world'];

            if (!Server::getInstance()->getWorldManager()->isWorldGenerated($claimWorld)) {
                throw new RuntimeException('Claim world not found.');
            }

            if (!Server::getInstance()->getWorldManager()->isWorldLoaded($claimWorld)) {
                Server::getInstance()->getWorldManager()->loadWorld($claimWorld);
            }
            $claimWorld = Server::getInstance()->getWorldManager()->getWorldByName($claimWorld);
            $claim = new Claim(name: $name, type: Claim::KOTH, world: $claimWorld, firstPosition: Position::fromObject(KothPosition::stringToVector($data['claim']['firstPosition']), $claimWorld), secondPosition: Position::fromObject(KothPosition::stringToVector($data['claim']['secondPosition']), $claimWorld));
        }
        return new self($name, (int) $data['time'], Position::fromObject(KothPosition::stringToVector($data['firstPosition']), $world), Position::fromObject(KothPosition::stringToVector($data['secondPosition']), $world), $world, $claim);
    }

    public function getCurrentTime(): int {
        return $this->currentTime;
    }

    public function getAlignedBB(): AxisAlignedBB {
        return $this->alignedBB;
    }

    public function setClaim(?Claim $claim): void {
        $this->claim = $claim;
    }

    public function checkKoth(): void {
        $currentCapturer = $this->currentCapturer;

        if ($currentCapturer === null) {
            $nearbyEntities = $this->world->getNearbyEntities($this->alignedBB);

            foreach ($nearbyEntities as $entity) {
                if ($entity instanceof Player) {
                    $session = SessionFactory::get($entity);

                    if ($session === null || $session->getFaction() === null) {
                        return;
                    }
                    $this->currentCapturer = $entity;
                    break;
                }
            }
            return;
        }
        $session = SessionFactory::get($currentCapturer);

        if (!$currentCapturer->isOnline() || $session === null || $session->getFaction() === null || !$this->alignedBB->isVectorInside($currentCapturer->getPosition())) {
            $this->currentCapturer = null;
            $this->currentTime = $this->time;
            return;
        }

        if ($this->currentTime <= 0) {
            Server::getInstance()->broadcastMessage(Language::get('win-koth', [
                '%playerName%' => $currentCapturer->getName(),
                '%factionName%' => $session->getFaction()->getName(),
                '%name%' => $this->name
            ]));
            $session->getFaction()->setPoints($session->getFaction()->getPoints() + BetterKoths::getInstance()->getConfig()->get('points'));

            $this->currentCapturer = null;
            $this->currentTime = $this->time;

            KothFactory::setKothActive();
            return;

        }
        $this->currentTime--;
    }

    public function getName(): string {
        return $this->name;
    }

    #[ArrayShape(['time' => "int", 'firstPosition' => "string", 'secondPosition' => "string", 'world' => "string", 'claim' => "array|null"])] public function serializeData(): array {
        $data = [
            'time' => $this->time,
            'firstPosition' => KothPosition::vectorToString($this->firstPosition),
            'secondPosition' => KothPosition::vectorToString($this->secondPosition),
            'world' => $this->world->getFolderName(),
            'claim' => null
        ];

        if ($this->claim !== null) {
            $claim = $this->claim;
            $data['claim'] = [
                'firstPosition' => KothPosition::vectorToString($claim->getFirstPosition()),
                'secondPosition' => KothPosition::vectorToString($claim->getSecondPosition()),
                'world' => $claim->getWorld()->getFolderName()
            ];
        }
        return $data;
    }
}