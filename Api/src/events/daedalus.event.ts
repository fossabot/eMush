import {Daedalus} from "../models/daedalus.model";
import eventManager from "../config/event.manager";
import GameConfig from '../config/game.config';
import {Player} from "../models/player.model";
import {PlayerEvent} from "./player.event";

export enum DaedalusEvent {
    DAEDALUS_START = 'daedalus_start',
    DAEDALUS_END = 'daedalus_end',
    DAEDALUS_NEW_CYCLE = 'daedalus_new_cycle',
    DAEDALUS_NEW_DAY = 'daedalus_new_DAY',
}

const newCycle = (daedalus: Daedalus) => {
    const nbCycle = 24 / GameConfig.cycleLength;

    if (daedalus.cycle > nbCycle) {
        daedalus.cycle = 1;
    } else {
        daedalus.cycle++;
    }

    daedalus.getPlayersAlive().forEach((player: Player) => eventManager.emit(PlayerEvent.PLAYER_NEW_CYCLE, player))

    daedalus.save();
}

const newDay = (daedalus: Daedalus) => {
    daedalus.day++;

    daedalus.getPlayersAlive().forEach((player: Player) => eventManager.emit(PlayerEvent.PLAYER_NEW_DAY, player))

    daedalus.save();
}

eventManager.on(DaedalusEvent.DAEDALUS_NEW_CYCLE, newCycle);
eventManager.on(DaedalusEvent.DAEDALUS_NEW_DAY, newDay);
