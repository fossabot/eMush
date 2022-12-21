import { GameConfig } from "@/entities/Config/GameConfig";

export class PlaceConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public type: string|null;
    public doors: Array<any>|null;
    public items: Array<any>|null;
    public equipments: Array<any>|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.type = null;
        this.doors = [];
        this.items = [];
        this.equipments = [];
    }
    load(object:any) : PlaceConfig {
        if (typeof object !== "undefined") {
            this.iri = object["@id"];
            this.id = object.id;
            this.name = object.name;
            this.type = object.type;
            this.doors = object.doors;
            this.items = object.items;
            this.equipments = object.equipments;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'id': this.id,
            'name': this.name,
            'type': this.type,
            'doors': this.doors,
            'items': this.items,
            'equipments': this.equipments
        };
    }
    decode(jsonString : string): PlaceConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.iri = object.iri;
            this.id = object.id;
            this.name = object.name;
            this.type = object.type;
            this.doors = object.doors;
            this.items = object.items;
            this.equipments = object.equipments;
        }

        return this;
    }
}
