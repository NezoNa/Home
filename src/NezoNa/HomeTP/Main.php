<?php

namespace NezoNa\HomeTP;

use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\player\GameMode;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use _64FF00\PurePerms\PurePerms;
use pocketmine\utils\Config;

/*
  _   _               _   _       
 | \ | |             | \ | |      
 |  \| | ___ _______ |  \| | __ _ 
 | . ` |/ _ \_  / _ \| . ` |/ _` |
 | |\  |  __// / (_) | |\  | (_| |
 |_| \_|\___/___\___/|_| \_|\__,_|

 Home plugins by vk.com/id_01
*/

class Main extends PluginBase implements Listener {
	/** @var Config */
	public $config;

    private $db;
	private $purePerms;
    public function onEnable(): void {
		
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
	$this->purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->unregister($commandMap->getCommand("sethome"));
        $commandMap->unregister($commandMap->getCommand("delhome"));
        $commandMap->unregister($commandMap->getCommand("homes"));
		$commandMap->unregister($commandMap->getCommand("home"));
		
        $commandMap->register("sethome", new Commands\Sethome($this));
		$commandMap->register("delhome", new Commands\Delhome($this));
		$commandMap->register("homes", new Commands\Homes($this));
        $commandMap->register("home", new Commands\Home($this));
		
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    
		$this->db = new \SQLite3($this->getDataFolder() . "homes.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS homes (id INTEGER PRIMARY KEY AUTOINCREMENT, nickname TEXT, namehome TEXT, home TEXT)");
    }
	
	public function getPlayerRank(Player $player): string{
		$group = $this->purePerms->getUserDataMgr()->getData($player)["group"];
		return $group ?? "No Rank";
	}
	
	public function getMaxHomesForPlayer(Player $player): int {
		$group = $this->getPlayerRank($player);
		$config = $this->getConfig()->get("ranks", []);

		return $config[$group] ?? $config['DEFAULT'] ?? 1;
	}

    public function getDb() {
        return $this->db;
    }

    public function onDisable(): void {
        $this->db->close();
    }
}
