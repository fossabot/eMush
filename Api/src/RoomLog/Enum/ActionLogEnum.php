<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\ActionOutputEnum;

class ActionLogEnum
{
    public const DISASSEMBLE_SUCCESS = 'disassemble_success';
    public const DISASSEMBLE_FAIL = 'disassemble_fail';
    public const BUILD_SUCCESS = 'build_success';
    public const COFFEE_SUCCESS = 'coffee_success';
    public const COMFORT_SUCCESS = 'comfort_success';
    public const CONSUME_SUCCESS = 'consume_success';
    public const CONSUME_DRUG = 'consume_drug';
    public const COOK_SUCCESS = 'cook_success';
    public const DISPENSE_SUCCESS = 'dispense_success';
    public const DO_THE_THING_SUCCESS = 'do_the_thing_success';
    public const DROP = 'drop';
    public const EXPRESS_COOK_SUCCESS = 'express_cook_success';
    public const EXTINGUISH_SUCCESS = 'extinguish_success';
    public const EXTINGUISH_FAIL = 'extinguish_fail';
    public const EXTRACT_SPORE_SUCCESS = 'extract_spore_success';
    public const FLIRT_SUCCESS = 'flirt_success';
    public const GET_UP = 'get_up';
    public const HEAL_SUCCESS = 'heal_success';
    public const HIDE_SUCCESS = 'hide_success';
    public const HIT_SUCCESS = 'hit_success';
    public const HIT_FAIL = 'hit_fail';
    public const HYBRIDIZE_SUCCESS = 'hybridize_success';
    public const HYBRIDIZE_FAIL = 'transplant_fail';
    public const HYPERFREEZE_SUCCESS = 'hyperfreeze_success';
    public const PHAGOCYTE_SUCCESS = 'phagocyte_success';
    public const INFECT_SUCCESS = 'infect_success';
    public const INSERT_FUEL = 'insert_fuel';
    public const INSERT_OXYGEN = 'insert_oxygen';
    public const RETRIEVE_OXYGEN = 'retrieve_oxygen';
    public const RETRIEVE_FUEL = 'retrieve_fuel';
    public const LIE_DOWN = 'lie_down';
    public const EXIT_ROOM = 'exit_room';
    public const ENTER_ROOM = 'enter_room';
    public const READ_BOOK = 'read_book';
    public const READ_DOCUMENT = 'read_document';
    public const REPAIR_SUCCESS = 'repair_success';
    public const REPAIR_FAIL = 'repair_fail';
    public const SABOTAGE_SUCCESS = 'sabotage_success';
    public const SABOTAGE_FAIL = 'sabotage_fail';
    public const SEARCH_SUCCESS = 'search_success';
    public const SEARCH_FAIL = 'search_fail';
    public const SHRED_SUCCESS = 'shred_success';
    public const SHOWER_HUMAN = 'shower_human';
    public const SHOWER_MUSH = 'shower_mush';
    public const WASH_IN_SINK_HUMAN = 'wash_in_sink_human';
    public const WASH_IN_SINK_MUSH = 'wash_in_sink_mush';
    public const STRENGTHEN_SUCCESS = 'strengthen_success';
    public const SPREAD_FIRE_SUCCESS = 'spread_fire_success';
    public const TAKE = 'take';
    public const TRANSPLANT_SUCCESS = 'transplant_success';
    public const TREAT_PLANT_SUCCESS = 'treat_plant_success';
    public const TRY_KUBE = 'try_kube';
    public const ULTRAHEAL_SUCCESS = 'ultraheal_success';
    public const SELF_HEAL = 'self_heal';
    public const WATER_PLANT_SUCCESS = 'water_plant_success';
    public const WRITE_SUCCESS = 'write_success';
    public const OPEN_SUCCESS = 'open_success';
    public const INSTALL_CAMERA = 'install_camera';
    public const REMOVE_CAMERA = 'remove_camera';
    public const CHECK_SPORE_LEVEL = 'check_spore_level';
    public const REMOVE_SPORE_SUCCESS = 'remove_spore_success';
    public const REMOVE_SPORE_FAIL = 'remove_spore_fail';
    public const PUBLIC_BROADCAST = 'public_broadcast';
    public const MOTIVATIONAL_SPEECH = 'motivational_speech';
    public const BORING_SPEECH = 'boring_speech';
    public const MAKE_SICK = 'make_sick';
    public const FAKE_DISEASE = 'fake_disease';
    public const FAIL_SURGERY = 'fail_surgery';
    public const FAIL_SELF_SURGERY = 'fail_self_surgery';
    public const UPDATE_TALKIE_SUCCESS = 'update_talkie_success';
    public const SCREW_TALKIE_SUCCESS = 'screw_talkie_success';

    public const DEFAULT_FAIL = 'default_fail';

    public const VISIBILITY = 'visibility';
    public const VALUE = 'value';

    public const ACTION_LOGS = [
        ActionEnum::DISASSEMBLE => [
            ActionOutputEnum::SUCCESS => self::DISASSEMBLE_SUCCESS,
            ActionOutputEnum::FAIL => self::DISASSEMBLE_FAIL,
        ],
        ActionEnum::TAKE => [
            ActionOutputEnum::SUCCESS => self::TAKE,
        ],
        ActionEnum::HIDE => [
            ActionOutputEnum::SUCCESS => self::HIDE_SUCCESS,
        ],
        ActionEnum::DROP => [
            ActionOutputEnum::SUCCESS => self::DROP,
        ],
        ActionEnum::REPAIR => [
            ActionOutputEnum::SUCCESS => self::REPAIR_SUCCESS,
            ActionOutputEnum::FAIL => self::REPAIR_FAIL,
        ],
        ActionEnum::SEARCH => [
            ActionOutputEnum::SUCCESS => self::SEARCH_SUCCESS,
            ActionOutputEnum::FAIL => self::SEARCH_FAIL,
        ],
        ActionEnum::EXTRACT_SPORE => [
            ActionOutputEnum::SUCCESS => self::EXTRACT_SPORE_SUCCESS,
        ],
        ActionEnum::INFECT => [
            ActionOutputEnum::SUCCESS => self::INFECT_SUCCESS,
        ],
        ActionEnum::SABOTAGE => [
            ActionOutputEnum::SUCCESS => self::SABOTAGE_SUCCESS,
            ActionOutputEnum::FAIL => self::SABOTAGE_FAIL,
        ],
        ActionEnum::READ_DOCUMENT => [
            ActionOutputEnum::SUCCESS => self::READ_DOCUMENT,
        ],
        ActionEnum::READ_BOOK => [
            ActionOutputEnum::SUCCESS => self::READ_BOOK,
        ],
        ActionEnum::SHRED => [
            ActionOutputEnum::SUCCESS => self::SHRED_SUCCESS,
        ],
        ActionEnum::CONSUME => [
            ActionOutputEnum::SUCCESS => self::CONSUME_SUCCESS,
        ],
        ActionEnum::CONSUME_DRUG => [
            ActionOutputEnum::SUCCESS => self::CONSUME_DRUG,
        ],
        ActionEnum::PHAGOCYTE => [
            ActionOutputEnum::SUCCESS => self::PHAGOCYTE_SUCCESS,
        ],
        ActionEnum::WATER_PLANT => [
            ActionOutputEnum::SUCCESS => self::WATER_PLANT_SUCCESS,
        ],
        ActionEnum::TREAT_PLANT => [
            ActionOutputEnum::SUCCESS => self::TREAT_PLANT_SUCCESS,
        ],
        ActionEnum::TRY_KUBE => [
            ActionOutputEnum::SUCCESS => self::TRY_KUBE,
        ],
        ActionEnum::HYBRIDIZE => [
            ActionOutputEnum::SUCCESS => self::HYBRIDIZE_SUCCESS,
            ActionOutputEnum::FAIL => self::HYBRIDIZE_FAIL,
        ],
        ActionEnum::EXTINGUISH => [
            ActionOutputEnum::SUCCESS => self::EXTINGUISH_SUCCESS,
            ActionOutputEnum::FAIL => self::EXTINGUISH_FAIL,
        ],
        ActionEnum::HYPERFREEZE => [
            ActionOutputEnum::SUCCESS => self::HYPERFREEZE_SUCCESS,
        ],
        ActionEnum::EXPRESS_COOK => [
            ActionOutputEnum::SUCCESS => self::COOK_SUCCESS,
        ],
        ActionEnum::WRITE => [
            ActionOutputEnum::SUCCESS => self::WRITE_SUCCESS,
        ],
        ActionEnum::INSERT_OXYGEN => [
            ActionOutputEnum::SUCCESS => self::INSERT_OXYGEN,
        ],
        ActionEnum::RETRIEVE_OXYGEN => [
            ActionOutputEnum::SUCCESS => self::RETRIEVE_OXYGEN,
        ],
        ActionEnum::INSERT_FUEL => [
            ActionOutputEnum::SUCCESS => self::INSERT_FUEL,
        ],
        ActionEnum::RETRIEVE_FUEL => [
            ActionOutputEnum::SUCCESS => self::RETRIEVE_FUEL,
        ],
        ActionEnum::COOK => [
            ActionOutputEnum::SUCCESS => self::COOK_SUCCESS,
        ],
        ActionEnum::COFFEE => [
            ActionOutputEnum::SUCCESS => self::COFFEE_SUCCESS,
        ],
        ActionEnum::DISPENSE => [
            ActionOutputEnum::SUCCESS => self::DISPENSE_SUCCESS,
        ],
        ActionEnum::SHOWER => [
            ActionOutputEnum::SUCCESS => self::SHOWER_HUMAN,
            ActionOutputEnum::FAIL => self::SHOWER_MUSH,
        ],
        ActionEnum::WASH_IN_SINK => [
            ActionOutputEnum::SUCCESS => self::WASH_IN_SINK_HUMAN,
            ActionOutputEnum::FAIL => self::WASH_IN_SINK_MUSH,
        ],
        ActionEnum::LIE_DOWN => [
            ActionOutputEnum::SUCCESS => self::LIE_DOWN,
        ],
        ActionEnum::GET_UP => [
            ActionOutputEnum::SUCCESS => self::GET_UP,
        ],
        ActionEnum::HIT => [
            ActionOutputEnum::SUCCESS => self::HIT_SUCCESS,
            ActionOutputEnum::FAIL => self::HIT_FAIL,
        ],
        ActionEnum::COMFORT => [
            ActionOutputEnum::SUCCESS => self::COMFORT_SUCCESS,
        ],
        ActionEnum::HEAL => [
            ActionOutputEnum::SUCCESS => self::HEAL_SUCCESS,
        ],
        ActionEnum::SELF_HEAL => [
            ActionOutputEnum::SUCCESS => self::SELF_HEAL,
        ],
        ActionEnum::ULTRAHEAL => [
            ActionOutputEnum::SUCCESS => self::ULTRAHEAL_SUCCESS,
        ],
        ActionEnum::USE_BANDAGE => [
            ActionOutputEnum::SUCCESS => self::SELF_HEAL,
        ],
        ActionEnum::SPREAD_FIRE => [
            ActionOutputEnum::SUCCESS => self::SPREAD_FIRE_SUCCESS,
        ],
        ActionEnum::INSTALL_CAMERA => [
            ActionOutputEnum::SUCCESS => self::INSTALL_CAMERA,
        ],
        ActionEnum::REMOVE_CAMERA => [
            ActionOutputEnum::SUCCESS => self::REMOVE_CAMERA,
        ],

        ActionEnum::STRENGTHEN_HULL => [
            ActionOutputEnum::SUCCESS => self::STRENGTHEN_SUCCESS,
            ActionOutputEnum::FAIL => self::DEFAULT_FAIL,
        ],

        ActionEnum::FLIRT => [
            ActionOutputEnum::SUCCESS => self::FLIRT_SUCCESS,
        ],

        ActionEnum::MOVE => [
            ActionOutputEnum::SUCCESS => self::ENTER_ROOM,
        ],

        ActionEnum::DO_THE_THING => [
            ActionOutputEnum::SUCCESS => self::DO_THE_THING_SUCCESS,
        ],

        ActionEnum::CHECK_SPORE_LEVEL => [
            ActionOutputEnum::SUCCESS => self::CHECK_SPORE_LEVEL,
        ],

        ActionEnum::REMOVE_SPORE => [
            ActionOutputEnum::SUCCESS => self::REMOVE_SPORE_SUCCESS,
            ActionOutputEnum::FAIL => self::REMOVE_SPORE_FAIL,
        ],
        ActionEnum::PUBLIC_BROADCAST => [
            ActionOutputEnum::SUCCESS => self::PUBLIC_BROADCAST,
        ],
        ActionEnum::EXTINGUISH_MANUALLY => [
            ActionOutputEnum::SUCCESS => self::EXTINGUISH_SUCCESS,
            ActionOutputEnum::FAIL => self::EXTINGUISH_FAIL,
        ],
        ActionEnum::MOTIVATIONAL_SPEECH => [
            ActionOutputEnum::SUCCESS => self::MOTIVATIONAL_SPEECH,
        ],
        ActionEnum::BORING_SPEECH => [
            ActionOutputEnum::SUCCESS => self::BORING_SPEECH,
        ],
        ActionEnum::MAKE_SICK => [
            ActionOutputEnum::SUCCESS => self::MAKE_SICK,
        ],
        ActionEnum::FAKE_DISEASE => [
            ActionOutputEnum::SUCCESS => self::FAKE_DISEASE,
        ],
        ActionEnum::SURGERY => [
            ActionOutputEnum::FAIL => self::FAIL_SURGERY,
        ],
        ActionEnum::SELF_SURGERY => [
            ActionOutputEnum::FAIL => self::FAIL_SELF_SURGERY,
        ],
        ActionEnum::SCREW_TALKIE => [
            ActionOutputEnum::SUCCESS => self::SCREW_TALKIE_SUCCESS,
        ],
        ActionEnum::UPDATE_TALKIE => [
            ActionOutputEnum::SUCCESS => self::UPDATE_TALKIE_SUCCESS,
        ],
    ];
}
