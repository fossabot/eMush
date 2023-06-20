import { Character } from "@/entities/Character";

export class Message {
    public id : number|null;
    public message : string|null;
    public character : Character;
    public children : Array<Message>;
    public date : string|null;
    public hidden = false;
    public nbChildrenToDisplay = 2;

    constructor() {
        this.id = null;
        this.message = null;
        this.character = new Character();
        this.children = [];
        this.date = null;
    }

    load(object: any): Message {
        if (typeof object !== "undefined") {
            this.id = object.id;
            this.message = object.message;
            this.character = this.character.load(object.character);
            this.children = [];
            object.child.forEach((childMessageData: any) => {
                const childMessage = (new Message()).load(childMessageData);
                this.children.push(childMessage);
            });
            this.hideFirstChildren();
            this.date = object.date;
        }
        return this;
    }
    jsonEncode(): string {
        return JSON.stringify(this);
    }
    decode(jsonString: string): Message {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
    getHiddenChildrenCount(): number {
        return this.children.length - this.nbChildrenToDisplay;
    }
    getChildrenToToggle(): Array<Message> {
        return this.children.slice(0, -this.nbChildrenToDisplay);
    }
    hasChildrenToDisplay(): boolean {
        return this.children.length > this.nbChildrenToDisplay;
    }
    isFirstChildHidden(): boolean {
        return this.children[0].hidden;
    }
    private hideFirstChildren(): void {
        this.children.slice(0, -this.nbChildrenToDisplay).forEach(child => {
            child.hidden = true;
        });
    }
}
