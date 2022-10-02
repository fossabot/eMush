import { GameConfig } from "@/entities/Config/GameConfig";

export class DiseaseCauseConfig {
    public iri: string|null;
    public id: number|null;
    public gameConfig: GameConfig|null;
    public causeName: string|null;
    public diseases: Array<any>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.gameConfig = null;
        this.causeName = null;
        this.diseases = [];
    }
    load(object:any) : DiseaseCauseConfig {
        if (typeof object !== "undefined") {
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.causeName = object.causeName;
            this.diseases = object.diseases;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'gameConfig': this.gameConfig?.iri,
            'causeName': this.causeName,
            'diseases': this.diseases
        };
    }
    decode(jsonString : string): DiseaseCauseConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.gameConfig = object.gameConfig;
            this.causeName = object.causeName;
            this.diseases = object.diseases;
        }

        return this;
    }
}
