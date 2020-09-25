import {Player} from '../models/player.model';
import {Room} from '../models/room.model';
import {Daedalus} from '../models/daedalus.model';

export default async (forceInit = false) => {
    if (forceInit) {
        Player.drop();
        Room.drop();
        Daedalus.drop();
    }

    Daedalus.hasMany(Player, {
        foreignKey: 'player_id',
        as: 'players',
    });

    Daedalus.hasMany(Room, {
        foreignKey: 'daedalus_id',
        as: 'rooms',
    });

    Room.belongsTo(Daedalus, {
        foreignKey: 'daedalus_id',
        as: 'daedalus',
    });

    Room.hasMany(Player, {
        foreignKey: 'room_id',
        as: 'players',
    });

    Player.belongsTo(Daedalus, {
        foreignKey: 'daedalus_id',
        as: 'daedalus',
    });

    Player.belongsTo(Room, {
        foreignKey: 'room_id',
        as: 'room',
    });

    await Daedalus.sync({force: forceInit}).then(() =>
        console.log('Daedalus table created')
    );
    await Room.sync({force: forceInit}).then(() =>
        console.log('Room table created')
    );
    await Player.sync({force: forceInit})
        .then(() => console.log('Player table created'))
        .catch(err => console.error(err));
};
