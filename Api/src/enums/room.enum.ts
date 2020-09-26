export enum RoomEnum {
    BRIDGE = 'bridge',
    ALPHA_BAY = 'alpha_bay',
    BRAVO_BAY = 'bravo_bay',
    ALPHA_BAY_2 = 'alpha_bay_2',
    NEXUS = 'nexus',
    MEDLAB = 'medlab',
    LABORATORY = 'laboratory',
    REFECTORY = 'refectory',
    HYDROPONIC_GARDEN = 'hydroponic_garden',
    ENGINE_ROOM = 'engine_room',
    FRONT_ALPHA_TURRET = 'front_alpha_turret',
    CENTRE_ALPHA_TURRET = 'centre_alpha_turret',
    REAR_ALPHA_TURRET = 'rear_alpha_turret',
    FRONT_BRAVO_TURRET = 'front_bravo_turret',
    CENTRE_BRAVO_TURRET = 'centre_bravo_turret',
    REAR_BRAVO_TURRET = 'rear_bravo_turret',
    PATROLLER_16 = 'patroller16',
    PATROLLER_17 = 'patroller17',
    PATROLLER_18 = 'patroller18',
    PATROLLER_19 = 'patroller19',
    PATROLLER_20 = 'patroller20',
    PATROLLER_21 = 'patroller21',
    PATROLLER_22 = 'patroller22',
    PATROLLER_PASIPHAE = 'patroller_pasiphae',
    FRONT_CORRIDOR = 'front_corridor',
    CENTRAL_CORRIDOR = 'central_corridor',
    REAR_CORRIDOR = 'rear_corridor',
    PLANET = 'planet',
    ICARUS_BAY = 'icarus_bay',
    ALPHA_DORM = 'alpha_dorm',
    BRAVO_DORM = 'bravo_dorm',
    FRONT_STORAGE = 'front_storage',
    CENTER_ALPHA_STORAGE = 'center_alpha_storage',
    REAR_ALPHA_STORAGE = 'rear_alpha_storage',
    CENTER_BRAVO_STORAGE = 'center_bravo_storage',
    REAR_BRAVO_STORAGE = 'rear_bravo_storage',
    SPACE = 'space',
    GREAT_BEYOND = 'great_beyond',
}

export function getAllCharacter(): string[] {
    return [
        RoomEnum.BRIDGE,
        RoomEnum.ALPHA_BAY,
        RoomEnum.BRAVO_BAY,
        RoomEnum.ALPHA_BAY_2,
        RoomEnum.NEXUS,
        RoomEnum.MEDLAB,
        RoomEnum.LABORATORY,
        RoomEnum.REFECTORY,
        RoomEnum.HYDROPONIC_GARDEN,
        RoomEnum.ENGINE_ROOM,
        RoomEnum.FRONT_ALPHA_TURRET,
        RoomEnum.CENTRE_ALPHA_TURRET,
        RoomEnum.REAR_ALPHA_TURRET,
        RoomEnum.FRONT_BRAVO_TURRET,
        RoomEnum.CENTRE_BRAVO_TURRET,
        RoomEnum.REAR_BRAVO_TURRET,
        RoomEnum.PATROLLER_16,
        RoomEnum.PATROLLER_17,
        RoomEnum.PATROLLER_18,
        RoomEnum.PATROLLER_19,
        RoomEnum.PATROLLER_20,
        RoomEnum.PATROLLER_21,
        RoomEnum.PATROLLER_22,
        RoomEnum.PATROLLER_PASIPHAE,
        RoomEnum.FRONT_CORRIDOR,
        RoomEnum.CENTRAL_CORRIDOR,
        RoomEnum.REAR_CORRIDOR,
        RoomEnum.PLANET,
        RoomEnum.ICARUS_BAY,
        RoomEnum.ALPHA_DORM,
        RoomEnum.BRAVO_DORM,
        RoomEnum.FRONT_STORAGE,
        RoomEnum.CENTER_ALPHA_STORAGE,
        RoomEnum.REAR_ALPHA_STORAGE,
        RoomEnum.CENTER_BRAVO_STORAGE,
        RoomEnum.REAR_BRAVO_STORAGE,
        RoomEnum.SPACE,
        RoomEnum.GREAT_BEYOND,
    ];
}
