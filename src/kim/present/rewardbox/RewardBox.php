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
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0.0
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\rewardbox;

use kim\present\rewardbox\command\{
	CreateSubcommand, EditSubcommand, RemoveSubcommand, Subcommand
};
use kim\present\rewardbox\lang\PluginLang;
use pocketmine\command\{
	Command, CommandSender, PluginCommand
};
use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;

class RewardBox extends PluginBase{
	public const CREATE = 0;
	public const REMOVE = 1;
	public const EDIT = 2;

	/** @var RewardBox */
	private static $instance;

	/** @return RewardBox */
	public static function getInstance() : RewardBox{
		return self::$instance;
	}

	/** @var PluginLang */
	private $language;

	/** @var PluginCommand */
	private $command;

	/** @var Subcommand[] */
	private $subcommands;

	//TODO: reward boxs data

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 */
	public function onLoad() : void{
		self::$instance = $this;
	}

	/**
	 * Called when the plugin is enabled
	 */
	public function onEnable() : void{
		//Save default resources
		$this->saveResource("lang/eng/lang.ini", false);
		$this->saveResource("lang/kor/lang.ini", false);
		$this->saveResource("lang/language.list", false);

		//Load config file
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$config = $this->getConfig();

		//TODO: Check latest version

		//Load language file
		$this->language = new PluginLang($this, $config->getNested("settings.language"));
		$this->getLogger()->info($this->language->translateString("language.selected", [$this->language->getName(), $this->language->getLang()]));

		//TODO: Load individual chest data

		//Register main command
		$this->command = new PluginCommand($config->getNested("command.name"), $this);
		$this->command->setPermission("rewardbox.cmd");
		$this->command->setAliases($config->getNested("command.aliases"));
		$this->command->setUsage($this->language->translateString("commands.rewardbox.usage"));
		$this->command->setDescription($this->language->translateString("commands.rewardbox.description"));
		$this->getServer()->getCommandMap()->register($this->getName(), $this->command);

		//Register subcommands
		$this->subcommands = [
			self::CREATE => new CreateSubcommand($this),
			self::REMOVE => new RemoveSubcommand($this),
			self::EDIT => new EditSubcommand($this)
		];

		//Load permission's default value from config
		$permissions = $this->getServer()->getPluginManager()->getPermissions();
		$defaultValue = $config->getNested("permission.main");
		if($defaultValue !== null){
			$permissions["rewardbox.cmd"]->setDefault(Permission::getByName($config->getNested("permission.main")));
		}
		foreach($this->subcommands as $key => $subcommand){
			$label = $subcommand->getLabel();
			$defaultValue = $config->getNested("permission.children.{$label}");
			if($defaultValue !== null){
				$permissions["rewardbox.cmd.{$label}"]->setDefault(Permission::getByName($defaultValue));
			}
		}

		//TODO: Register event listeners
	}

	/**
	 * Called when the plugin is disabled
	 * Use this to free open things and finish actions
	 */
	public function onDisable() : void{
		//TODO: Save individual chest data
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param string        $label
	 * @param string[]      $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(empty($args[0])){
			$targetSubcommand = null;
			foreach($this->subcommands as $key => $subcommand){
				if($sender->hasPermission($subcommand->getPermission())){
					if($targetSubcommand === null){
						$targetSubcommand = $subcommand;
					}else{
						//Filter out cases where more than two command has permission
						return false;
					}
				}
			}
			$targetSubcommand->handle($sender);
		}else{
			$label = array_shift($args);
			foreach($this->subcommands as $key => $subcommand){
				if($subcommand->checkLabel($label)){
					$subcommand->handle($sender, $args);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @Override for multilingual support of the config file
	 *
	 * @return bool
	 */
	public function saveDefaultConfig() : bool{
		$resource = $this->getResource("lang/{$this->getServer()->getLanguage()->getLang()}/config.yml");
		if($resource === null){
			$resource = $this->getResource("lang/" . PluginLang::FALLBACK_LANGUAGE . "/config.yml");
		}

		if(!file_exists($configFile = "{$this->getDataFolder()}config.yml")){
			$ret = stream_copy_to_stream($resource, $fp = fopen($configFile, "wb")) > 0;
			fclose($fp);
			fclose($resource);
			return $ret;
		}
		return false;
	}

	/**
	 * @return PluginLang
	 */
	public function getLanguage() : PluginLang{
		return $this->language;
	}

	/**
	 * @return Subcommand[]
	 */
	public function getSubcommands() : array{
		return $this->subcommands;
	}
}