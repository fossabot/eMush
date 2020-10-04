import {
    Column,
    CreateDateColumn,
    Entity,
    ManyToOne,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';
import {Player} from './player.model';
import {Room} from './room.model';
import {ItemTypeEnum} from '../enums/itemType.enum';

@Entity()
export class Item {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public name!: string;
    @Column()
    public type!: ItemTypeEnum;
    @Column('simple-array')
    public statuses: string[] = [];
    @ManyToOne(type => Room, room => room.items)
    public room!: Room;
    @ManyToOne(type => Player, player => player.items)
    public player!: Player;
    @Column()
    public isDismantable!: boolean;
    @Column()
    public isHeavy!: boolean;
    @ManyToOne(type => Player)
    public personal!: Player;
    @Column()
    public isStackable!: boolean;
    @Column()
    public isHideable!: boolean;
    @Column()
    public isMoveable!: boolean;
    @Column()
    public isFireDestroyable!: boolean;
    @Column()
    public isFireBreakable!: boolean;
    @CreateDateColumn()
    public createdAt!: Date;
    @UpdateDateColumn()
    public updatedAt!: Date;

    isPersonal(): boolean {
        return this.player !== null;
    }
}
