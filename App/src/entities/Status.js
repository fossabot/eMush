export class Status {
    constructor() {
        this.id = null;
        this.key = null;
        this.name = null;
        this.charge = null;
        this.description = null;
    }

    load = function(object) {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.key = object.key;
            this.name = object.name;
            this.charge = object.charge;
            this.description = object.description;
        }
        return this;
    }
    jsonEncode = function() {
        return JSON.stringify(this);
    }
    decode = function(jsonString) {
        if (jsonString) {
            let object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}
