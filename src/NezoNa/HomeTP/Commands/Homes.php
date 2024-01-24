<?php

namespace NezoNa\HomeTP\Commands;

use NezoNa\HomeTP\Main;
use pocketmine\math\Vector3;
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

class Homes extends Command {
    private $plugin;
    public function __construct(Main $plugin)
    {
        parent::__construct("homes", "List of other players' houses and teleportation", "/homes <player> [home]");
        $this->plugin = $plugin;
        $this->setPermission("h.homes");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$this->testPermission($sender)) {
            return;
        }
        if (!$sender instanceof Player) {
            return;
        }
		if (count($args) < 1) {
			$sender->sendMessage("§e*§r » §cReference usage /homes <player> <home> or /homes <player>");
			return;
		}
        $targetPlayerName = $args[0];
        $db = $this->plugin->getDb();
        $stmt = $db->prepare("SELECT * FROM homes WHERE nickname = :nickname");
        $stmt->bindValue(":nickname", $targetPlayerName);
        $result = $stmt->execute();
        $homes = [];
	while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $coordinates = json_decode($row["home"], true);

    if (is_array($coordinates) && count($coordinates) === 3) {
        if (count($args) === 1) {
        $homes[] = $row["namehome"];
        } elseif (count($args) === 2 && $args[1] === $row["namehome"]) {
            $sender->teleport(new Vector3($coordinates[0], $coordinates[1], $coordinates[2]));
            $sender->sendMessage("§e*§r » §fYou were teleported home by §e'{$row["namehome"]}' §fplayer §e'{$targetPlayerName}'§f.");
            return; 
        }
    } else {
        $sender->sendMessage("§e*§r » §cAn error occurred while receiving player data §e'{$targetPlayerName}'§f.");
		}
	}

	if (count($homes) === 0) {
		$sender->sendMessage("§e*§r » §cThe player §e'{$targetPlayerName}' §chas no houses.");
		return;
	}

	if (count($args) === 1) {
		$sender->sendMessage("§e*§r » §fList of player's houses §e'{$targetPlayerName}'§f: §e" . implode(", ", $homes));
	} elseif (count($args) === 2) {
		$sender->sendMessage("§e*§r » §cThe player does not have a §e'{$targetPlayerName}' §cat home with the name §e'{$args[1]}'§f.");
	}
}
}
