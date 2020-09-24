import {Model, DataTypes} from 'sequelize';
import {database} from '../config/database';

export class Character extends Model {
    private id!: number;

    get getId(): number {
        return this.id;
    }
}

Character.init(
    {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
        name: {
            type: DataTypes.STRING(32),
        },
    },
    {
        tableName: 'character',
        timestamps: false,
        sequelize: database, // this bit is important
    }
);

Character.sync().then(() => console.log('Character table created'));
