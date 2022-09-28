<?php

namespace Mush\Place\Enum;

class RoomEnum
{
    public const BRIDGE = 'bridge';
    public const ALPHA_BAY = 'alpha_bay';
    public const BRAVO_BAY = 'bravo_bay';
    public const ALPHA_BAY_2 = 'alpha_bay_2';
    public const NEXUS = 'nexus';
    public const MEDLAB = 'medlab';
    public const LABORATORY = 'laboratory';
    public const REFECTORY = 'refectory';
    public const HYDROPONIC_GARDEN = 'hydroponic_garden';
    public const ENGINE_ROOM = 'engine_room';
    public const FRONT_ALPHA_TURRET = 'front_alpha_turret';
    public const CENTRE_ALPHA_TURRET = 'centre_alpha_turret';
    public const REAR_ALPHA_TURRET = 'rear_alpha_turret';
    public const FRONT_BRAVO_TURRET = 'front_bravo_turret';
    public const CENTRE_BRAVO_TURRET = 'centre_bravo_turret';
    public const REAR_BRAVO_TURRET = 'rear_bravo_turret';
    public const FRONT_CORRIDOR = 'front_corridor';
    public const CENTRAL_CORRIDOR = 'central_corridor';
    public const REAR_CORRIDOR = 'rear_corridor';
    public const PLANET = 'planet';
    public const ICARUS_BAY = 'icarus_bay';
    public const ALPHA_DORM = 'alpha_dorm';
    public const BRAVO_DORM = 'bravo_dorm';
    public const FRONT_STORAGE = 'front_storage';
    public const CENTER_ALPHA_STORAGE = 'center_alpha_storage';
    public const REAR_ALPHA_STORAGE = 'rear_alpha_storage';
    public const CENTER_BRAVO_STORAGE = 'center_bravo_storage';
    public const REAR_BRAVO_STORAGE = 'rear_bravo_storage';
    public const SPACE = 'space';

    public static function getAllDaedalusRooms(): array
    {
        return [
            self::BRIDGE,
            self::ALPHA_BAY,
            self::BRAVO_BAY,
            self::ALPHA_BAY_2,
            self::NEXUS,
            self::MEDLAB,
            self::LABORATORY,
            self::REFECTORY,
            self::HYDROPONIC_GARDEN,
            self::ENGINE_ROOM,
            self::FRONT_ALPHA_TURRET,
            self::CENTRE_ALPHA_TURRET,
            self::REAR_ALPHA_TURRET,
            self::FRONT_BRAVO_TURRET,
            self::CENTRE_BRAVO_TURRET,
            self::REAR_BRAVO_TURRET,
            self::FRONT_CORRIDOR,
            self::CENTRAL_CORRIDOR,
            self::REAR_CORRIDOR,
            self::ICARUS_BAY,
            self::ALPHA_DORM,
            self::BRAVO_DORM,
            self::FRONT_STORAGE,
            self::CENTER_ALPHA_STORAGE,
            self::REAR_ALPHA_STORAGE,
            self::CENTER_BRAVO_STORAGE,
            self::REAR_BRAVO_STORAGE,
        ];
    }

    public static function getStorages(): array
    {
        return [
            self::FRONT_STORAGE,
            self::CENTER_ALPHA_STORAGE,
            self::REAR_ALPHA_STORAGE,
            self::CENTER_BRAVO_STORAGE,
            self::REAR_BRAVO_STORAGE,
        ];
    }
}
