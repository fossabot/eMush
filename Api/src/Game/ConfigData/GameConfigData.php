<?php

namespace Mush\Game\ConfigData;

use Mush\Hunter\Enum\HunterEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

/** @codeCoverageIgnore */
class GameConfigData
{
    public static array $dataArray = [
        [
            'daedalusConfig' => 'default',
            'difficultyConfig' => 'default',
            'name' => 'default',
            'characterConfigs' => ['andie', 'chun', 'derek', 'eleesha', 'frieda', 'gioele', 'hua', 'ian', 'janice', 'jin_su', 'kuan_ti', 'paola', 'raluca', 'roland', 'stephen', 'terrence'],
            'consumableDiseaseConfigs' => ['creepnut_default', 'meztine_default', 'guntiflop_default', 'ploshmina_default', 'precati_default', 'bottine_default', 'fragilane_default', 'anemole_default', 'peniraft_default', 'kubinus_default', 'caleboot_default', 'filandra_default', 'junkin_default', 'alien_steak_default', 'supervitamin_bar_default', 'bacta_default', 'betapropyl_default', 'eufurylate_default', 'newke_default', 'phuxx_default', 'pinq_default', 'pymp_default', 'rosebud_default', 'soma_default', 'spyce_default', 'twinoid_default', 'xenox_default'],
            'diseaseCauseConfigs' => ['alien_fruit_default', 'perished_food_default', 'cat_allergy_default', 'cycle_default', 'cycle_low_morale_default', 'make_sick_default', 'fake_disease_default', 'surgery_default', 'infection_default', 'sex_default', 'trauma_default', 'contact_default', 'critical_fail_knife_default', 'critical_success_knife_default', 'critical_fail_blaster_default', 'critical_success_blaster_default'],
            'diseaseConfigs' => ['food_poisoning_default', 'vitamin_deficiency_default', 'tapeworm_default', 'syphilis_default', 'space_rabies_default', 'smallpox_default', 'slight_nausea_default', 'skin_inflammation_default', 'sinus_storm_default', 'sepsis_default', 'rubella_default', 'rejuvenation_default', 'quincks_oedema_default', 'mush_allergy_default', 'migraine_default', 'junkbumpkinitis_default', 'gastroenteritis_default', 'fungic_infection_default', 'flu_default', 'extreme_tinnitus_default', 'cold_default', 'cat_allergy_default', 'black_bite_default', 'acid_reflux_default', 'broken_finger_default', 'broken_foot_default', 'broken_leg_default', 'broken_ribs_default', 'bruised_shoulder_default', 'burns_50_of_body_default', 'burns_90_of_body_default', 'burnt_arms_default', 'burnt_hand_default', 'burst_nose_default', 'busted_arm_joint_default', 'busted_shoulder_default', 'critical_haemorrhage_default', 'haemorrhage_default', 'minor_haemorrhage_default', 'damaged_ears_default', 'destroyed_ears_default', 'dysfunctional_liver_default', 'head_trauma_default', 'implanted_bullet_default', 'inner_ear_damaged_default', 'mashed_foot_default', 'mashed_hand_default', 'missing_finger_default', 'open_air_brain_default', 'punctured_lung_default', 'mashed_arms_default', 'mashed_legs_default', 'torn_tongue_default', 'broken_shoulder_default', 'agoraphobia_default', 'ailurophobia_default', 'chronic_migraine_default', 'chronic_vertigo_default', 'coprolalia_default', 'crabism_default', 'depression_default', 'paranoia_default', 'psychotic_episodes_default', 'spleen_default', 'vertigo_default', 'weapon_phobia_default'],
            'equipmentConfigs' => ['quadrimetric_compass_default', 'rope_default', 'drill_default', 'babel_module_default', 'echolocator_default', 'thermosensor_default', 'white_flag_default', 'apprenton_astrophysicist_default', 'apprenton_biologist_default', 'apprenton_botanist_default', 'apprenton_diplomat_default', 'apprenton_firefighter_default', 'apprenton_ chef_default', 'apprenton_it_expert_default', 'apprenton_logistics_expert_default', 'apprenton_medic_default', 'apprenton_pilot_default', 'apprenton_ radio_expert_default', 'apprenton_robotics_expert_default', 'apprenton_shooter_default', 'apprenton_shrink_default', 'apprenton_sprinter_default', 'apprenton_technician_default', 'document_default', 'mush_research_review_default', 'commanders_manual_default', 'post_it_default', 'bacta_default', 'betapropyl_default', 'eufurylate_default', 'newke_default', 'phuxx_default', 'pinq_default', 'pymp_default', 'rosebud_default', 'soma_default', 'spyce_default', 'twinoid_default', 'xenox_default', 'banana_default', 'banana_tree_default', 'creepnut_default', 'creepist_default', 'meztine_default', 'cactax_default', 'guntiflop_default', 'bifflon_default', 'ploshmina_default', 'pulminagro_default', 'precati_default', 'recatus_default', 'bottine_default', 'buttalien_default', 'fragilane_default', 'platacia_default', 'anemole_default', 'tubiliscus_default', 'peniraft_default', 'graapshoot_default', 'kubinus_default', 'Fiboniccus_default', 'caleboot_default', 'mycopia_default', 'filandra_default', 'asperagunk_default', 'junkin_default', 'bumpjunkin_default', 'standard_ration_default', 'cooked_ration_default', 'coffee_default', 'anabolic_default', 'alien_steak_default', 'space_potato_default', 'proactive_puffed_rice_default', 'lombrick_bar_default', 'supervitamin_bar_default', 'organic_waste_default', 'hacker_kit_default', 'block_of_post_it_default', 'camera_item_default', 'extinguisher_default', 'duct_tape_default', 'mad_kube_default', 'microwave_default', 'superfreezer_default', 'alien_holographic_tv_default', 'medikit_default', 'spore_sucker_default', 'jar_of_alien_oil_default', 'bandage_default', 'retro_fungal_serum_default', 'space_capsule_default', 'adjustable_wrench_default', 'plastenite_armor_default', 'stainproof_apron_default', 'protective_gloves_default', 'soap_default', 'alien_bottle_opener_default', 'antigrav_scooter_default', 'sniper_helmet_default', 'ncc_lenses_default', 'rolling_boulder_default', 'oscilloscope_default', 'spacesuit_default', 'super_soaper_default', 'printed_circuit_jelly_default', 'invertebrate_shell_default', 'magellan_liquid_map_default', 'blaster_default', 'knife_default', 'lizaro_jungle_default', 'grenade_default', 'old_faithful_default', 'rocket_launcher_default', 'natamy_rifle_default', 'icarus_default', 'door_default', 'communication_center_default', 'neron_core_default', 'astro_terminal_default', 'research_laboratory_default', 'pilgred_default', 'calculator_default', 'bios_terminal_default', 'command_terminal_default', 'planet_scanner_default', 'jukebox_default', 'emergency_reactor_default', 'reactor_lateral_default', 'reactor_lateral_alpha_default', 'reactor_lateral_bravo_default', 'antenna_default', 'gravity_simulator_default', 'thalasso_default', 'patrol_ship_alpha_longane_default', 'patrol_ship_alpha_jujube_default', 'patrol_ship_alpha_tamarin_default', 'patrol_ship_bravo_socrate_default', 'patrol_ship_bravo_epicure_default', 'patrol_ship_bravo_planton_default', 'patrol_ship_alpha_2_wallis_default', 'pasiphae_default', 'camera_equipment_default', 'combustion_chamber_default', 'kitchen_default', 'narcotic_distiller_default', 'shower_default', 'dynarcade_default', 'bed_default', 'medlab_bed_default', 'coffee_machine_default', 'cryo_module_default', 'mycoscan_default', 'turret_command_default', 'surgery_plot_default', 'fuel_tank_default', 'oxygen_tank_default', 'itrackie_default', 'tracker_default', 'walkie_talkie_default', 'tabulatrix_default', 'myco_alarm_default', 'plastic_scraps_default', 'metal_scraps_default', 'old_t_shirt_default', 'thick_tube_default', 'mush_sample_default', 'mush_genome_disk_default', 'starmap_fragment_default', 'water_stick_default', 'hydropot_default', 'oxygen_capsule_default', 'fuel_capsule_default', 'echolocator_blueprint_default', 'white_flag_blueprint_default', 'babel_module_blueprint_default', 'thermosensor_blueprint_default', 'grenade_blueprint_default', 'old_faithful_blueprint_default', 'lizaro_jungle_blueprint_default', 'rocket_launcher_blueprint_default', 'extinguisher_blueprint_default', 'oscilloscope_blueprint_default', 'sniper_helmet_blueprint_default'],
            'statusConfigs' => ['alien_artefact_default', 'heavy_default', 'module_access_default', 'hidden_default', 'broken_default', 'unstable_default', 'hazardous_default', 'decomposing_default', 'frozen_default', 'plant_thirsty_default', 'plant_dry_default', 'plant_diseased_default', 'document_content_default', 'reinforced_default', 'antisocial_default', 'berzerk_default', 'brainsync_default', 'burdened_default', 'demoralized_default', 'dirty_default', 'disabled_default', 'focused_default', 'full_stomach_default', 'gagged_default', 'germaphobe_default', 'guardian_default', 'highly_inactive_default', 'hyperactive_default', 'immunized_default', 'inactive_default', 'lost_default', 'lying_down_default', 'multi_teamster_default', 'outcast_default', 'pacifist_default', 'pregnant_default', 'starving_default', 'stuck_in_the_ship_default', 'suicidal_default', 'WATCHED_PUBLIC_BROADCAST_default', 'attempt_default', 'electric_charges_antigrav_scooter_default', 'electric_charges_old_faithful_default', 'electric_charges_rocket_launcher_default', 'electric_charges_turret_command_default', 'electric_charges_microwave_default', 'electric_charges_coffee_machine_default', 'electric_charges_narcotic_distiller_default', 'electric_charges_blaster_default', 'fire_default', 'plant_young_default', 'eureka_moment_default', 'first_time_default', 'mush_default', 'contaminated_default', 'fuel_charge_default', 'drug_eaten_default', 'did_the_thing_default', 'did_boring_speech_default', 'updating_default', 'already_washed_in_the_sink_default', 'talkie_screwed_default', HunterStatusEnum::TRUCE_CYCLES . '_asteroid_default', PlayerStatusEnum::HAS_REJUVENATED . '_default', EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_default', EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default', 'electric_charges_patrol_ship_default'],
            'triumphConfigs' => ['alien_science', 'expedition', 'super_nova', 'first_starmap', 'next_starmap', 'cycle_mush', 'starting_mush', 'cycle_mush_late', 'conversion', 'infection', 'humanocide', 'chun_dead', 'sol_return_mush', 'eden_mush', 'cycle_human', 'cycle_inactive', 'new_planet_orbit', 'sol_contact', 'small_research', 'standard_research', 'brilliant_research', 'sol_return', 'sol_mush_intruder', 'hunter_killed', 'mushicide', 'rebel_wolf', 'nice_surgery', 'eden_crew_alive', 'eden_alien_plant', 'eden_gender', 'eden', 'eden_cat', 'eden_cat_dead', 'eden_cat_mush', 'eden_disease', 'eden_engineers', 'eden_biologist', 'eden_mush_intruder', 'eden_by_pregnant', 'eden_computed', 'anathema', 'pregnancy', 'all_pregnant'],
            'hunterConfigs' => [HunterEnum::ASTEROID . '_default', HunterEnum::DICE . '_default', HunterEnum::HUNTER . '_default', HunterEnum::SPIDER . '_default', HunterEnum::TRAX . '_default'],
        ],
    ];
}
