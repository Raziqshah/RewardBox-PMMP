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

namespace kim\present\rewardbox\act;

use kim\present\rewardbox\RewardBox;
use kim\present\rewardbox\utils\HashUtils;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\tile\Chest;

class PlayerAct{
	/** @var PlayerAct[] */
	private static $acts = [];

	/**
	 * @return PlayerAct[]
	 */
	public static function getActs() : array{
		return PlayerAct::$acts;
	}

	/**
	 * @param Player $player
	 *
	 * @return PlayerAct|null
	 */
	public static function getAct(Player $player) : ?PlayerAct{
		return PlayerAct::$acts[$player->getLowerCaseName()] ?? null;
	}

	public static function unregisterAll() : void{
		PlayerAct::$acts = [];
	}

	/**
	 * @param PlayerAct $task
	 */
	public static function registerAct(PlayerAct $task) : void{
		PlayerAct::$acts[HashUtils::playerHash($task->player)] = $task;
	}

	/**
	 * @param PlayerAct $task
	 */
	public static function unregsiterAct(PlayerAct $task) : void{
		unset(PlayerAct::$acts[HashUtils::playerHash($task->player)]);
	}

	/** @var RewardBox */
	protected $plugin;

	/** @var Player */
	protected $player;

	/** @var \Closure */
	protected $closure;

	/**
	 * PlayerAct constructor.
	 *
	 * @param RewardBox $plugin
	 * @param Player    $player
	 * @param \Closure  $closure
	 */
	public function __construct(RewardBox $plugin, Player $player, \Closure $closure){
		$this->plugin = $plugin;
		$this->player = $player;
		$this->closure = $closure;
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onPlayerInteractEvent(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$chest = $this->player->level->getTile($event->getBlock());
			if($chest instanceof Chest){
				($this->closure)($chest);
				self::unregsiterAct($this);
			}else{
				$this->player->sendMessage($this->plugin->getLanguage()->translate("acts.generic.notChest"));
			}
			$event->setCancelled();
		}
	}
}
