<?php

namespace NezoNa\HomeTP\Commands;

use NezoNa\HomeTP\Main;
use pocketmine\player\Player;
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

class Delhome extends Command {
    private $plugin;
    public function __construct(Main $plugin) {
        parent::__construct("delhome", "Remove the teleportation point home", "/delhome name");
        $this->plugin = $plugin;
        $this->setPermission("h.delhome");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }
        if (!$sender instanceof Player) {
            return;
        }
        if (count($args) !== 1) {
			$sender->sendMessage("§e*§r » §cReference usage /delhome <name>");
            return;
        }

        $player = $sender;
        $name = strtolower($args[0]);
        $db = $this->plugin->getDb();

        $checkStmt = $db->prepare("SELECT * FROM homes WHERE nickname = :nickname AND namehome = :namehome");
        $checkStmt->bindValue(":nickname", $player->getName());
        $checkStmt->bindValue(":namehome", $name);
        $result = $checkStmt->execute();
        $homeData = $result->fetchArray(SQLITE3_ASSOC);

        if (!$homeData) {
            $sender->sendMessage("§e*§r » §cThe home§e '{$name}' §cnot found.");
            return;
        }

        $deleteStmt = $db->prepare("DELETE FROM homes WHERE nickname = :nickname AND namehome = :namehome");
        $deleteStmt->bindValue(":nickname", $player->getName());
        $deleteStmt->bindValue(":namehome", $name);
        $deleteStmt->execute();

        $sender->sendMessage("§e*§r » §fThe home§e '{$name}' §fsuccessfully deleted!");
    }
}
