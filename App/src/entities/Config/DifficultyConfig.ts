import { GameConfig } from "@/entities/Config/GameConfig";

export class DifficultyConfig {
    public iri: string|null;
    public id: number|null;
    public gameConfig: GameConfig|null;
    public equipmentBreakRate: number|null;
    public doorBreakRate: number|null;
    public equipmentFireBreakRate: number|null;
    public startingFireRate: number|null;
    public propagatingFireRate: number|null;
    public hullFireDamageRate: number|null;
    public tremorRate: number|null;
    public electricArcRate: number|null;
    public metalPlateRate: number|null;
    public panicCrisisRate: number|null;
    public firePlayerDamage: Array<any>|null;
    public fireHullDamage: Array<any>|null;
    public electricArcPlayerDamage: Array<any>|null;
    public tremorPlayerDamage: Array<any>|null;
    public metalPlatePlayerDamage: Array<any>|null;
    public panicCrisisPlayerDamage: Array<any>|null;
    public plantDiseaseRate: number|null;
    public cycleDiseaseRate: number|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.gameConfig = null;
        this.equipmentBreakRate = null;
        this.doorBreakRate = null;
        this.equipmentFireBreakRate = null;
        this.startingFireRate = null;
        this.propagatingFireRate = null;
        this.hullFireDamageRate = null;
        this.tremorRate = null;
        this.electricArcRate = null;
        this.metalPlateRate = null;
        this.panicCrisisRate = null;
        this.firePlayerDamage = [];
        this.fireHullDamage = [];
        this.electricArcPlayerDamage = [];
        this.tremorPlayerDamage = [];
        this.metalPlatePlayerDamage = [];
        this.panicCrisisPlayerDamage = [];
        this.plantDiseaseRate = null;
        this.cycleDiseaseRate = null;
    }
    load(object:any) : DifficultyConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.equipmentBreakRate = object.equipmentBreakRate;
            this.doorBreakRate = object.doorBreakRate;
            this.equipmentFireBreakRate = object.equipmentFireBreakRate;
            this.startingFireRate = object.startingFireRate;
            this.propagatingFireRate = object.propagatingFireRate;
            this.hullFireDamageRate = object.hullFireDamageRate;
            this.tremorRate = object.tremorRate;
            this.electricArcRate = object.electricArcRate;
            this.metalPlateRate = object.metalPlateRate;
            this.panicCrisisRate = object.panicCrisisRate;
            this.firePlayerDamage = object.firePlayerDamage;
            this.fireHullDamage = object.fireHullDamage;
            this.electricArcPlayerDamage = object.electricArcPlayerDamage;
            this.tremorPlayerDamage = object.tremorPlayerDamage;
            this.metalPlatePlayerDamage = object.metalPlatePlayerDamage;
            this.panicCrisisPlayerDamage = object.panicCrisisPlayerDamage;
            this.plantDiseaseRate = object.plantDiseaseRate;
            this.cycleDiseaseRate = object.cycleDiseaseRate;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'gameConfig': this.gameConfig?.iri,
            'equipmentBreakRate': this.equipmentBreakRate,
            'doorBreakRate': this.doorBreakRate,
            'equipmentFireBreakRate': this.equipmentFireBreakRate,
            'startingFireRate': this.startingFireRate,
            'propagatingFireRate': this.propagatingFireRate,
            'hullFireDamageRate': this.hullFireDamageRate,
            'tremorRate': this.tremorRate,
            'electricArcRate': this.electricArcRate,
            'metalPlateRate': this.metalPlateRate,
            'panicCrisisRate': this.panicCrisisRate,
            'firePlayerDamage': this.firePlayerDamage,
            'fireHullDamage': this.fireHullDamage,
            'electricArcPlayerDamage': this.electricArcPlayerDamage,
            'tremorPlayerDamage': this.tremorPlayerDamage,
            'metalPlatePlayerDamage': this.metalPlatePlayerDamage,
            'panicCrisisPlayerDamage': this.panicCrisisPlayerDamage,
            'plantDiseaseRate': this.plantDiseaseRate,
            'cycleDiseaseRate': this.cycleDiseaseRate
        };
    }
    decode(jsonString : string): DifficultyConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.equipmentBreakRate = object.equipmentBreakRate;
            this.doorBreakRate = object.doorBreakRate;
            this.equipmentFireBreakRate = object.equipmentFireBreakRate;
            this.startingFireRate = object.startingFireRate;
            this.propagatingFireRate = object.propagatingFireRate;
            this.hullFireDamageRate = object.hullFireDamageRate;
            this.tremorRate = object.tremorRate;
            this.electricArcRate = object.electricArcRate;
            this.metalPlateRate = object.metalPlateRate;
            this.panicCrisisRate = object.panicCrisisRate;
            this.firePlayerDamage = object.firePlayerDamage;
            this.fireHullDamage = object.fireHullDamage;
            this.electricArcPlayerDamage = object.electricArcPlayerDamage;
            this.tremorPlayerDamage = object.tremorPlayerDamage;
            this.metalPlatePlayerDamage = object.metalPlatePlayerDamage;
            this.panicCrisisPlayerDamage = object.panicCrisisPlayerDamage;
            this.plantDiseaseRate = object.plantDiseaseRate;
            this.cycleDiseaseRate = object.cycleDiseaseRate;
        }

        return this;
    }
}
