<?php

declare(strict_types=1);

namespace betterkoths;

use betterkoths\session\SessionFactory;
use hcf\claim\Claim;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;

final class EventHandler implements Listener {

    public function handleChat(PlayerChatEvent $event): void {
        $message = $event->getMessage();
        $player = $event->getPlayer();

        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($message === 'save') {
            $result = $session->create($player);

            if ($result) {
                $hcf_session = \hcf\session\SessionFactory::get($player);

                if ($hcf_session === null) {
                    return;
                }
                $handler = $hcf_session->startClaimCreatorHandler($hcf_session, $session->getName(), Claim::KOTH);

                try {
                    $handler->prepare($player);
                    $player->sendMessage(TextFormat::colorize('&cNow select the claim.'));
                } catch (\RuntimeException) {
                    $hcf_session->stopClaimCreatorHandler();
                }
            }
        } elseif ($message === 'cancel') {
            SessionFactory::remove($player);
            $player->sendMessage(TextFormat::colorize('&cKoth creation was cancelled.'));
        }
    }

    public function handleInteract(PlayerInteractEvent $event): void {
        $action = $event->getAction();
        $block = $event->getBlock();
        $item = $event->getItem();
        $player = $event->getPlayer();
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($item->getId() === ItemIds::STICK) {
            $event->cancel();

            if ($action === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                $world = $session->getWorld();

                if ($world !== null) {
                    if ($world->getFolderName() !== $player->getWorld()->getFolderName()) {
                        $player->sendMessage(TextFormat::colorize('&cInvalid position.'));
                        return;
                    }
                } else {
                    $session->setWorld($player->getWorld());
                }
                $session->setFirstPosition($block->getPosition());
                $player->sendMessage(TextFormat::colorize('&aYou have select first position.'));
            } elseif ($action === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                $world = $session->getWorld();

                if ($world !== null) {
                    if ($world->getFolderName() !== $player->getWorld()->getFolderName()) {
                        $player->sendMessage(TextFormat::colorize('&cInvalid position.'));
                        return;
                    }
                } else {
                    $session->setWorld($player->getWorld());
                }
                $session->setSecondPosition($block->getPosition());
                $player->sendMessage(TextFormat::colorize('&aYou have select second position.'));
            }
        }
    }

    public function handleQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        SessionFactory::remove($player);
    }
}