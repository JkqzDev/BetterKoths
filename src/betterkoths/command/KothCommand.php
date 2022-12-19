<?php

declare(strict_types=1);

namespace betterkoths\command;

use betterkoths\koth\KothFactory;
use betterkoths\session\SessionFactory;
use betterkoths\utils\Language;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class KothCommand extends Command {

    public function __construct() {
        parent::__construct('koth', 'Command for koths.');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (count($args) < 1) {
            $sender->sendMessage(Language::get('usage', ['%usage%' => '/koth list']));
            return;
        }

        switch (strtolower($args[0])) {
            case 'list':
                $message = '&7';

                foreach (KothFactory::getAll() as $name => $koth) {
                    $message .= PHP_EOL . Language::get('list-format', [
                            '%name%' => $name,
                            '%minX%' => (string) $koth->getAlignedBB()->minX,
                            '%minZ%' => (string) $koth->getAlignedBB()->minZ,
                        ], true, false);
                }
                $message .= PHP_EOL . '&7';

                $sender->sendMessage(TextFormat::colorize($message));
                break;

            case 'start':
                if (!$this->testPermission($sender, 'start.koth.command')) {
                    return;
                }

                if (count($args) < 2) {
                    $sender->sendMessage(Language::get('usage', ['%usage%' => '/koth start [kothName]']));
                    return;
                }
                $koth = KothFactory::get($args[1]);

                if ($koth === null) {
                    $sender->sendMessage(Language::get('doesnt-exist', ['%koth%' => $args[1]]));
                    return;
                }
                KothFactory::setKothActive($koth);
                $sender->getServer()->broadcastMessage(Language::get('started', ['%koth%' => $koth->getName()]));
                break;

            case 'create':
                if (!$sender instanceof Player) {
                    return;
                }

                if (!$this->testPermission($sender, 'create.koth.command')) {
                    return;
                }

                if (SessionFactory::get($sender) !== null) {
                    $sender->sendMessage(Language::get('already-creating'));
                    return;
                }

                if (count($args) < 3) {
                    $sender->sendMessage(Language::get('usage', ['%usage%' => '/koth create [kothName] [time]']));
                    return;
                }
                $kothName = $args[1];
                $time = $args[2];

                if (KothFactory::get($kothName) !== null) {
                    $sender->sendMessage(Language::get('already-exists', ['%koth%' => $kothName]));
                    return;
                }

                if (!is_numeric($time)) {
                    $sender->sendMessage(Language::get('invalid-time'));
                    return;
                }
                SessionFactory::create($sender, $kothName, (int) $time);
                $sender->sendMessage(Language::get('start-creating'));
                break;

            case 'delete':
                if (!$this->testPermission($sender, 'delete.koth.command')) {
                    return;
                }

                if (count($args) < 2) {
                    $sender->sendMessage(Language::get('usage', ['%usage%' => '/koth delete [kothName]']));
                    return;
                }
                $koth = KothFactory::get($args[1]);

                if ($koth === null) {
                    $sender->sendMessage(Language::get('doesnt-exist', ['%koth%' => $args[1]]));
                    return;
                }
                KothFactory::remove($koth->getName());
                $sender->sendMessage(Language::get('deleted', ['%koth%' => $koth->getName()]));
                break;
        }
    }
}