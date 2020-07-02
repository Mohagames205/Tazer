<?php

namespace mohagames\Tazer\tasks;

use mohagames\Tazer\Main;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class PlayerFreezeTask extends Task{

    public $player;
    public $main;

    public function __construct(Main $main, Player $player)
    {
        $this->player = $player;
        $this->main = $main;
    }

    public function onRun(int $currentTick)
    {

        unset($this->main->freezedplayers[$this->player->getName()]);
        $this->player->sendMessage("Je kan weer bewegen");
        $this->player->addEffect(new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 100, 5));

    }


}