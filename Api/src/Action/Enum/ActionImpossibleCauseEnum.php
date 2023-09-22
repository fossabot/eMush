<?php

namespace Mush\Action\Enum;

class ActionImpossibleCauseEnum
{
    public const INSUFFICIENT_ACTION_POINT = 'insufficient_action_point';
    public const NOT_A_ROOM = 'not_a_room';
    public const DAILY_LIMIT = 'daily_limit';
    public const CEASEFIRE = 'ceasefire';
    public const UNIQUE_ACTION = 'unique_action';
    public const IN_SPACE_CAPSULE = 'in_space_capsule';
    public const IN_PATROLLER = 'in_patroller';
    public const NO_SHELVING_UNIT = 'no_shelving_unit';
    public const ON_PLANET = 'on_planet';
    public const PRE_MUSH_RESTRICTED = 'pre_mush_restricted';
    public const PRE_MUSH_AGGRESSIVE = 'pre_mush_aggressive';
    public const HEAL_NO_INJURY = 'heal_no_injury';
    public const FULL_INVENTORY = 'full_inventory';
    public const DMZ_CORE_PEACE = 'dmz_core_peace';
    public const DAEDALUS_TRAVELING = 'daedalus_traveling';
    public const BROKEN_EQUIPMENT = 'broken_equipment';
    public const DIRTY_RESTRICTION = 'dirty_restriction';
    public const TERMINAL_ROLE_RESTRICTED = 'terminal_role_restricted';
    public const TERMINAL_NERON_LOCK = 'terminal_neron_lock';
    public const EXPLORE_NOTHING_LEFT = 'explore_nothing_left';
    public const EXPLORE_NOT_IN_ORBIT = 'explore_not_in_orbit';
    public const FIRE_OUT_AMMUNITION = 'fire_out_ammunition';
    public const UNLOADED_WEAPON = 'unloaded_weapon';
    public const DO_THE_THING_ALREADY_DONE = 'do_the_thing_already_done';
    public const DO_THE_THING_CAMERA = 'do_the_thing_camera';
    public const DO_THE_THING_WITNESS = 'do_the_thing_witness';
    public const DO_THE_THING_ASLEEP = 'do_the_thing_asleep';
    public const DO_THE_THING_NO_BED = 'do_the_thing_no_bed';
    public const DO_THE_THING_NOT_INTERESTED = 'do_the_thing_not_interested';
    public const FLIRT_ALREADY_FLIRTED = 'flirt_already_flirted';
    public const FLIRT_ANTISOCIAL = 'flirt_antisocial';
    public const FLIRT_SAME_FAMILY = 'flirt_same_family';
    public const DAILY_SPORE_LIMIT = 'daily_spore_limit';
    public const PERSONAL_SPORE_LIMIT = 'personal_spore_limit';
    public const BOOBY_TRAP_ALREADY_DONE = 'booby_trap_already_done';
    public const SLIME_ALREADY_DONE = 'slime_already_done';
    public const INFECT_IMMUNE = 'infect_immune';
    public const INFECT_NO_SPORE = 'infect_no_spore';
    public const INFECT_MUSH = 'infect_mush';
    public const INFECT_DAILY_LIMIT = 'infect_daily_limit';
    public const PHAGOCYTE_NO_SPORE = 'phagocyte_no_spore';
    public const TRANSFER_NO_SPORE = 'transfer_no_spore';
    public const MUTATED = 'mutated';
    public const WHISPER_MUTE = 'whisper_mute';
    public const WHISPER_NO_AVAILABLE_CHANEL = 'whisper_no_available_chanel';
    public const TARGET_ALREADY_OUTCAST = 'target_already_outcast';
    public const ALREADY_OUTCAST_ONBOARD = 'already_outcast_onboard';
    public const LONELY_APPRENTICESHIP = 'lonely_apprenticeship';
    public const LIST_ALREADY_PRINTED = 'list_already_printed';
    public const LIST_NO_MUSH = 'list_no_mush';
    public const ISSUE_MISSION_ALREADY_ISSUED = 'issue_mission_already_issued';
    public const ISSUE_MISSION_NO_TARGET = 'issue_mission_no_target';
    public const DRONE_UPGRADE_LACK_RESSOURCES = 'drone_upgrade_lack_ressources';
    public const BUILD_LACK_RESSOURCES = 'build_lack_ressources';
    public const MAGE_BOOK_ALREADY_HAVE_SKILL = 'mage_book_already_have_skill';
    public const MAGE_BOOK_ALREADY_HAVE_READ = 'mage_book_already_have_read';
    public const CONSUME_DRUG_TWICE = 'consume_drug_twice';
    public const CONSUME_FULL_BELLY = 'consume_full_belly';
    public const REINFORCE_FULL_HULL = 'reinforce_full_hull';
    public const ALREADY_INSTALLED_CAMERA = 'already_installed_camera';
    public const WATER_PLANT_NO_THIRSTY = 'water_plant_no_thirsty';
    public const TREAT_PLANT_NO_DISEASE = 'treat_plant_no_disease';
    public const LAUNCH_GRENADE_ALONE = 'launch_grenade_alone';
    public const SABOTAGE_NO_DOOR = 'sabotage_no_door';
    public const ANALYSE_NOTHING_UNDISCOVERED = 'analyse_nothing_undiscovered';
    public const NO_PILGRED = 'no_pilgred';
    public const NOT_ENOUGH_MAP_FRAGMENTS = 'not_enough_map_fragments';
    public const NO_DIRECTION_PICKED = 'no_direction_picked';
    public const COMS_ALREADY_ATTEMPTED = 'coms_already_attempted';
    public const NO_ACTIVE_REBEL = 'no_active_rebel';
    public const NO_SELECTED_REBEL = 'no_selected_rebel';
    public const NO_XYLOPH_LEFT = 'no_xyloph_left';
    public const NO_PROJECT_LEFT = 'no_project_left';
    public const COMS_NOT_OFFICER = 'coms_not_officer';
    public const DEAL_LACK_RESSOURCES = 'deal_lack_ressources';
    public const DEAL_NOT_COMS_OFFICER = 'deal_not_coms_officer';
    public const DISMANTLE_REINFORCED = 'dismantle_reinforced';
    public const BED_OCCUPIED = 'bed_occupied';
    public const ALREADY_IN_BED = 'already_in_bed';
    public const REINFORCE_LACK_RESSOURCES = 'reinforce_lack_ressources';
    public const RENOVATE_LACK_RESSOURCES = 'renovate_lack_ressources';
    public const SURGERY_NOT_LYING_DOWN = 'surgery_not_lying_down';
    public const MUSH_REMOVE_SPORE = 'mush_remove_spore';
    public const IMMUNIZED_REMOVE_SPORE = 'immunized_remove_spore';
    public const ALREADY_DID_BORING_SPEECH = 'already_did_boring_speech';
    public const HAVE_ALL_FAKE_DISEASES = 'have_all_fake_diseases';
    public const SYMPTOMS_ARE_PREVENTING_ACTION = 'symptoms_are_preventing_action';
    public const GAGGED_PREVENT_SPOKEN_ACTION = 'gagged_prevent_spoken_action';
    public const SCREWED_TALKIE_ALREADY_PIRATED = 'screwed_talkie_already_pirated';
    public const SCREWED_TALKIE_NO_TALKIE = 'screwed_talkie_no_talkie';
    public const ALREADY_WASHED_IN_SINK_TODAY = 'already_washed_in_sink_today';
    public const DAEDALUS_ALREADY_FULL_HULL = 'daedalus_already_full_hull';

    public const UPDATE_TALKIE_REQUIRE_NERON = 'update_talkie_require_neron';
    public const UPDATE_TALKIE_REQUIRE_TRACKER = 'update_talkie_require_tracker';
}
