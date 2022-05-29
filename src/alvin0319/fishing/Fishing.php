<?php
declare(strict_types=1);

namespace alvin0319\fishing;

use alvin0319\fishing\entity\FishingHook;
use alvin0319\fishing\item\FishingRod;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

class Fishing extends PluginBase{
	use SingletonTrait;

	/** @var FishingHook[] */
	protected array $entities = [];

	protected function onLoad() : void{
		self::$instance = $this;
	}

	protected function onEnable() : void{
		EntityFactory::getInstance()->register(FishingHook::class, function(World $world, CompoundTag $nbt) : FishingHook{
			return new FishingHook(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ["FishingHook", "minecraft:fishing_hook"], EntityLegacyIds::FISHING_HOOK);
		ItemFactory::getInstance()->register(new FishingRod(), true);
		CreativeInventory::getInstance()->add(new FishingRod());
	}

	public function addFishingEntity(Player $player, FishingHook $entity){
		$this->entities[$player->getName()] = $entity;
	}

	public function getFishingEntity(Player $player) : ?FishingHook{
		return $this->entities[$player->getName()] ?? null;
	}

	public function removeFishingEntity(Player $player){
		unset($this->entities[$player->getName()]);
	}

	public function onDisable() : void{
		foreach($this->entities as $fishingHook){
			if(!$fishingHook->isClosed()){
				$fishingHook->close();
			}
		}
	}
}