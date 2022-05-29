<?php
declare(strict_types=1);

namespace alvin0319\fishing\item;

use alvin0319\fishing\entity\FishingHook;
use alvin0319\fishing\Fishing;
use pocketmine\entity\Location;
use pocketmine\item\Durable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_rand;

class FishingRod extends Durable{
	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemIds::FISHING_ROD, 0));
	}

	public function getMaxDurability() : int{
		return 65;
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : ItemUseResult{
		$hook = Fishing::getInstance()->getFishingEntity($player);
		if($hook instanceof FishingHook){
			$hook->onCatch();
			$this->applyDamage(mt_rand(1, 2));
		}else{
			$location = $player->getLocation();
			$fishingHook = new FishingHook(Location::fromObject(
				$player->getEyePos(),
				$location->getWorld(),
				($location->yaw > 180 ? 360 : 0) - $location->yaw,
				-$location->pitch
			), $player);
			$fishingHook->setMotion($player->getDirectionVector());
			$fishingHook->spawnToAll();
			Fishing::getInstance()->addFishingEntity($player, $fishingHook);
		}
		return ItemUseResult::SUCCESS();
	}
}