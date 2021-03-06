<?php

/*
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://opensource.org/licenses/MIT MIT License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\rewardbox\command;

use kim\present\rewardbox\act\PlayerAct;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\tile\Chest;

class RemoveSubcommand extends Subcommand{
	public const LABEL = "remove";

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 */
	public function execute(CommandSender $sender, array $args = []) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.generic.onlyPlayer"));
			return;
		}

		PlayerAct::registerAct(new PlayerAct($this->plugin, $sender, function(Chest $chest) use ($sender) : bool{
			$messageId = "acts.create." . ($this->plugin->removeRewardBox($chest, true) ? "success" : "notRewardBox");
			$sender->sendMessage($this->plugin->getLanguage()->translate($messageId));
			return true;
		}));
		$sender->sendMessage($this->plugin->getLanguage()->translate("commands.rewardbox.remove"));
	}
}
