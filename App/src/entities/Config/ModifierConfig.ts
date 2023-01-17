import { ModifierActivationRequirement } from "@/entities/Config/ModifierActivationRequirement";

export class ModifierConfig {
    public iri: string|null;
    public id: number|null;
    public name: string|null;
    public modifierName: string|null;
    public delta: number|null;
    public targetVariable: string|null;
    public targetEvent: string|null;
    public modifierHolderClass: string|null;
    public mode: string|null;
    public modifierActivationRequirements:ModifierActivationRequirement[]|null;


    constructor() {
        this.iri = null;
        this.id = null;
        this.name = null;
        this.modifierName = null;
        this.delta = null;
        this.targetVariable = null;
        this.targetEvent = null;
        this.modifierHolderClass = null;
        this.mode = null;
        this.modifierActivationRequirements = null;
    }
    load(object:any) : ModifierConfig {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.name = object.name;
            this.modifierName = object.modifierName;
            this.delta = object.delta;
            this.targetVariable = object.targetVariable;
            this.targetEvent = object.targetEvent;
            this.modifierHolderClass = object.modifierHolderClass;
            this.mode = object.mode;
        }
        return this;
    }
    jsonEncode() : any {
        const modifierActivationRequirements : string[] = [];
        this.modifierActivationRequirements?.forEach(modifierActivationRequirement => (typeof modifierActivationRequirement.iri === 'string' ? modifierActivationRequirements.push(modifierActivationRequirement.iri) : null));
        return {
            'id': this.id,
            'name': this.name,
            'modifierName': this.modifierName,
            'delta': this.delta,
            'targetVariable': this.targetVariable,
            'targetEvent': this.targetEvent,
            'modifierHolderClass': this.modifierHolderClass,
            'mode': this.mode,
            'modifierActivationRequirements': modifierActivationRequirements
        };
    }
    decode(jsonString : string): ModifierConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
