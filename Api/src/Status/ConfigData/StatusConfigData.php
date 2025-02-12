<?php

namespace Mush\Status\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

/** @codeCoverageIgnore */
class StatusConfigData
{
    public static array $dataArray = [
        [
            'name' => 'alien_artefact_default',
            'statusName' => 'alien_artefact',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'heavy_default',
            'statusName' => 'heavy',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'module_access_default',
            'statusName' => 'module_access',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'hidden_default',
            'statusName' => 'hidden',
            'visibility' => 'private',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'broken_default',
            'statusName' => 'broken',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'unstable_default',
            'statusName' => 'unstable',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'hazardous_default',
            'statusName' => 'hazardous',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'decomposing_default',
            'statusName' => 'decomposing',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'frozen_default',
            'statusName' => 'frozen',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'modifier_for_equipment_+1actionPoint_on_consume',
            ],
        ],
        [
            'name' => 'plant_thirsty_default',
            'statusName' => 'plant_thirsty',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'plant_dry_default',
            'statusName' => 'plant_dry',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'plant_diseased_default',
            'statusName' => 'plant_diseased',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'document_content_default',
            'statusName' => 'document_content',
            'visibility' => 'hidden',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'reinforced_default',
            'statusName' => 'reinforced',
            'visibility' => 'hidden',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'antisocial_default',
            'statusName' => 'antisocial',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'antisocial_modifier_for_player_-1moralPoint_on_new_cycle_if_player_in_room_not_alone',
            ],
        ],
        [
            'name' => 'berzerk_default',
            'statusName' => 'berzerk',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'brainsync_default',
            'statusName' => 'brainsync',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'burdened_default',
            'statusName' => 'burdened',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'modifier_for_player_+2movementPoint_on_move',
            ],
        ],
        [
            'name' => 'demoralized_default',
            'statusName' => 'demoralized',
            'visibility' => 'private',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'modifier_for_player_+30percentage_on_cycle_disease',
            ],
        ],
        [
            'name' => 'dirty_default',
            'statusName' => 'dirty',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'disabled_default',
            'statusName' => 'disabled',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'modifier_for_player_-1movementPoint_on_move_if_player_in_room_not_alone',
                'modifier_for_player_-2movementPoint_on_event_action_movement_conversion',
            ],
        ],
        [
            'name' => 'focused_default',
            'statusName' => 'focused',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'full_stomach_default',
            'statusName' => 'full_stomach',
            'visibility' => 'private',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'gagged_default',
            'statusName' => 'gagged',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => ['mute_modifier'],
        ],
        [
            'name' => 'germaphobe_default',
            'statusName' => 'germaphobe',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'guardian_default',
            'statusName' => 'guardian',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'highly_inactive_default',
            'statusName' => 'highly_inactive',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'hyperactive_default',
            'statusName' => 'hyperactive',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'immunized_default',
            'statusName' => 'immunized',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'inactive_default',
            'statusName' => 'inactive',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'lost_default',
            'statusName' => 'lost',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'modifier_for_player_-1moralPoint_on_new_cycle',
            ],
        ],
        [
            'name' => 'lying_down_default',
            'statusName' => 'lying_down',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'lying_down_modifier_for_player_+1actionPoint_on_new_cycle',
            ],
        ],
        [
            'name' => 'multi_teamster_default',
            'statusName' => 'multi_teamster',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'outcast_default',
            'statusName' => 'outcast',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'pacifist_default',
            'statusName' => 'pacifist',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'modifier_for_place_+2actionPoint_on_action_aggressive',
            ],
        ],
        [
            'name' => 'pregnant_default',
            'statusName' => 'pregnant',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'starving_default',
            'statusName' => 'starving',
            'visibility' => 'private',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'starving_for_player_-1healthPoint_on_new_cycle',
            ],
        ],
        [
            'name' => 'stuck_in_the_ship_default',
            'statusName' => 'stuck_in_the_ship',
            'visibility' => 'public',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'suicidal_default',
            'statusName' => 'suicidal',
            'visibility' => 'private',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [
                'modifier_for_player_+30percentage_on_cycle_disease',
            ],
        ],
        [
            'name' => 'WATCHED_PUBLIC_BROADCAST_default',
            'statusName' => 'WATCHED_PUBLIC_BROADCAST',
            'visibility' => 'hidden',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'talkie_screwed_default',
            'statusName' => 'talkie_screwed',
            'visibility' => 'hidden',
            'type' => 'status_config',
            'chargeVisibility' => null,
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => null,
            'dischargeStrategies' => ['none'],
            'autoRemove' => null,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'attempt_default',
            'statusName' => 'attempt',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => 0,
            'dischargeStrategies' => ['none'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_microwave_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 4,
            'startCharge' => 1,
            'dischargeStrategies' => ['express_cook'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_antigrav_scooter_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 8,
            'startCharge' => 2,
            'dischargeStrategies' => [ModifierNameEnum::ANTIGRAV_SCOOTER_CONVERSION_MODIFIER],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_blaster_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 3,
            'startCharge' => 1,
            'dischargeStrategies' => ['shoot'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_old_faithful_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 12,
            'startCharge' => 12,
            'dischargeStrategies' => ['shoot'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_rocket_launcher_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['shoot'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_narcotic_distiller_default',
            'statusName' => 'electric_charges',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'daily_increment',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['dispense'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_coffee_machine_default',
            'statusName' => 'electric_charges',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'daily_increment',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['coffee'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_turret_command_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => 4,
            'startCharge' => 4,
            'dischargeStrategies' => ['shoot_hunter', ActionEnum::SHOOT_RANDOM_HUNTER],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'electric_charges_patrol_ship_default',
            'statusName' => 'electric_charges',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => ChargeStrategyTypeEnum::PATROL_SHIP_CHARGE_INCREMENT,
            'maxCharge' => 10,
            'startCharge' => 10,
            'dischargeStrategies' => ['shoot_hunter_patrol_ship', ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'fire_default',
            'statusName' => 'fire',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'cycle_increment',
            'maxCharge' => null,
            'startCharge' => 0,
            'dischargeStrategies' => ['none'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'plant_young_default',
            'statusName' => 'plant_young',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'growing_plant',
            'maxCharge' => null,
            'startCharge' => 0,
            'dischargeStrategies' => ['none'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'eureka_moment_default',
            'statusName' => 'eureka_moment',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'none',
            'maxCharge' => 1,
            'startCharge' => 0,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'first_time_default',
            'statusName' => 'first_time',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'none',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'mush_default',
            'statusName' => 'mush',
            'visibility' => 'mush',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'daily_reset',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['none'],
            'autoRemove' => false,
            'modifierConfigs' => [
                'mush_shower_malus_for_player_set_-3healthPoint_on_post.action_if_reason_shower',
                'modifier_for_player_prevent_change.variable_if_reason_consume',
                'modifier_for_player_set_4satiety_on_change.variable_if_reason_consume',
                'modifier_for_player_set_0moralPoint_on_change.variable',
            ],
        ],
        [
            'name' => 'contaminated_default',
            'statusName' => 'contaminated',
            'visibility' => 'mush',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'mush',
            'chargeStrategy' => 'none',
            'maxCharge' => null,
            'startCharge' => 0,
            'dischargeStrategies' => ['none'],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'drug_eaten_default',
            'statusName' => 'drug_eaten',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'cycle_decrement',
            'maxCharge' => 2,
            'startCharge' => 2,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'did_the_thing_default',
            'statusName' => 'did_the_thing',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'daily_decrement',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'did_boring_speech_default',
            'statusName' => 'did_boring_speech',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'daily_decrement',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'already_washed_in_the_sink_default',
            'statusName' => 'already_washed_in_the_sink',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'hidden',
            'chargeStrategy' => 'daily_decrement',
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'updating_default',
            'statusName' => 'updating',
            'visibility' => 'public',
            'type' => 'charge_status_config',
            'chargeVisibility' => 'public',
            'chargeStrategy' => 'cycle_decrement',
            'maxCharge' => 4,
            'startCharge' => 4,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => HunterStatusEnum::TRUCE_CYCLES . '_asteroid_default',
            'statusName' => HunterStatusEnum::TRUCE_CYCLES,
            'visibility' => VisibilityEnum::PUBLIC,
            'type' => 'charge_status_config',
            'chargeVisibility' => VisibilityEnum::PUBLIC,
            'chargeStrategy' => 'cycle_decrement',
            'maxCharge' => 6,
            'startCharge' => 6,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => PlayerStatusEnum::HAS_REJUVENATED . '_default',
            'statusName' => PlayerStatusEnum::HAS_REJUVENATED,
            'visibility' => VisibilityEnum::HIDDEN,
            'type' => 'charge_status_config',
            'chargeVisibility' => VisibilityEnum::HIDDEN,
            'chargeStrategy' => ChargeStrategyTypeEnum::DAILY_DECREMENT,
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => ['none'],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_default',
            'statusName' => EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            'visibility' => VisibilityEnum::PUBLIC,
            'type' => 'charge_status_config',
            'chargeVisibility' => VisibilityEnum::HIDDEN,
            'chargeStrategy' => ChargeStrategyTypeEnum::NONE,
            'maxCharge' => 10,
            'startCharge' => 10,
            'dischargeStrategies' => [ChargeStrategyTypeEnum::NONE],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default',
            'statusName' => EquipmentStatusEnum::PATROL_SHIP_ARMOR,
            'visibility' => VisibilityEnum::PUBLIC,
            'type' => 'charge_status_config',
            'chargeVisibility' => VisibilityEnum::HIDDEN,
            'chargeStrategy' => ChargeStrategyTypeEnum::NONE,
            'maxCharge' => 12,
            'startCharge' => 12,
            'dischargeStrategies' => [ChargeStrategyTypeEnum::NONE],
            'autoRemove' => false,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'traveling_default',
            'statusName' => 'traveling',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => VisibilityEnum::HIDDEN,
            'chargeStrategy' => ChargeStrategyTypeEnum::CYCLE_DECREMENT,
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => [ChargeStrategyTypeEnum::NONE],
            'autoRemove' => true,
            'modifierConfigs' => [],
        ],
        [
            'name' => 'no_gravity_repaired_default',
            'statusName' => 'no_gravity_repaired',
            'visibility' => 'hidden',
            'type' => 'charge_status_config',
            'chargeVisibility' => VisibilityEnum::HIDDEN,
            'chargeStrategy' => ChargeStrategyTypeEnum::CYCLE_DECREMENT,
            'maxCharge' => 1,
            'startCharge' => 1,
            'dischargeStrategies' => [ChargeStrategyTypeEnum::NONE],
            'autoRemove' => true,
            'modifierConfigs' => [
                'modifier_for_daedalus_-1movementPoint_on_change.variable_if_reason_new_cycle',
                'modifier_for_daedalus_-1movementPoint_on_event_action_movement_conversion',
            ],
        ],
        [
            'name' => 'no_gravity_default',
            'statusName' => 'no_gravity',
            'visibility' => 'hidden',
            'type' => 'status_config',
            'modifierConfigs' => [
                'modifier_for_daedalus_-1movementPoint_on_change.variable_if_reason_new_cycle',
                'modifier_for_daedalus_-1movementPoint_on_event_action_movement_conversion',
            ],
        ],
    ];
}
