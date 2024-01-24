<?php

namespace NezoNa\HomeTP\Commands;

use NezoNa\HomeTP\Main;
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

class Home extends Command {
    private $plugin;
    public function __construct(Main $plugin) {
        parent::__construct("home", "Teleporting home or viewing a list of houses", "/home [name]");
        $this->plugin = $plugin;
        $this->setPermission("h.home");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }

        if (!$sender instanceof Player) {
            return;
        }
		
	if (count($args) < 1) {
		$sender->sendMessage("§e*§r » §cReference usage /home <name> or /home");
		return;
	}

	if (count($args) === 0) {
	    $this->sendHomeList($sender);
	    return;
	}


        $name = strtolower($args[0]);

        $db = $this->plugin->getDb();
        $stmt = $db->prepare("SELECT * FROM homes WHERE nickname = :nickname AND namehome = :namehome");
        $stmt->bindValue(":nickname", $sender->getName());
        $stmt->bindValue(":namehome", $name);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row !== false) {
            $homeData = json_decode($row["home"], true);
            $x = $homeData[0];
            $y = $homeData[1];
            $z = $homeData[2];

            $sender->teleport(new Position($x, $y, $z, $sender->getWorld()));
            $sender->sendMessage("§e*§r » §fYou were teleported home by§e '{$name}'!");
        } else {
            $sender->sendMessage("§e*§r » §cThe home§e '{$name}' §cnot found.");
        }
    }

    private function sendHomeList(Player $player): void {
        $db = $this->plugin->getDb();
        $stmt = $db->prepare("SELECT namehome FROM homes WHERE nickname = :nickname");
        $stmt->bindValue(":nickname", $player->getName());
        $result = $stmt->execute();

        $homes = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $homes[] = $row["namehome"];
        }

        if (empty($homes)) {
            $player->sendMessage("§e*§r » §cYou don't have a home!");
        } else {
            $player->sendMessage("§e*§r » §fYou homes: §ce" . implode(", ", $homes));
        }
    }
}
