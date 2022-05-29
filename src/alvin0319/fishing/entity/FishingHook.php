<?php
declare(strict_types=1);

namespace alvin0319\fishing\entity;

use alvin0319\fishing\Fishing;
use OnixUtils\OnixUtils;
use pocketmine\color\Color;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\DustParticle;
use function count;

class FishingHook extends Projectile{
	protected int $fishTick;

	protected int $groundTick;

	public function getName() : string{
		return "FishingHook";
	}

	public function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->fishTick = mt_rand(150, 200);
		$this->groundTick = 20 * 5;
		$this->setNameTagAlwaysVisible(true);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		parent::entityBaseTick($tickDiff);

		if(!$this->isAlive() || $this->isClosed() || $this->isFlaggedForDespawn())
			return false;

		$owner = $this->getOwningEntity();
		if(!$owner instanceof Player || $owner->isClosed()){
			$this->flagForDespawn();
			return false;
		}

		$this->setNameTag(TextFormat::GRAY . "§d{$owner->getName()}§f님 낚시중...");
		if($this->fishTick-- > 0){
			if(!$this->isUnderwater()){
				if($this->groundTick-- < 0){
					$this->flagForDespawn();
					OnixUtils::message($owner, "물고기는 땅에서 나오지 않습니다... ^^");
					return false;
				}
			}
			if($this->isUnderwater()){
				$this->motion->y += 0.0001;
			}
		}else{
			if($this->fishTick > -20){
				$this->setNameTag(TextFormat::GRAY . "§d{$owner->getName()}§f님 낚시중...\n" . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "입질이다!");
				//$owner->sendPopup("입질이다!!!!!!");
				$this->getWorld()->addParticle($this->getPosition(), new DustParticle(new Color(255, 0, 0)));
			}else{
				$this->flagForDespawn();
				OnixUtils::message($owner, "물고기를 낚기에 너무 늦었습니다.");
			}
		}
		return true;
	}

	public function destroyCycles() : void{
		parent::destroyCycles();

		$owner = $this->getOwningEntity();
		if($owner instanceof Player){
			Fishing::getInstance()->removeFishingEntity($owner);
		}
	}

	public function onCatch() : void{
		if($this->isFlaggedForDespawn())
			return;

		$this->flagForDespawn();
		$owner = $this->getOwningEntity();
		if(!$owner instanceof Player || $owner->isClosed() || $this->fishTick > 0)
			return;

		/** @var Item[] $items */
		static $items = [];
		if(count($items) === 0){
			$itemFactory = ItemFactory::getInstance();
			$items = [
				$itemFactory->get(ItemIds::RAW_FISH, 0, mt_rand(1, 3)),
				$itemFactory->get(ItemIds::RAW_SALMON, 0, mt_rand(1, 3)),
				$itemFactory->get(461, 0, mt_rand(1, 3)),
				$itemFactory->get(462, 0, 3),
				$itemFactory->get(ItemIds::GLASS_BOTTLE, 0, 1),
				$itemFactory->get(ItemIds::GLASS_BOTTLE, 0, 1),
				$itemFactory->get(ItemIds::GLASS_BOTTLE, 0, 1),
			];
		}

		$item = $items[array_rand($items)];
		$owner->getInventory()->addItem($item);
		OnixUtils::message($owner, "낚시에 성공하여 {$item->getName()}을(를) {$item->getCount()}개 얻었습니다.");
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.25, 0.25);
	}

	public static function getNetworkTypeId() : string{
		return EntityIds::FISHING_HOOK;
	}
}