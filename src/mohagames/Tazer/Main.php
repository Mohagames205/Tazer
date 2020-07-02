<?php

namespace mohagames\Tazer;

use mohagames\LobbyItems\controllers\ConfigController;
use mohagames\Tazer\tasks\PlayerFreezeTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\SnowballPoofParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;


class Main extends PluginBase implements Listener{

    public $freezedplayers;
    public $lasttime;

    public function onEnable()
    {
        $default = [
            "item_id" => 500,
            "custom_name" => "Tazer"
        ];
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML, $default);
        $config->save();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch($command->getName()){
            case "tazer":
                $config = $this->getConfig();
                $item = Item::get($config->get("item_id"));
                $item->setCustomName($config->get("custom_name"));

                $sender->getInventory()->addItem($item);
                $sender->sendMessage("U hebt nu een tazer ontvangen");

                return true;


            default:
                return false;
        }
    }


    public function onInteract(PlayerInteractEvent $e)
    {
        $config = $this->getConfig();
        if($config->get("item_id") == $e->getItem()->getId() && $config->get("custom_name") == $e->getItem()->getCustomName())
        {

            if(!isset($this->lasttime[$e->getPlayer()->getName()])) $this->lasttime[$e->getPlayer()->getName()] = time() - 3;

            if($this->lasttime[$e->getPlayer()->getName()] > time()){
                $e->getPlayer()->sendTip("§cPlease wait...");
                return;
            }

            $dir_vector = $e->getPlayer()->getDirectionVector();


            for($scalar = 0; $scalar <= 7; $scalar++){
                $location = $dir_vector->multiply($scalar)->add($e->getPlayer()->add(0, 1, 0));

                $e->getPlayer()->getLevel()->addParticle(new AngryVillagerParticle($location));

                $entity = $e->getPlayer()->getLevel()->getNearestEntity($location->subtract(0, 1, 0), 0.5);

                if($entity instanceof Player){
                    if($entity !== $e->getPlayer()) {

                        if(!isset($this->freezedplayers[$entity->getName()])) {
                            $this->freezedplayers[$entity->getName()] = true;
                            $this->getScheduler()->scheduleDelayedTask(new PlayerFreezeTask($this, $entity), 120);
                            $e->getPlayer()->sendMessage("§aJe hebt " . $entity->getName() . " geraakt.");
                        }
                        else{
                            $e->getPlayer()->sendMessage("§cDeze speler is al getazed!");
                        }
                    }
                }

            }

            $this->lasttime[$e->getPlayer()->getName()] = time() + 3;

        }
    }

    public function onPlayerMovement(PlayerMoveEvent $e)
    {
        if(isset($this->freezedplayers[$e->getPlayer()->getName()])){
            $e->setCancelled();
        }
    }


}