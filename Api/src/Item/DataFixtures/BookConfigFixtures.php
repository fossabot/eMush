<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Item\Entity\Item;
use Mush\Item\Entity\Items\Book;
use Mush\Item\Entity\Items\Documents;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\DocumentContentEnum;

class BookConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        //First Mage Books
        $skillsArray = [SkillEnum::ASTROPHYSICIST,
                                        SkillEnum::BIOLOGIST,
                                        SkillEnum::BOTANIST,
                                        SkillEnum::DIPLOMAT,
                                        SkillEnum::FIREFIGHTER,
                                        SkillEnum::CHEF,
                                        SkillEnum::IT_EXPERT,
                                        SkillEnum::LOGISTICS_EXPERT,
                                        SkillEnum::MEDIC,
                                        SkillEnum::PILOT,
                                        SkillEnum::RADIO_EXPERT,
                                        SkillEnum::ROBOTICS_EXPERT,
                                        SkillEnum::SHOOTER,
                                        SkillEnum::SHRINK,
                                        SkillEnum::SPRINTER,
                                        SkillEnum::TECHNICIAN,
                                        ];

        foreach ($skillsArray as $skillName) {
              $apprentonType = new Book();
              $apprentonType
                  ->setSkill($skillName)
              ;

              $apprenton = new Item();
              $apprenton
                  ->setGameConfig($gameConfig)
                  ->setName(ItemEnum::APPRENTON . '_' . $skillName)
                  ->setIsHeavy(false)
                  ->setIsTakeable(true)
                  ->setIsDropable(true)
                  ->setIsStackable(true)
                  ->setIsHideable(true)
                  ->setIsFireDestroyable(true)
                  ->setIsFireBreakable(false)
                  ->setTypes(new ArrayCollection([$apprentonType]))
              ;

              $manager->persist($apprentonType);
              $manager->persist($apprenton);
        }

     //Then Documents
    $documentType = new Document();
    $documentType
           ->setIsTranslated(true)
           ;
     
     $document = new Item();
     $document
            ->setGameConfig($gameConfig)
            ->setName(ItemEnum::DOCUMENT)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setActions([ActionEnum::SHRED])
            ->setTypes(new ArrayCollection([$documentType]))
        ;

        $manager->persist($documentType);
        $manager->persist($document);
        
        $commandersManualType = new Document();
	    $commandersManualType
	           ->setIsTranslated(true)
	           ->setIsContent(DocumentContentEnum::COMMANDERS_MANUAL)
	           ;
	     
	     $commandersManual = new Item();
	     $commandersManual
	            ->setGameConfig($gameConfig)
	            ->setName(ItemEnum::COMMANDERS_MANUAL)
	            ->setIsHeavy(false)
	            ->setIsTakeable(true)
	            ->setIsDropable(true)
	            ->setIsStackable(true)
	            ->setIsHideable(true)
	            ->setIsFireDestroyable(true)
	            ->setIsFireBreakable(false)
	            ->setTypes(new ArrayCollection([$commandersManualType]))
	        ;
	
	        $manager->persist($commandersManualType);
	        $manager->persist($commandersManual);
	        
	         $mushResearchType = new Document();
	    $mushResearchType
	           ->setIsTranslated(true)
	           ->setIsContent(DocumentContentEnum::MUSH_RESEARCH_REVIEW)
	           ;
	     
	     $mushResearch = new Item();
	     $mushResearch
	            ->setGameConfig($gameConfig)
	            ->setName(ItemEnum::MUSH_RESEARCH_REVIEW)
	            ->setIsHeavy(false)
	            ->setIsTakeable(true)
	            ->setIsDropable(true)
	            ->setIsStackable(true)
	            ->setIsHideable(true)
	            ->setIsFireDestroyable(true)
	            ->setIsFireBreakable(false)
	            ->setTypes(new ArrayCollection([$mushResearchType]))
	        ;
	
	        $manager->persist($mushResearchType);
	        $manager->persist($mushResearch);
              
              
              $postItType = new Document();
	    $postItType
	           ->setIsTranslated(false)
	           ;
	     
	     $postIt = new Item();
	     $postIt
	            ->setGameConfig($gameConfig)
	            ->setName(ItemEnum::POST_IT)
	            ->setIsHeavy(false)
	            ->setIsTakeable(true)
	            ->setIsDropable(true)
	            ->setIsStackable(true)
	            ->setIsHideable(true)
	            ->setIsFireDestroyable(true)
	            ->setIsFireBreakable(false)
	            ->setActions([ActionEnum::SHRED])
	            ->setTypes(new ArrayCollection([$postItType]))
	        ;
	
	        $manager->persist($postItType);
	        $manager->persist($postIt);


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
