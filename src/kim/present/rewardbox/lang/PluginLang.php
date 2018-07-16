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

namespace kim\present\rewardbox\lang;

use pocketmine\lang\BaseLang;
use pocketmine\plugin\Plugin;

class PluginLang extends BaseLang{
	/** @var Plugin */
	private $plugin;

	/**
	 * @noinspection PhpMissingParentConstructorInspection
	 * PluginLang constructor.
	 *
	 * @param Plugin $plugin
	 * @param string $lang
	 */
	public function __construct(Plugin $plugin, string $lang){
		$this->langName = strtolower($lang);
		$this->plugin = $plugin;

		$this->load($lang);
		if(!self::loadLang($file = $plugin->getDataFolder() . "lang/" . self::FALLBACK_LANGUAGE . "/lang.ini", $this->fallbackLang)){
			$plugin->getLogger()->error("Missing required language file $file");
		}
	}

	/**
	 * @param string $lang
	 *
	 * @return bool
	 */
	public function load(string $lang) : bool{
		if($this->isAvailableLanguage($lang)){
			if(!self::loadLang($file = $this->plugin->getDataFolder() . "lang/" . $this->langName . "/lang.ini", $this->lang)){
				$this->plugin->getLogger()->error("Missing required language file $file");
			}else{
				return true;
			}
		}
		return false;
	}

	/**
	 * Read available language list from language.list file
	 *
	 * @return string[]
	 */
	public function getAvailableLanguageList() : array{
		return explode("\n", file_get_contents($this->plugin->getDataFolder() . "lang/language.list"));
	}

	/**
	 * @param string $lang
	 *
	 * @return bool
	 */
	public function isAvailableLanguage(string $lang) : bool{
		return in_array(strtolower($lang), $this->getAvailableLanguageList());
	}
}