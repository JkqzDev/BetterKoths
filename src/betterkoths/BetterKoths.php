<?php

declare(strict_types=1);

namespace betterkoths;

use betterkoths\command\KothCommand;
use betterkoths\koth\KothFactory;
use betterkoths\utils\Language;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

final class BetterKoths extends PluginBase {
    use SingletonTrait;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource('koths.yml');

        Language::init();
        KothFactory::loadAll();

        $this->getServer()->getCommandMap()->register('Koth', new KothCommand());
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $kothActive = KothFactory::getKothActive();
            $kothActive?->checkKoth();
        }), 20);
    }

    protected function onDisable(): void {
        KothFactory::saveAll();
    }
}