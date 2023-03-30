<?php

namespace Mush\Place\ConfigData;

use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;

/** @codeCoverageIgnore */
class PlaceConfigData
{
    public static $dataArray = [
        [
            'name' => 'bridge_default',
            'placeName' => 'bridge',
            'type' => 'room',
            'doors' => ['bridge_front_alpha_turret', 'bridge_front_bravo_turret', 'front_corridor_bridge'],
            'items' => [],
            'equipments' => ['communication_center', 'astro_terminal', 'command_terminal', 'tabulatrix'],
        ],
        [
            'name' => 'alpha_bay_default',
            'placeName' => 'alpha_bay',
            'type' => 'room',
            'doors' => ['alpha_bay_alpha_dorm', 'alpha_bay_center_alpha_storage', 'alpha_bay_central_alpha_turret', 'alpha_bay_central_corridor', 'alpha_bay_alpha_bay_2'],
            'items' => [],
            'equipments' => ['patrol_ship', 'patrol_ship', 'patrol_ship'],
        ],
        [
            'name' => 'bravo_bay_default',
            'placeName' => 'bravo_bay',
            'type' => 'room',
            'doors' => ['bravo_bay_bravo_dorm', 'bravo_bay_center_bravo_storage', 'bravo_bay_central_bravo_turret', 'bravo_bay_central_corridor', 'bravo_bay_rear_corridor'],
            'items' => [],
            'equipments' => ['patrol_ship', 'patrol_ship', 'patrol_ship'],
        ],
        [
            'name' => 'alpha_bay_2_default',
            'placeName' => 'alpha_bay_2',
            'type' => 'room',
            'doors' => ['alpha_bay_alpha_bay_2', 'engine_room_bay_alpha_2', 'rear_corridor_bay_alpha_2', 'rear_alpha_turret_bay_alpha_2'],
            'items' => ['oscilloscope_blueprint', 'metal_scraps', 'metal_scraps', 'metal_scraps', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule', 'space_capsule'],
            'equipments' => ['patrol_ship', 'pasiphae', 'dynarcade', 'jukebox'],
        ],
        [
            'name' => 'nexus_default',
            'placeName' => 'nexus',
            'type' => 'room',
            'doors' => ['rear_corridor_nexus'],
            'items' => [],
            'equipments' => ['neron_core', 'bios_terminal', 'calculator'],
        ],
        [
            'name' => 'medlab_default',
            'placeName' => 'medlab',
            'type' => 'room',
            'doors' => ['medlab_central_bravo_turret', 'medlab_laboratory', 'front_corridor_medlab'],
            'items' => ['bandage', 'bandage', 'bandage', 'jar_of_alien_oil', 'spore_sucker'],
            'equipments' => ['surgery_plot', 'narcotic_distiller', 'medlab_bed'],
        ],
        [
            'name' => 'laboratory_default',
            'placeName' => 'laboratory',
            'type' => 'room',
            'doors' => ['front_corridor_laboratory', 'medlab_laboratory'],
            'items' => ['apprenton_pilot', 'sniper_helmet_blueprint', 'echolocator_blueprint', 'alien_bottle_opener', 'rolling_boulder', 'metal_scraps', 'plastic_scraps', 'banana', 'creepnut', 'bottine', 'fragilane', 'filandra', 'alien_holographic_tv'],
            'equipments' => ['gravity_simulator', 'research_laboratory', 'cryo_module', 'mycoscan'],
        ],
        [
            'name' => 'refectory_default',
            'placeName' => 'refectory',
            'type' => 'room',
            'doors' => ['refectory_central_corridor'],
            'items' => ['mad_kube', 'microwave', 'superfreezer', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration', 'standard_ration'],
            'equipments' => ['kitchen', 'coffee_machine'],
        ],
        [
            'name' => 'hydroponic_garden_default',
            'placeName' => 'hydroponic_garden',
            'type' => 'room',
            'doors' => ['front_corridor_garden', 'front_storage_garden'],
            'items' => ['hydropot', 'banana_tree', 'banana_tree'],
            'equipments' => [],
        ],
        [
            'name' => 'engine_room_default',
            'placeName' => 'engine_room',
            'type' => 'room',
            'doors' => ['engine_room_bay_alpha_2', 'engine_room_bay_icarus', 'engine_room_rear_alpha_storage', 'engine_room_rear_bravo_storage', 'engine_room_rear_alpha_turret', 'engine_room_rear_bravo_turret'],
            'items' => [],
            'equipments' => ['antenna', 'planet_scanner', 'pilgred', 'reactor_lateral', 'reactor_lateral', 'emergency_reactor', 'combustion_chamber'],
        ],
        [
            'name' => 'front_alpha_turret_default',
            'placeName' => 'front_alpha_turret',
            'type' => 'room',
            'doors' => ['bridge_front_alpha_turret', 'front_corridor_front_alpha_turret'],
            'items' => [],
            'equipments' => ['turret_command'],
        ],
        [
            'name' => 'centre_alpha_turret_default',
            'placeName' => 'centre_alpha_turret',
            'type' => 'room',
            'doors' => ['front_storage_central_alpha_turret', 'alpha_bay_central_alpha_turret'],
            'items' => [],
            'equipments' => ['turret_command'],
        ],
        [
            'name' => 'rear_alpha_turret_default',
            'placeName' => 'rear_alpha_turret',
            'type' => 'room',
            'doors' => ['rear_alpha_turret_bay_alpha_2', 'engine_room_rear_alpha_turret'],
            'items' => [],
            'equipments' => ['turret_command'],
        ],
        [
            'name' => 'front_bravo_turret_default',
            'placeName' => 'front_bravo_turret',
            'type' => 'room',
            'doors' => ['bridge_front_bravo_turret', 'front_corridor_front_beta_turret'],
            'items' => [],
            'equipments' => ['turret_command'],
        ],
        [
            'name' => 'centre_bravo_turret_default',
            'placeName' => 'centre_bravo_turret',
            'type' => 'room',
            'doors' => ['medlab_central_bravo_turret', 'bravo_bay_central_bravo_turret'],
            'items' => [],
            'equipments' => ['turret_command'],
        ],
        [
            'name' => 'rear_bravo_turret_default',
            'placeName' => 'rear_bravo_turret',
            'type' => 'room',
            'doors' => ['rear_bravo_turret_bay_icarus', 'engine_room_rear_bravo_turret'],
            'items' => [],
            'equipments' => ['turret_command'],
        ],
        [
            'name' => 'front_corridor_default',
            'placeName' => 'front_corridor',
            'type' => 'room',
            'doors' => ['front_corridor_front_alpha_turret', 'front_corridor_front_beta_turret', 'front_corridor_bridge', 'front_corridor_garden', 'front_corridor_front_storage', 'front_corridor_laboratory', 'front_corridor_medlab', 'front_corridor_central_corridor'],
            'items' => [],
            'equipments' => [],
        ],
        [
            'name' => 'central_corridor_default',
            'placeName' => 'central_corridor',
            'type' => 'room',
            'doors' => ['refectory_central_corridor', 'front_corridor_central_corridor', 'alpha_bay_central_corridor', 'bravo_bay_central_corridor'],
            'items' => [],
            'equipments' => [],
        ],
        [
            'name' => 'rear_corridor_default',
            'placeName' => 'rear_corridor',
            'type' => 'room',
            'doors' => ['rear_corridor_nexus', 'rear_corridor_bay_alpha_2', 'rear_corridor_alpha_dorm', 'rear_corridor_bravo_dorm', 'rear_corridor_bay_icarus', 'rear_corridor_rear_alpha_storage', 'rear_corridor_rear_bravo_storage', 'bravo_bay_rear_corridor'],
            'items' => [],
            'equipments' => [],
        ],
        [
            'name' => 'icarus_bay_default',
            'placeName' => 'icarus_bay',
            'type' => 'room',
            'doors' => ['rear_corridor_bay_icarus', 'rear_bravo_turret_bay_icarus', 'engine_room_bay_icarus'],
            'items' => [],
            'equipments' => ['icarus'],
        ],
        [
            'name' => 'alpha_dorm_default',
            'placeName' => 'alpha_dorm',
            'type' => 'room',
            'doors' => ['alpha_bay_alpha_dorm', 'rear_corridor_alpha_dorm'],
            'items' => [],
            'equipments' => ['bed', 'bed', 'bed', 'shower'],
        ],
        [
            'name' => 'bravo_dorm_default',
            'placeName' => 'bravo_dorm',
            'type' => 'room',
            'doors' => ['bravo_bay_bravo_dorm', 'rear_corridor_bravo_dorm'],
            'items' => [],
            'equipments' => ['bed', 'bed', 'bed', 'thalasso'],
        ],
        [
            'name' => 'front_storage_default',
            'placeName' => 'front_storage',
            'type' => 'room',
            'doors' => ['front_storage_central_alpha_turret', 'front_storage_garden', 'front_corridor_front_storage'],
            'items' => [],
            'equipments' => [],
        ],
        [
            'name' => 'center_alpha_storage_default',
            'placeName' => 'center_alpha_storage',
            'type' => 'room',
            'doors' => ['alpha_bay_center_alpha_storage'],
            'items' => [],
            'equipments' => ['oxygen_tank'],
        ],
        [
            'name' => 'center_bravo_storage_default',
            'placeName' => 'center_bravo_storage',
            'type' => 'room',
            'doors' => ['bravo_bay_center_bravo_storage'],
            'items' => [],
            'equipments' => ['oxygen_tank'],
        ],
        [
            'name' => 'rear_alpha_storage_default',
            'placeName' => 'rear_alpha_storage',
            'type' => 'room',
            'doors' => ['rear_corridor_rear_alpha_storage', 'engine_room_rear_alpha_storage'],
            'items' => [],
            'equipments' => ['fuel_tank'],
        ],
        [
            'name' => 'rear_bravo_storage_default',
            'placeName' => 'rear_bravo_storage',
            'type' => 'room',
            'doors' => ['rear_corridor_rear_bravo_storage', 'engine_room_rear_bravo_storage'],
            'items' => [],
            'equipments' => ['fuel_tank'],
        ],
        [
            'name' => RoomEnum::SPACE . '_default',
            'placeName' => RoomEnum::SPACE,
            'type' => PlaceTypeEnum::SPACE,
            'doors' => [],
            'items' => [],
            'equipments' => [],
        ],
    ];
}
