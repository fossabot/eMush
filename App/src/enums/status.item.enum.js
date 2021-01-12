const HEAVY = "heavy";
const HIDDEN = "hidden";
const PLANT_YOUNG = "plant_young";
const PLANT_THIRSTY= "plant_thirsty";
const PLANT_DRIED_OUT= "plant_dried_out";
const PLANT_DISEASED = "plant_diseased";
const CHARGE = "charges";
const BROKEN = "broken";
const FROZEN = "broken";


export const statusItemEnum = {
    [HEAVY]: {
        'icon': require('@/assets/images/status/heavy.png')
    },
    [HIDDEN]: {
        'icon': require('@/assets/images/status/hidden.png')
    },
    [PLANT_YOUNG]: {
        'icon': require('@/assets/images/status/plant_youngling.png')
    },
    [PLANT_THIRSTY]: {
        'icon': require('@/assets/images/status/plant_thirsty.png')
    },
    [PLANT_DRIED_OUT]: {
        'icon': require('@/assets/images/status/plant_dry.png')
    },
    [PLANT_DISEASED]: {
        'icon': require('@/assets/images/status/plant_diseased.png')
    },
    [CHARGE]: {
        'icon': require('@/assets/images/status/charge.png')
    },
    [BROKEN]: {
        'icon': require('@/assets/images/status/broken.png')
    },
    [FROZEN]: {
        'icon': require('@/assets/images/status/food_frozen.png')
    }
};
