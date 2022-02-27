<?php

/*
       _             _____           ______ __
      | |           |  __ \         |____  / /
      | |_   _ _ __ | |  | | _____   __ / / /_
  _   | | | | | '_ \| |  | |/ _ \ \ / // / '_ \
 | |__| | |_| | | | | |__| |  __/\ V // /| (_) |
  \____/ \__,_|_| |_|_____/ \___| \_//_/  \___/


This program was produced by JunDev76 and cannot be reproduced, distributed or used without permission.

Developers:
 - JunDev76 (https://github.jundev.me/)

Copyright 2022. JunDev76. Allrights reserved.
*/

namespace JunDev76\SpawnMenuItem;

use FormSystem\form\ModalForm;
use JunDev76\EconomySystem\EconomySystem;
use pocketmine\block\Block;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\WrittenBook;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class SpawnMenuItem extends PluginBase{

    protected Item $commandBook;

    public function getBook(Player $player) : Item{
        $bcontents = [];
        $added = [];
        foreach($this->getServer()->getCommandMap()->getCommands() as $command){
            if(substr($command->getName(), 0, 1) !== "#"){
                if(($permission = $command->getPermission()) !== null){
                    if($permission !== DefaultPermissions::ROOT_USER){
                        continue;
                    }
                }
                if(isset($added[$command->getName()])){
                    continue;
                }
                $added[$command->getName()] = true;

                $d = $command->getDescription();
                if($d instanceof Translatable){
                    $text = $d->getText();
                }else{
                    $text = $this->getServer()->getLanguage()->translateString($d);
                }
                $text = "§l§a[§r§8/" . $command->getName() . "§r§l§a] §r§7- " . $text;
                foreach($command->getAliases() as $alias){
                    $text .= "\n  §r§l§7- §r§8[/" . $alias . "§r§8]";
                }

                $bcontents[] = $text;
            }
        }

        $item = ItemFactory::getInstance()->get(ItemIds::WRITTEN_BOOK);
        if(!$item instanceof WrittenBook){
            return ItemFactory::getInstance()->get(0);
        }
        $item->setTitle("§r§l§a[크] §r§f명령어북");
        $item->setGeneration(WrittenBook::GENERATION_ORIGINAL);
        $item->setAuthor("§r§l§a크로스팜");
        $item->setCustomName("§r§l§a[크] §r§f명령어북");
        $item->setLore(["§r§l§f쉬운 크로스팜 플레이를 위한 도움책 입니다!"]);
        $item->getNamedTag()->setInt("commandbook", 1);
        $content = [];
        $con = "";
        foreach($bcontents as $k => $v){
            $con .= $v . "\n";
            if(($k !== 0 && $k % 6 === 0) || $k === count($bcontents) - 1){
                $content[] = $con;
                $con = "";
            }
        }
        foreach($content as $k => $v){
            $item->setPageText($k, $v);
        }
        return $item;
    }

    protected function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvent(PlayerInteractEvent::class, function(PlayerInteractEvent $ev){
            $player = $ev->getPlayer();
            $blockPos = $ev->getBlock()->getPosition();

            if($blockPos->getWorld()->getFolderName() !== 'spawnworld'){
                return;
            }

            if($blockPos->x === 245 && $blockPos->y === 65 && $blockPos->z === 240){
                $ev->cancel();
                $form = new ModalForm(function(Player $player, $data) : void{
                    if($data === true){
                        if(EconomySystem::getInstance()->getMoney($player) < 100){
                            $player->sendMessage('§l§a[시스템] §r§f돈이 부족해요.');
                            return;
                        }

                        if(!isset($this->commandBook)){
                            $this->commandBook = $this->getBook($player);
                        }

                        if(!$player->getInventory()->canAddItem($this->commandBook)){
                            $player->sendMessage('§l§a[시스템] §r§f인벤토리에 공간이 부족해요.');
                            return;
                        }

                        EconomySystem::getInstance()->reduceMoney($player, 100);

                        $player->getInventory()->addItem($this->commandBook);
                    }
                });
                $form->setTitle('');
                $form->setButton1('§l§a구매하기');
                $form->setButton2('§l취소');
                $form->setContent("§l§a명령어북§r§f을 구매할까요?\n\n100원이 소모됩니다.");
                $form->sendForm($player);
                return;
            }

            if($blockPos->x === 247 && $blockPos->y === 65 && $blockPos->z === 240){
                $ev->cancel();
                $form = new ModalForm(function(Player $player, $data) : void{
                    if($data === true){
                        if(EconomySystem::getInstance()->getMoney($player) < 100){
                            $player->sendMessage('§l§a[시스템] §r§f돈이 부족해요.');
                            return;
                        }

                        $item = ItemFactory::getInstance()->get(ItemIds::COMPASS);
                        $item->setCustomName('§r§a§l[ §f유저편의도구 §a]');

                        if(!$player->getInventory()->canAddItem($item)){
                            $player->sendMessage('§l§a[시스템] §r§f인벤토리에 공간이 부족해요.');
                            return;
                        }

                        EconomySystem::getInstance()->reduceMoney($player, 100);

                        $player->getInventory()->addItem($item);
                    }
                });
                $form->setTitle('');
                $form->setButton1('§l§a구매하기');
                $form->setButton2('§l취소');
                $form->setContent("§l§a유저편의도구§r§f를 구매할까요?\n\n100원이 소모됩니다.");
                $form->sendForm($player);
                return;
            }

            if($blockPos->x === 249 && $blockPos->y === 65 && $blockPos->z === 240){
                $ev->cancel();
                $this->getServer()->dispatchCommand($player, '광산');
                return;
            }

            if($blockPos->x === 251 && $blockPos->y === 65 && $blockPos->z === 240){
                $ev->cancel();
                $this->getServer()->dispatchCommand($player, '팜 목록');
                return;
            }

            if($blockPos->x === 253 && $blockPos->y === 65 && $blockPos->z === 240){
                $ev->cancel();
                $form = new ModalForm(function(Player $player, $data) : void{
                    if($data === true){
                        if(EconomySystem::getInstance()->getMoney($player) < 5000){
                            $player->sendMessage('§l§a[시스템] §r§f돈이 부족해요.');
                            return;
                        }

                        $item = ItemFactory::getInstance()->get(ItemIds::BREAD, 0, 10);

                        if(!$player->getInventory()->canAddItem($item)){
                            $player->sendMessage('§l§a[시스템] §r§f인벤토리에 공간이 부족해요.');
                            return;
                        }

                        EconomySystem::getInstance()->reduceMoney($player, 5000);

                        $player->getInventory()->addItem($item);
                    }
                });
                $form->setTitle('');
                $form->setButton1('§l§a구매하기');
                $form->setButton2('§l취소');
                $form->setContent("§l§a빵 10개§r§f를 구매할까요?\n\n5000원이 소모됩니다.");
                $form->sendForm($player);
                return;
            }

            if($blockPos->x === 255 && $blockPos->y === 65 && $blockPos->z === 240){
                $ev->cancel();
                $this->getServer()->dispatchCommand($player, '야간투시');
                return;
            }
        }, EventPriority::NORMAL, $this, true);
    }
}