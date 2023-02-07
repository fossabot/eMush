<?php

namespace Mush\Tests\functional\Disease\Service\ConfigData;

use App\Tests\FunctionalTester;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Service\ConfigData\DiseaseCauseConfigData;
use Mush\Disease\Service\ConfigData\DiseaseCauseConfigDataLoader;

class DiseaseCauseConfigDataLoaderCest
{
    private DiseaseCauseConfigDataLoader $diseaseCauseConfigDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->diseaseCauseConfigDataLoader = $I->grabService(DiseaseCauseConfigDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->diseaseCauseConfigDataLoader->loadConfigsData();

        foreach (DiseaseCauseConfigData::$dataArray as $diseaseCauseConfigData) {
            $diseaseCauseConfigData = $this->dropArrayFields($diseaseCauseConfigData);

            $I->seeInRepository(DiseaseCauseConfig::class, $diseaseCauseConfigData);
        }

        $I->seeNumRecords(count(DiseaseCauseConfigData::$dataArray), DiseaseCauseConfig::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = DiseaseCauseConfigData::$dataArray[0];
        $config = $this->dropArrayFields($config);

        $this->diseaseCauseConfigDataLoader->loadConfigsData();

        $I->seeNumRecords(1, DiseaseCauseConfig::class, $config);
    }

    // can't compare arrays because of Codeception bug
    private function dropArrayFields(array $configArray)
    {
        unset($configArray['diseases']);

        return $configArray;
    }
}
