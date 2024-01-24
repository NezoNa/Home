<?php

namespace NezoNa\HomeTP\Commands;

use NezoNa\HomeTP\Main;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

/*
  _   _               _   _       
 | \ | |             | \ | |      
 |  \| | ___ _______ |  \| | __ _ 
 | . ` |/ _ \_  / _ \| . ` |/ _` |
 | |\  |  __// / (_) | |\  | (_| |
 |_| \_|\___/___\___/|_| \_|\__,_|

 Home plugins by vk.com/id_01
*/

class Sethome extends Command {
    private $plugin;
    public function __construct(Main $plugin) {
        parent::__construct("sethome", "Set a point for teleportation home", "/sethome name");
        $this->plugin = $plugin;
        $this->setPermission("h.sethome");
    }
    public function getPlugin(): Main {
        return $this->plugin;
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		$main = $this->getPlugin();
        if (!$this->testPermission($sender)) {
            return;
        }
        if (!$sender instanceof Player) {
            return;
        }

        if (count($args) !== 1) {
            $sender->sendMessage("§e*§r » §cReference usage /sethome <home>");
            return;
        }

        $player = $sender;
        $name = strtolower($args[0]);
        $home = $player->getPosition();
        $db = $this->plugin->getDb();

		$maxHomes = $main->getMaxHomesForPlayer($player);
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM homes WHERE nickname = :nickname");
        $checkStmt->bindValue(":nickname", $player->getName());
        $result = $checkStmt->execute();
        $count = $result->fetchArray(SQLITE3_ASSOC)['count'];

        if ($count >= $maxHomes) {
            $sender->sendMessage("§e*§r » §cYou can create no more than $maxHomes points for your home!");
            return;
        }
		
        $checkStmt = $db->prepare("SELECT * FROM homes WHERE nickname = :nickname AND namehome = :namehome");
        $checkStmt->bindValue(":nickname", $player->getName());
        $checkStmt->bindValue(":namehome", $name);
        $result = $checkStmt->execute();
        if ($result->fetchArray(SQLITE3_ASSOC)) {
            $sender->sendMessage("§e*§r » §cThe house named §e'{$name}' may already exist!");
            return;
        }

        $insertStmt = $db->prepare("INSERT INTO homes (nickname, namehome, home) VALUES (:nickname, :namehome, :home)");
        $insertStmt->bindValue(":nickname", $player->getName());
        $insertStmt->bindValue(":namehome", $name);
        $insertStmt->bindValue(":home", json_encode([$home->x, $home->y, $home->z]));
        $insertStmt->execute();

        $sender->sendMessage("§e*§r » §fThe point for the house §e'{$name}' §f is set!");
    }
}
