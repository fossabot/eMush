<template>
    <div v-if="difficultyConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.name')"
                id="difficultyConfig_name"
                v-model="difficultyConfig.name"
                type="text"
                :errors="errors.name"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.electricArcRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.electricArcRate"
                type="number"
                :errors="errors.electricArcRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.metalPlateRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.metalPlateRate"
                type="number"
                :errors="errors.metalPlateRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.panicCrisisRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.panicCrisisRate"
                type="number"
                :errors="errors.panicCrisisRate"
            />
            <Input
                :label="$t('admin.difficultyConfig.tremorRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.tremorRate"
                type="number"
                :errors="errors.tremorRate"
            />
            <Input
                :label="$t('admin.difficultyConfig.startingFireRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.startingFireRate"
                type="number"
                :errors="errors.startingFireRate"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.equipmentBreakRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.equipmentBreakRate"
                type="number"
                :errors="errors.equipmentBreakRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.doorBreakRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.doorBreakRate"
                type="number"
                :errors="errors.doorBreakRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.equipmentFireBreakRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.equipmentFireBreakRate"
                type="number"
                :errors="errors.equipmentFireBreakRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.propagatingFireRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.propagatingFireRate"
                type="number"
                :errors="errors.propagatingFireRate"
            />
            <Input
                :label="$t('admin.difficultyConfig.cycleDiseaseRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.cycleDiseaseRate"
                type="number"
                :errors="errors.cycleDiseaseRate"
            />
            
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.hullFireDamageRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.hullFireDamageRate"
                type="number"
                :errors="errors.hullFireDamageRate"
            />
            <Input
                :label="$t('admin.difficultyConfig.plantDiseaseRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.plantDiseaseRate"
                type="number"
                :errors="errors.plantDiseaseRate"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.hunterSpawnRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.hunterSpawnRate"
                type="number"
                :errors="errors.hunterSpawnRate"
            />
            <Input
                :label="$t('admin.difficultyConfig.hunterSafeCycles')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.hunterSafeCycles"
                type="text"
                :errors="errors.hunterSafeCycles"
            />
            <Input
                :label="$t('admin.difficultyConfig.startingHuntersTruceCycles')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.startingHuntersTruceCycles"
                type="number"
                :errors="errors.startingHuntersTruceCycles"
            />
        </div>
        <MapManager
            :label="$t('admin.difficultyConfig.firePlayerDamage')"
            :map="difficultyConfig.firePlayerDamage"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addNewFirePlayerDamage"
            @removeIndex="removeFirePlayerDamage"
        />
        <MapManager
            :label="$t('admin.difficultyConfig.fireHullDamage')"
            :map="difficultyConfig.fireHullDamage"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addNewFireHullDamage"
            @removeIndex="removeFireHullDamage"
        />
        <MapManager
            :label="$t('admin.difficultyConfig.electricArcPlayerDamage')"
            :map="difficultyConfig.electricArcPlayerDamage"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addNewElectricArcPlayerDamage"
            @removeIndex="removeElectricArcPlayerDamage"
        />
        <MapManager
            :label="$t('admin.difficultyConfig.tremorPlayerDamage')"
            :map="difficultyConfig.tremorPlayerDamage"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addNewTremorPlayerDamage"
            @removeIndex="removeTremorPlayerDamage"
        />
        <MapManager
            :label="$t('admin.difficultyConfig.metalPlatePlayerDamage')"
            :map="difficultyConfig.metalPlatePlayerDamage"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addNewMetalPlatePlayerDamage"
            @removeIndex="removeMetalPlatePlayerDamage"
        />
        <MapManager
            :label="$t('admin.difficultyConfig.panicCrisisPlayerDamage')"
            :map="difficultyConfig.panicCrisisPlayerDamage"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addNewPanicCrisisPlayerDamage"
            @removeIndex="removePanicCrisisPlayerDamage"
        />
        <MapManager
            :label="$t('admin.difficultyConfig.difficultyModes')"
            :map="difficultyConfig.difficultyModes"
            mapIndexesType="string"
            mapValuesType="number"
            @addTuple="addNewDifficultyModes"
            @removeIndex="removeDifficultyModes"
        />
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { DifficultyConfig } from "@/entities/Config/DifficultyConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import MapManager from "@/components/Utils/MapManager.vue";

interface DifficultyConfigState {
    difficultyConfig: null|DifficultyConfig
    errors: any
}

export default defineComponent({
    name: "DifficultyConfigDetailPage",
    components: {
        Input,
        MapManager
    },
    data: function (): DifficultyConfigState {
        return {
            difficultyConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.difficultyConfig === null) {
                return;
            }
            this.errors = {};
            // @ts-ignore
            GameConfigService.updateDifficultyConfig(this.difficultyConfig)
                .then((res: DifficultyConfig | null) => {
                    this.difficultyConfig = res;
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.data.violations) {
                            this.errors = handleErrors(error.response.data.violations);
                        }
                    } else if (error.request) {
                        // The request was made but no response was received
                        console.error(error.request);
                    } else {
                        // Something happened in setting up the request that triggered an Error
                        console.error('Error', error.message);
                    }
                });
        },
        addNewFirePlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.difficultyConfig && this.difficultyConfig.firePlayerDamage) {
                this.difficultyConfig.firePlayerDamage.set(index, value);
            }
        },
        removeFirePlayerDamage(index: number): void {
            if (this.difficultyConfig && this.difficultyConfig.firePlayerDamage) {
                this.difficultyConfig.firePlayerDamage.delete(index);
            }
        },
        addNewFireHullDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.difficultyConfig && this.difficultyConfig.fireHullDamage) {
                this.difficultyConfig.fireHullDamage.set(index, value);
            }
        },
        removeFireHullDamage(index: number): void {
            if (this.difficultyConfig && this.difficultyConfig.fireHullDamage) {
                this.difficultyConfig.fireHullDamage.delete(index);
            }
        },
        addNewElectricArcPlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.difficultyConfig && this.difficultyConfig.electricArcPlayerDamage) {
                this.difficultyConfig.electricArcPlayerDamage.set(index, value);
            }
        },
        removeElectricArcPlayerDamage(index: number): void {
            if (this.difficultyConfig && this.difficultyConfig.electricArcPlayerDamage) {
                this.difficultyConfig.electricArcPlayerDamage.delete(index);
            }
        },
        addNewTremorPlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.difficultyConfig && this.difficultyConfig.tremorPlayerDamage) {
                this.difficultyConfig.tremorPlayerDamage.set(index, value);
            }
        },
        removeTremorPlayerDamage(index: number): void {
            if (this.difficultyConfig && this.difficultyConfig.tremorPlayerDamage) {
                this.difficultyConfig.tremorPlayerDamage.delete(index);
            }
        },
        addNewMetalPlatePlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.difficultyConfig && this.difficultyConfig.metalPlatePlayerDamage) {
                this.difficultyConfig.metalPlatePlayerDamage.set(index, value);
            }
        },
        removeMetalPlatePlayerDamage(index: number): void {
            if (this.difficultyConfig && this.difficultyConfig.metalPlatePlayerDamage) {
                this.difficultyConfig.metalPlatePlayerDamage.delete(index);
            }
        },
        addNewPanicCrisisPlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.difficultyConfig && this.difficultyConfig.panicCrisisPlayerDamage) {
                this.difficultyConfig.panicCrisisPlayerDamage.set(index, value);
            }
        },
        removePanicCrisisPlayerDamage(index: number): void {
            if (this.difficultyConfig && this.difficultyConfig.panicCrisisPlayerDamage) {
                this.difficultyConfig.panicCrisisPlayerDamage.delete(index);
            }
        },
        addNewDifficultyModes(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.difficultyConfig && this.difficultyConfig.difficultyModes) {
                this.difficultyConfig.difficultyModes.set(index, value);
            }
        },
        removeDifficultyModes(index: string): void {
            if (this.difficultyConfig && this.difficultyConfig.difficultyModes) {
                this.difficultyConfig.difficultyModes.delete(index);
            }
        }
    },
    beforeMount() {
        const difficultyConfigId = Number(this.$route.params.difficultyConfigId);
        GameConfigService.loadDifficultyConfig(difficultyConfigId).then((res: DifficultyConfig | null) => {
            this.difficultyConfig = res;
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
