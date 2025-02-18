<?php

//For Maindeck cards, $tag1 and $tag2 can be any tag that you want to use to filter results. For equipment, set $tag1 according to note below
function GetPool($type, $hero, $rarity, $background, $tag1="", $tag2 = "",){
  if(($hero == "Bravo" || $hero == "Dorinthea" || $hero == "Arakni") && $type == "Talent") $type = "Class";
  if($hero == "ALL") $background = $rarity;
  if($type == "Class") return GetPoolClass(array($rarity, $background));
  else if($type == "Generic") return GetPoolGeneric(array($rarity));
  else if($type == "Talent") return GetPoolTalent(array($type, $rarity, $background));
  else if($type == "Equipment") {
    //Okay, this is a little weird, but to call for equipment, set $type to be "Equipment", and $tag1 to be either "Generic", "All", or "Hero". Default is "All".
    if($rarity == "-"){
      return GetPoolLogicEquipment($tag1, $hero, array($tag2));
    }
    else {
      return GetPoolLogicEquipment($tag1, $hero, array($rarity, $tag2));
    }
  }
}

//See GetPool() for logic. $type would be
function GetPoolLogicEquipment($tag1, $hero, $tags){
  if($tag1 == "Hero"){
    array_push($tags, $hero);
    return GetPoolEquipment($tags);
  }
  else if($tag1 == "Generic"){
    array_push($tags, "Generic");
    return GetPoolEquipment($tags);
  }
  else { // "All" mode as default. Can return generic and class cards with the given tags.
    return array_merge(GetPoolLogicEquipment("Hero", $hero, $tags), GetPoolLogicEquipment("Generic", $hero, $tags));
  }

}

//Called at DecisionQueue.php at Backgrounds event
function GiveUniversalEquipment(){
  $character = &GetZone(1, "Character");
  array_push($character, "ironrot_plate");
}

//Input a list of parameters
function GetPoolClass($arrayParameters){

  $CardRewardPool = array(
    array("crippling_crush_red", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"),
    array("spinal_crush_red", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"),
    array("cranial_crush_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("forged_for_war_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("show_time_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("disable_red", "Class", "Rare", "Anothos", "TitanFist", "Sledge"), //Disable
    array("disable_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("disable_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("staunch_response_red", "Class", "Rare", "Anothos", "TitanFist", "Sledge"), //Staunch Response
    array("staunch_response_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("staunch_response_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("blessing_of_deliverance_red", "Class", "Rare", "Anothos", "TitanFist", "Sledge"), //Blessing of Deliverance
    array("blessing_of_deliverance_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("blessing_of_deliverance_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("buckling_blow_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Buckling Blow
    array("buckling_blow_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("buckling_blow_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("cartilage_crush_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Cartilage Crush
    array("cartilage_crush_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("cartilage_crush_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("crush_confidence_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Crush Confidence
    array("cartilage_crush_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("crush_confidence_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("debilitate_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Debilitate
    array("debilitate_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("debilitate_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("emerging_power_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Emerging Power
    array("emerging_power_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("emerging_power_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("stonewall_confidence_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Stonewall Confidence
    array("stonewall_confidence_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("stonewall_confidence_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),

    array("ancestral_empowerment_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Ancestral Empowerment
    array("head_jab_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Head Jab
    array("head_jab_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("head_jab_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("leg_tap_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Leg Tap
    array("leg_tap_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("leg_tap_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("surging_strike_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Surging Strike
    array("surging_strike_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("surging_strike_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),

    array("glint_the_quicksilver_blue", "Class", "Majestic", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("steelblade_supremacy_red", "Class", "Majestic", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("rout_red", "Class", "Majestic", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //120-122 are Supers. I'm putting them in the majestic queue
    array("singing_steelblade_yellow", "Class", "Majestic", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("ironsong_determination_yellow", "Class", "Majestic", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("overpower_red", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Overpower
    array("overpower_yellow", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("overpower_blue", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("steelblade_shunt_red", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Steelblade Shunt
    array("steelblade_shunt_yellow", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("steelblade_shunt_blue", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("warriors_valor_red", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Warrior's Valor
    array("warriors_valor_yellow", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("warriors_valor_blue", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("ironsong_response_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Ironsong Response
    array("ironsong_response_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("ironsong_response_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("biting_blade_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Biting Blade
    array("biting_blade_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("biting_blade_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("stroke_of_foresight_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Stroke of Foresight
    array("stroke_of_foresight_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("stroke_of_foresight_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("sharpen_steel_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Sharpern Steel
    array("sharpen_steel_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("sharpen_steel_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("driving_blade_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Driving Blade
    array("driving_blade_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("driving_blade_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("natures_path_pilgrimage_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Nature's Path Pilgrimage
    array("natures_path_pilgrimage_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("natures_path_pilgrimage_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),

    array("three_of_a_kind_red", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Three of a Kind
    array("endless_arrow_red", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Endless Arrow
    array("take_cover_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Take Cover
    array("take_cover_yellow", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("take_cover_blue", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("take_aim_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Take Aim
    array("take_aim_yellow", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("take_aim_blue", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("hamstring_shot_red", "Class", "Common", "Shiver", "Voltaire", "RedLiner"), //Hamstring Shot
    array("hamstring_shot_yellow", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("hamstring_shot_blue", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("searing_shot_red", "Class", "Common", "Shiver", "Voltaire", "RedLiner"), //Searing Shot
    array("searing_shot_yellow", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("searing_shot_blue", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),

    array("mangle_red", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"), //Mangle
    array("righteous_cleansing_yellow", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"), //Righteous Cleansing
    array("stamp_authority_blue", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"), //Stamp Authority
    array("towering_titan_red", "Class", "Rare", "Anothos", "TitanFist", "Sledge"), //Towering Titan
    array("towering_titan_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("towering_titan_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("crush_the_weak_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Crush the Weak
    array("crush_the_weak_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("crush_the_weak_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("chokeslam_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Chokeslam
    array("chokeslam_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("chokeslam_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("emerging_dominance_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Emerging Dominance
    array("emerging_dominance_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("emerging_dominance_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("blessing_of_serenity_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Blessing of Serenity
    array("blessing_of_serenity_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("blessing_of_serenity_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),

    array("flying_kick_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Flying Kick
    array("flying_kick_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("flying_kick_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("soulbead_strike_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Soulbead Strike
    array("soulbead_strike_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("soulbead_strike_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("torrent_of_tempo_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Torrent of Tempo
    array("torrent_of_tempo_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("torrent_of_tempo_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("bittering_thorns_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Bittering Thorns
    array("salt_the_wound_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Salt the Wound

    array("twinning_blade_yellow", "Class", "Majestic", "Saber", "Dawnblade", "AllWeps"), //Twinning Blade - Only Swords
    array("unified_decree_yellow", "Class", "Majestic", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Unified Decree
    array("spoils_of_war_red", "Class", "Majestic", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Spoils of War
    //Dauntless - Can be added when the AI can handle taxes
    array("out_for_blood_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Out for Blood
    array("out_for_blood_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("out_for_blood_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("hit_and_run_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Hit and Run
    array("hit_and_run_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("hit_and_run_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("push_forward_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Push Forward
    array("push_forward_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("push_forward_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),

    array("remorseless_red", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Remorseless
    array("poison_the_tips_yellow", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Poison the Tips
    array("sleep_dart_red", "Class", "Common", "Shiver", "Voltaire", "RedLiner"), //Sleep Dart
    array("sleep_dart_yellow", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("sleep_dart_blue", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),

    array("spill_blood_red", "Class", "Majestic", "Hatchet", "Battleaxe"), //Spill Blood - Only Axes
    array("dusk_path_pilgrimage_red", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Dusk Path Pilgrimage
    array("dusk_path_pilgrimage_yellow", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("dusk_path_pilgrimage_blue", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("plow_through_red", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Plow Through
    array("plow_through_yellow", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("plow_through_blue", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("second_swing_red", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Second Swing
    array("second_swing_yellow", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("second_swing_blue", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),

    array("frost_lock_blue", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Frost Lock
    array("light_it_up_yellow", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Light it Up
    array("ice_storm_red", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Ice Storm
    array("cold_wave_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer"), //Cold Wave - Could be worth ommiting due to the AI never playing DRs, but it still gets the "If this is fused" and "if you've fused this turn" type of things. Also, the player doesn't know that
    array("cold_wave_yellow", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer"),
    array("cold_wave_blue", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer"),
    array("snap_shot_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer"), //Snap Shot
    array("snap_shot_yellow", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer"),
    array("snap_shot_blue", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer"),
    array("blizzard_bolt_red", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"), //Blizzard Bolt
    array("blizzard_bolt_yellow", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),
    array("blizzard_bolt_blue", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),
    array("buzz_bolt_red", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"), //Buzz Bolt
    array("buzz_bolt_yellow", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),
    array("buzz_bolt_blue", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),
    array("chilling_icevein_red", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"), //Chilling Icevein
    array("chilling_icevein_yellow", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),
    array("chilling_icevein_blue", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),
    array("dazzling_crescendo_red", "Class", "Common", "Voltaire", "DeathDealer"), //Dazzling Crescendo
    array("dazzling_crescendo_yellow", "Class", "Common", "Voltaire", "DeathDealer"),
    array("dazzling_crescendo_blue", "Class", "Common", "Voltaire", "DeathDealer"),
    array("flake_out_red", "Class", "Common", "Shiver", "DeathDealer"), //Flake Out
    array("flake_out_yellow", "Class", "Common", "Shiver", "DeathDealer"),
    array("flake_out_blue", "Class", "Common", "Shiver", "DeathDealer"),
    array("frazzle_red", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"), //Frazzle
    array("frazzle_yellow", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),
    array("frazzle_blue", "Class", "Common", "Shiver", "Voltaire", "DeathDealer"),

    array("tear_asunder_blue", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"), //Tear Asunder
    array("embolden_red", "Class", "Rare", "Anothos", "TitanFist", "Sledge"), //Embolden
    array("embolden_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("embolden_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    //thump_red - Thump - Can the AI handle this? I haven't tested it yet. TODO Test it

    array("seek_and_destroy_red", "Class", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Seek and Destroy
    array("boltn_shot_red", "Class", "Common", "Shiver", "Voltaire", "RedLiner"), //Bolt'n' Shot
    array("boltn_shot_yellow", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("boltn_shot_blue", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("over_flex_red", "Class", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Over Flex
    array("over_flex_yellow", "Class", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("over_flex_blue", "Class", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),


    array("pulverize_red", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"),
    array("imposing_visage_blue", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"),
    //nerves_of_steel_blue Nerves of Steel - I think this one is situational enough that we can omit it for now
    array("thunder_quake_red", "Class", "Rare", "Anothos", "TitanFist", "Sledge"), //Thunder Quake
    array("thunder_quake_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("thunder_quake_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("macho_grande_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Macho Grande
    array("macho_grande_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("macho_grande_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("seismic_stir_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Seismic Stir
    array("seismic_stir_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("seismic_stir_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("steadfast_red", "Class", "Common", "Anothos", "TitanFist", "Sledge"), //Steadfast
    array("steadfast_yellow", "Class", "Common", "Anothos", "TitanFist", "Sledge"),
    array("steadfast_blue", "Class", "Common", "Anothos", "TitanFist", "Sledge"),

    array("hundred_winds_red", "Class", "Rare", "Emberblade", "Kodachi", "Edge"), //Hundred Winds
    array("hundred_winds_yellow", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("hundred_winds_blue", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("ride_the_tailwind_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Ride the Tailwind
    array("ride_the_tailwind_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("ride_the_tailwind_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("twin_twisters_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Twin Twisters
    array("twin_twisters_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("twin_twisters_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),

    array("shatter_yellow", "Class", "Majestic", "Dawnblade", "Battleaxe"), //Shatter
    //blood_on_her_hands_yellow Blood on Her Hands - Not playable in any of our heroes
    array("oath_of_steel_red", "Class", "Rare", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //Oath of Steel
    array("slice_and_dice_red", "Class", "Rare", "Saber", "Dawnblade", "AllWeps"), //Slice and Dice - Sword / Dagger only
    array("slice_and_dice_yellow", "Class", "Rare", "Saber", "Dawnblade", "AllWeps"),
    array("slice_and_dice_blue", "Class", "Rare", "Saber", "Dawnblade", "AllWeps"),
    array("blade_runner_red", "Class", "Common", "Saber", "Hatchet", "AllWeps"), //Blade Runner - 1H weapon
    array("blade_runner_yellow", "Class", "Common", "Saber", "Hatchet", "AllWeps"),
    array("blade_runner_blue", "Class", "Common", "Saber", "Hatchet", "AllWeps"),
    array("in_the_swing_red", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"), //In the Swing
    array("in_the_swing_yellow", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("in_the_swing_blue", "Class", "Common", "Saber", "Dawnblade", "Hatchet", "Battleaxe", "AllWeps"),
    array("outland_skirmish_red", "Class", "Common", "Saber", "Hatchet", "AllWeps"), //Outland Skirmish - 1H weapon
    array("outland_skirmish_yellow", "Class", "Common", "Saber", "Hatchet", "AllWeps"),
    array("outland_skirmish_blue", "Class", "Common", "Saber", "Hatchet", "AllWeps"),

    array("rain_razors_yellow", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Rain Razors
    array("release_the_tension_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Release the Tension - might be worth ommiting due to the AI not being able to play DRs, but it's still a buff, so it stays for now
    array("release_the_tension_yellow", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("release_the_tension_blue", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("fatigue_shot_red", "Class", "Common", "Shiver", "Voltaire", "RedLiner"), //Fatigue Shot
    array("fatigue_shot_yellow", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("fatigue_shot_blue", "Class", "Common", "Shiver", "Voltaire", "RedLiner"),
    array("read_the_glide_path_red", "Class", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Read the Glide Path
    array("read_the_glide_path_yellow", "Class", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("read_the_glide_path_blue", "Class", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),

    array("phoenix_form_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Phoenix Form
    array("spreading_flames_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Searing Emberblade
    array("combustion_point_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Combustion Point
    array("engulfing_flamewave_red", "Class", "Rare", "Emberblade", "Kodachi", "Edge"), //Engulfing Flamewave
    array("engulfing_flamewave_yellow", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("engulfing_flamewave_blue", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("mounting_anger_red", "Class", "Rare", "Emberblade", "Kodachi", "Edge"), //Mounting Anger
    array("mounting_anger_yellow", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("mounting_anger_blue", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("rise_from_the_ashes_red", "Class", "Rare", "Emberblade", "Kodachi", "Edge"), //Rise from the Ashes
    array("rise_from_the_ashes_yellow", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("rise_from_the_ashes_blue", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("brand_with_cinderclaw_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Brand with Cinderclaw
    array("brand_with_cinderclaw_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("brand_with_cinderclaw_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("cinderskin_devotion_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Cinderskin Devotion
    array("cinderskin_devotion_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("cinderskin_devotion_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("dust_runner_outlaw_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Dust Runner Outlaw
    array("dust_runner_outlaw_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("dust_runner_outlaw_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("lava_vein_loyalty_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Lava Vein Loyalty
    array("lava_vein_loyalty_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("lava_vein_loyalty_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("rebellious_rush_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Rebellious Rush
    array("rebellious_rush_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("rebellious_rush_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("rising_resentment_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Rising Resentment
    array("rising_resentment_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("rising_resentment_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("ronin_renegade_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Ronin Renegade
    array("ronin_renegade_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("ronin_renegade_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("soaring_strike_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Soaring Strike
    array("soaring_strike_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("soaring_strike_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),

    array("double_strike_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Double Strike
    array("take_the_tempo_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Take the Tempo
    array("rapid_reflex_red", "Class", "Rare", "Emberblade", "Kodachi", "Edge"), //Rapid Reflex
    array("rapid_reflex_yellow", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("rapid_reflex_blue", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),

    array("buckle_blue", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"), //Buckle
    array("never_yield_blue", "Class", "Majestic", "Anothos", "TitanFist", "Sledge"), //Never Yield
    array("shield_bash_red", "Class", "Rare", "TitanFist"), //Shield Bash - Limited to Titanfist for off-hand synergy
    array("shield_bash_yellow", "Class", "Rare", "TitanFist"),
    array("shield_bash_blue", "Class", "Rare", "TitanFist"),
    array("blessing_of_patience_red", "Class", "Rare", "Anothos", "TitanFist", "Sledge"), //Blessing of Patience
    array("blessing_of_patience_yellow", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("blessing_of_patience_blue", "Class", "Rare", "Anothos", "TitanFist", "Sledge"),
    array("shield_wall_red", "Class", "Rare", "TitanFist"), //Shield Wall - Limited to Titanfist for off-hand synergy, this one honestly could be in all pools but I chose to limit for now
    array("shield_wall_yellow", "Class", "Rare", "TitanFist"),
    array("shield_wall_blue", "Class", "Rare", "TitanFist"),
    array("reinforce_steel_red", "Class", "Rare", "TitanFist"), //Reinforce Steel - Limited to Titanfist for off-hand synergy
    array("reinforce_steel_yellow", "Class", "Rare", "TitanFist"),
    array("reinforce_steel_blue", "Class", "Rare", "TitanFist"),
    array("withstand_red", "Class", "Rare", "TitanFist"), //Withstand - Limited to Titanfist for off-hand synergy
    array("withstand_yellow", "Class", "Rare", "TitanFist"),
    array("withstand_blue", "Class", "Rare", "TitanFist"),

    array("tiger_swipe_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Tiger Swipe
    array("mindstate_of_tiger_blue", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Mindstate of the Tiger
    array("roar_of_the_tiger_yellow", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Roar of the Tiger
    array("flex_claws_red", "Class", "Rare", "Emberblade", "Kodachi", "Edge"), //Flex Claws - Decide whether or not this is included in the pool
    array("flex_claws_yellow", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("flex_claws_blue", "Class", "Rare", "Emberblade", "Kodachi", "Edge"),
    array("pouncing_qi_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Pouncing Qi - Decide whether or not this is included in the pool
    array("pouncing_qi_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("pouncing_qi_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("predatory_streak_red", "Class", "Common", "Emberblade", "Kodachi", "Edge"), //Predatory Streak
    array("predatory_streak_yellow", "Class", "Common", "Emberblade", "Kodachi", "Edge"),
    array("predatory_streak_blue", "Class", "Common", "Emberblade", "Kodachi", "Edge"),

    array("cleave_red", "Class", "Majestic", "Hatchet", "Battleaxe", "AllWeps"), //Cleave - Since there aren't any Ally cards yet, I could see omitting this, but the buff is still playable so I'm keeping it
    array("ironsong_pride_red", "Class", "Majestic"), //Ironsong Ride - Limited to sword backgrounds
    array("blessing_of_steel_red", "Class", "Rare", "Hatchet", "Battleaxe", "AllWeps"), //Blessing of Steel
    array("blessing_of_steel_yellow", "Class", "Rare", "Hatchet", "Battleaxe", "AllWeps"),
    array("blessing_of_steel_blue", "Class", "Rare", "Hatchet", "Battleaxe", "AllWeps"),
    array("precision_press_red", "Class", "Rare", "Hatchet", "Battleaxe", "AllWeps"), //Precision Press
    array("precision_press_yellow", "Class", "Rare", "Hatchet", "Battleaxe", "AllWeps"),
    array("precision_press_blue", "Class", "Rare", "Hatchet", "Battleaxe", "AllWeps"),
    array("puncture_red", "Class", "Common", "Saber", "Dawnblade", "AllWeps"), //Puncture - Swords/Dagger only
    array("puncture_yellow", "Class", "Common", "Saber", "Dawnblade", "AllWeps"),
    array("puncture_blue", "Class", "Common", "Saber", "Dawnblade", "AllWeps"),
    array("felling_swing_red", "Class", "Common", "Hatchet", "Battleaxe", "AllWeps"), //Felling Swing
    array("felling_swing_yellow", "Class", "Common", "Hatchet", "Battleaxe", "AllWeps"),
    array("felling_swing_blue", "Class", "Common", "Hatchet", "Battleaxe", "AllWeps"),
    //visit_the_imperial_forge_red-087 Visit the Imperial Forge - I've decided to omit these, since armor isn't hugely relevant right now and I don't know how the AI can handle piercing. It feels like this card would only be good in an exploitative manner

    array("heat_seeker_red", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //only Heat Seeker. No way to gain aim counters yet. Might reevaluate if we make an aim centric power.

    array("eradicate_yellow", "Class", "Majestic", "Contract", "Stealth", "Reaction"), //Eradicate
    array("leave_no_witnesses_red", "Class", "Majestic", "Contract", "Stealth", "Reaction"), //Leave No Witnesses
    array("regicide_blue", "Class", "Majestic", "Contract", "Stealth", "Reaction"), //Regicide
    array("surgical_extraction_blue", "Class", "Majestic", "Contract", "Stealth", "Reaction"), //Surgical Extraction
    array("pay_day_blue", "Class", "Majestic", "Contract"), //Pay Day
    array("plunder_the_poor_red", "Class", "Rare", "Contract", "Stealth", "Reaction"), //Plunder the Poor
    array("plunder_the_poor_yellow", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("plunder_the_poor_blue", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("rob_the_rich_red", "Class", "Rare", "Contract", "Stealth", "Reaction"), //Rob the Rich
    array("rob_the_rich_yellow", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("rob_the_rich_blue", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("shred_red", "Class", "Rare", "Contract", "Stealth", "Reaction"), //Shred
    array("shred_yellow", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("shred_blue", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("annihilate_the_armed_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Annihilate the Armed
    array("annihilate_the_armed_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("annihilate_the_armed_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("fleece_the_frail_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Fleece the Frail
    array("fleece_the_frail_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("fleece_the_frail_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("nix_the_nimble_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Nix the Nimble
    array("nix_the_nimble_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("nix_the_nimble_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("sack_the_shifty_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Sack the Shifty
    array("sack_the_shifty_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("sack_the_shifty_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("slay_the_scholars_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Slay the Scholars
    array("slay_the_scholars_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("slay_the_scholars_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("cut_to_the_chase_red", "Class", "Common", "Contract"), //Cut to the Chase
    array("cut_to_the_chase_yellow", "Class", "Common", "Contract"),
    array("cut_to_the_chase_blue", "Class", "Common", "Contract"),

    array("amplifying_arrow_yellow", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Amplifying Arrow
    array("melting_point_red", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Melting Point
    array("fletch_a_red_tail_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Fletch a [color] tail
    array("fletch_a_yellow_tail_yellow", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("fletch_a_blue_tail_blue", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("lace_with_bloodrot_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Lace with [infection]
    array("lace_with_frailty_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("lace_with_inertia_red", "Class", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("infecting_shot_red", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"), //Infecting Shot
    array("infecting_shot_yellow", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"),
    array("infecting_shot_blue", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"),
    array("sedation_shot_red", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"), //Sedation Shot
    array("sedation_shot_yellow", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"),
    array("sedation_shot_blue", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"),
    array("withering_shot_red", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"), //Withering Shot
    array("withering_shot_red", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"),
    array("withering_shot_red", "Class", "Rare", "Shiver", "Voltaire", "RedLiner"),

    array("head_leads_the_tail_red", "Class", "Majestic", "Emberblade", "Kodachi", "Edge"), //Head Leads the Tail

    array("infiltrate_red", "Class", "Majestic", "Contract", "Stealth", "Reaction"), //Infiltrate
    array("spreading_plague_yellow", "Class", "Majestic", "Contract", "Stealth", "Reaction"), //Spreading Plague
    array("back_stab_red", "Class", "Rare", "Contract", "Stealth", "Reaction"), //Back Stab
    array("back_stab_yellow", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("back_stab_blue", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    //Sneak Attack is not presently playable by Arakni. Only gains the effects on Uzuri
    array("spike_with_bloodrot_red", "Class", "Rare", "Stealth", "Reaction"), //Spikes
    array("spike_with_frailty_red", "Class", "Rare", "Stealth", "Reaction"),
    array("spike_with_inertia_red", "Class", "Rare", "Stealth", "Reaction"),
    array("infect_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Infect
    array("infect_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("infect_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("isolate_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Isolate
    array("isolate_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("isolate_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("malign_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Malign
    array("malign_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("malign_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("prowl_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Prowl
    array("prowl_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("prowl_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("sedate_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Sedate
    array("sedate_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("sedate_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("wither_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Wither
    array("wither_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("wither_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("razors_edge_red", "Class", "Common", "Stealth", "Reaction"), //Razor's Edge
    array("razors_edge_yellow", "Class", "Common", "Stealth", "Reaction"),
    array("razors_edge_blue", "Class", "Common", "Stealth", "Reaction"),
    array("stab_wound_blue", "Class", "Majestic", "Kodachi", "Contract", "Stealth", "Reaction"), //Stab Wound
    array("concealed_blade_blue", "Class", "Majestic", "Kodachi", "Contract", "Stealth", "Reaction"), //Concealed Blade
    array("knives_out_blue", "Class", "Majestic", "Kodachi", "Contract", "Stealth", "Reaction"), //Knives Out
    array("bleed_out_red", "Class", "Rare", "Kodachi", "Contract", "Stealth", "Reaction"), //Bleed Out
    array("bleed_out_yellow", "Class", "Rare", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("bleed_out_blue", "Class", "Rare", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("hurl_red", "Class", "Rare", "Kodachi", "Contract", "Stealth", "Reaction"), //Hurl
    array("hurl_yellow", "Class", "Rare", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("hurl_blue", "Class", "Rare", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("plunge_red", "Class", "Common", "Kodachi", "Contract", "Stealth", "Reaction"), //Plunge
    array("plunge_yellow", "Class", "Common", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("plunge_blue", "Class", "Common", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("short_and_sharp_red", "Class", "Common", "Kodachi", "Contract", "Stealth", "Reaction"), //Short and Sharp
    array("short_and_sharp_yellow", "Class", "Common", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("short_and_sharp_blue", "Class", "Common", "Kodachi", "Contract", "Stealth", "Reaction"),
    array("codex_of_bloodrot_yellow", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner", "Contract", "Stealth", "Reaction"), //Codexes
    array("codex_of_frailty_yellow", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner", "Contract", "Stealth", "Reaction"),
    array("codex_of_inertia_yellow", "Class", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner", "Contract", "Stealth", "Reaction"),
    array("death_touch_red", "Class", "Rare", "Contract", "Stealth", "Reaction"), //Death Touch
    array("death_touch_yellow", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("death_touch_blue", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("toxicity_red", "Class", "Rare", "Contract", "Stealth", "Reaction"), //Toxicity
    array("toxicity_yellow", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("toxicity_blue", "Class", "Rare", "Contract", "Stealth", "Reaction"),
    array("virulent_touch_red", "Class", "Common", "Contract", "Stealth", "Reaction"), //Virulent Touch
    array("virulent_touch_yellow", "Class", "Common", "Contract", "Stealth", "Reaction"),
    array("virulent_touch_blue", "Class", "Common", "Contract", "Stealth", "Reaction"),
  );

  return ProcessPool($CardRewardPool, $arrayParameters);
}
function GetPoolGeneric($arrayParameters){

  //Currently, Generics are available to all heroes equally.
  //In the future, if we want to have certain generics available to certain heroes, we can go back and tag most cards with "All", and specific ones with the name of that hero
  $CardRewardPool = array(
    array("enlightened_strike_red", "Majestic"), //Enlightened Strike
    array("tome_of_fyendal_yellow", "Majestic"), // Tome of Fyendal
    array("last_ditch_effort_blue", "Majestic"), // Last Ditch Effort
    array("crazy_brew_blue", "Majestic"), // Crazy Brew - Super, but tagged as Majestic
    array("remembrance_yellow", "Majestic"), // Remembrance - Super, but tagged as Majestic
    array("drone_of_brutality_red", "Rare"), //Drone of Brutality
    array("drone_of_brutality_yellow", "Rare"),
    array("drone_of_brutality_blue", "Rare"),
    array("snatch_red", "Rare"), //Snatch
    array("snatch_yellow", "Rare"),
    array("snatch_blue", "Rare"),
    array("energy_potion_blue", "Rare"), //Energy potion
    array("potion_of_strength_blue", "Rare"), //Potion of Strength
    array("timesnap_potion_blue", "Rare"), //Timesnap Potion
    //array("sigil_of_solace_red", "Rare"), //Sigil of Solace - Nate thinks that these shouldn't be in the pool because they're too strong.
    //array("sigil_of_solace_yellow", "Rare"), // Nate, if you're reading this, feel free to remove these from the pool!
    array("sigil_of_solace_blue", "Rare"), // Sigil of Solace (Blue)
    array("barraging_brawnhide_red", "Common"), //Barraging Brawnhide
    array("barraging_brawnhide_yellow", "Common"),
    array("barraging_brawnhide_blue", "Common"),
    array("demolition_crew_red", "Common"), //Demolition Crew
    array("demolition_crew_yellow", "Common"),
    array("demolition_crew_blue", "Common"),
    array("flock_of_the_feather_walkers_red", "Common"), //Flock of the Feather Walkers
    array("flock_of_the_feather_walkers_yellow", "Common"),
    array("flock_of_the_feather_walkers_blue", "Common"),
    array("nimble_strike_red", "Common"), //Nimble Strike
    array("nimble_strike_yellow", "Common"),
    array("nimble_strike_blue", "Common"),
    array("raging_onslaught_red", "Common"), //Raging Onslaught
    array("raging_onslaught_yellow", "Common"),
    array("raging_onslaught_blue", "Common"),
    array("scar_for_a_scar_red", "Common"), //Scar for a Scar - Notably reprinted in UPR, and omitted there
    array("scar_for_a_scar_yellow", "Common"),
    array("scar_for_a_scar_blue", "Common"),
    array("scour_the_battlescape_red", "Common"), //Scour the Battlescape
    array("scour_the_battlescape_yellow", "Common"),
    array("scour_the_battlescape_blue", "Common"),
    array("regurgitating_slog_red", "Common"), //Regurgitating Slog
    array("regurgitating_slog_yellow", "Common"),
    array("regurgitating_slog_blue", "Common"),
    array("wounded_bull_red", "Common"), //Wounded Bull
    array("wounded_bull_yellow", "Common"),
    array("wounded_bull_blue", "Common"),
    array("wounding_blow_red", "Common"), //Wounding Blow
    array("wounding_blow_yellow", "Common"),
    array("wounding_blow_blue", "Common"),
    array("pummel_red", "Common"), //Pummel
    array("pummel_yellow", "Common"),
    array("pummel_blue", "Common"),
    array("razor_reflex_red", "Common"), //Razor Reflex
    array("razor_reflex_yellow", "Common"),
    array("razor_reflex_blue", "Common"),
    array("unmovable_red", "Common"), //Unmovable
    array("unmovable_yellow", "Common"),
    array("unmovable_blue", "Common"),
    array("sink_below_red", "Common"), //Sink Below
    array("sink_below_yellow", "Common"),
    array("sink_below_blue", "Common"),
    array("nimblism_red", "Common"), //Nimblism
    array("nimblism_yellow", "Common"),
    array("nimblism_blue", "Common"),
    array("sloggism_red", "Common"), //Sloggism
    array("sloggism_yellow", "Common"),
    array("sloggism_blue", "Common"),

    array("command_and_conquer_red", "Majestic"), //Command and motherflippin' Conquer
    array("art_of_war_yellow", "Majestic"), //Art of War
    array("pursuit_of_knowledge_blue", "Majestic"), //Pursuit of Knowledge
    array("chains_of_eminence_red", "Majestic"), //Chains of Eminence - Super, but tagged with Majestics
    //rusted_relic_blue - Rusted Relic - No interaction with arcane dmg
    array("life_for_a_life_red", "Rare"), //Life for a life
    array("life_for_a_life_yellow", "Rare"),
    array("life_for_a_life_blue", "Rare"),
    array("enchanting_melody_red", "Rare"), //Enchanting Melody
    array("enchanting_melody_yellow", "Rare"),
    array("enchanting_melody_blue", "Rare"),
    array("plunder_run_red", "Rare"), //Plunder Run
    array("plunder_run_yellow", "Rare"),
    array("plunder_run_blue", "Rare"),
    //eirinas_prayer_red-175 - Eirina's Prayer - No interaction with arcane dmg
    array("back_alley_breakline_red", "Common"), //Back Alley Breakline
    array("back_alley_breakline_yellow", "Common"),
    array("back_alley_breakline_blue", "Common"),
    array("cadaverous_contraband_red", "Common"), //Cadaverous Contraband
    array("cadaverous_contraband_yellow", "Common"),
    array("cadaverous_contraband_blue", "Common"),
    array("fervent_forerunner_red", "Common"), //Fervent Forerunner
    array("fervent_forerunner_yellow", "Common"),
    array("fervent_forerunner_blue", "Common"),
    array("moon_wish_red", "Common"), //Moon Wish
    array("moon_wish_yellow", "Common"),
    array("moon_wish_blue", "Common"),
    array("push_the_point_red", "Common"), //Push the Point
    array("push_the_point_yellow", "Common"),
    array("push_the_point_blue", "Common"),
    array("ravenous_rabble_red", "Common"), //Ravenous Rabble
    array("ravenous_rabble_yellow", "Common"),
    array("ravenous_rabble_blue", "Common"),
    array("rifting_red", "Common"), //Rifting
    array("rifting_yellow", "Common"),
    array("rifting_blue", "Common"),
    array("vigor_rush_red", "Common"), //Vigor Rush
    array("vigor_rush_yellow", "Common"),
    array("vigor_rush_blue", "Common"),
    array("fate_foreseen_red", "Common"), //Fate Foreseen
    array("fate_foreseen_yellow", "Common"),
    array("fate_foreseen_blue", "Common"),
    array("come_to_fight_red", "Common"), //Come to Fight
    array("come_to_fight_yellow", "Common"),
    array("come_to_fight_blue", "Common"),
    array("force_sight_red", "Common"), //Force Sight
    array("force_sight_yellow", "Common"),
    array("force_sight_blue", "Common"),
    array("lead_the_charge_red", "Common"), //Lead the Charge
    array("lead_the_charge_yellow", "Common"),
    array("lead_the_charge_blue", "Common"),
    array("sun_kiss_red", "Common"), //Sun Kiss
    array("sun_kiss_yellow", "Common"),
    array("sun_kiss_blue", "Common"),
    array("whisper_of_the_oracle_red", "Common"), //Whisper of the Oracle
    array("whisper_of_the_oracle_yellow", "Common"),
    array("whisper_of_the_oracle_blue", "Common"),

    array("coax_a_commotion_red", "Majestic"), //Coax a Commotion
    array("gorganian_tome", "Majestic"), //Gorganian Tome
    array("snag_blue", "Majestic"), //Snag
    array("promise_of_plenty_red", "Rare"), //Promise of Plenty
    array("promise_of_plenty_yellow", "Rare"),
    array("promise_of_plenty_blue", "Rare"),
    array("lunging_press_blue", "Rare"), //Lunging Press
    array("springboard_somersault_yellow", "Rare"), //Springboard Assault
    array("cash_in_yellow", "Rare"), //Cash In
    array("reinforce_the_line_red", "Rare"), //Reinforce the Line
    array("reinforce_the_line_yellow", "Rare"),
    array("reinforce_the_line_blue", "Rare"),
    array("brutal_assault_red", "Common"), //Brutal Assault
    array("brutal_assault_yellow", "Common"),
    array("brutal_assault_blue", "Common"),

    array("exude_confidence_red", "Majestic"), //Exude Confidence
    array("nourishing_emptiness_red", "Majestic"), //Nourishing Emptiness
    array("rouse_the_ancients_blue", "Majestic"), //Rouse the Ancients
    array("out_muscle_red", "Rare"), //Out Muscle
    array("out_muscle_yellow", "Rare"),
    array("out_muscle_blue", "Rare"),
    array("seek_horizon_red", "Rare"), //Seek Horizon
    array("seek_horizon_yellow", "Rare"),
    array("seek_horizon_blue", "Rare"),
    array("tremor_of_iarathael_red", "Rare"), //Tremor of iArathael - Honestly this one we might want to take out? I'm leaving it in for now
    array("tremor_of_iarathael_yellow", "Rare"),
    array("tremor_of_iarathael_blue", "Rare"),
    array("rise_above_red", "Rare"), //Rise Above
    array("rise_above_yellow", "Rare"),
    array("rise_above_blue", "Rare"),
    array("captains_call_red", "Rare"), //Captain's Call
    array("captains_call_yellow", "Rare"),
    array("captains_call_blue", "Rare"),
    array("adrenaline_rush_red", "Common"), //Adrenaline Rush
    array("adrenaline_rush_yellow", "Common"),
    array("adrenaline_rush_blue", "Common"),
    array("belittle_red", "Common"), //Belittle
    array("belittle_yellow", "Common"),
    array("belittle_blue", "Common"),
    array("brandish_red", "Common"), //Brandish
    array("brandish_yellow", "Common"),
    array("brandish_blue", "Common"),
    array("frontline_scout_red", "Common"), //Frontline Scout
    array("frontline_scout_yellow", "Common"),
    array("frontline_scout_blue", "Common"),
    array("overload_red", "Common"), //Overload
    array("overload_yellow", "Common"),
    array("overload_blue", "Common"),
    array("pound_for_pound_red", "Common"), //Pound for Pound
    array("pound_for_pound_yellow", "Common"),
    array("pound_for_pound_blue", "Common"),
    array("rally_the_rearguard_red", "Common"), //Rally the Rearguard
    array("rally_the_rearguard_yellow", "Common"),
    array("rally_the_rearguard_blue", "Common"),
    array("stony_woottonhog_red", "Common"), //Stony Woottonhog - The unofficial Roguelike Mascot
    array("stony_woottonhog_yellow", "Common"),
    array("stony_woottonhog_blue", "Common"),
    array("surging_militia_red", "Common"), //Surging Militia
    array("surging_militia_yellow", "Common"),
    array("surging_militia_blue", "Common"),
    array("yinti_yanti_red", "Common"), //Yinti Yanti
    array("yinti_yanti_yellow", "Common"),
    array("yinti_yanti_blue", "Common"),
    array("zealous_belting_red", "Common"), //Zealous Belting
    array("zealous_belting_yellow", "Common"),
    array("zealous_belting_blue", "Common"),
    array("minnowism_red", "Common"), //Minnowism
    array("minnowism_yellow", "Common"),
    array("minnowism_blue", "Common"),
    array("warmongers_recital_red", "Common"), //Warmonger's Recital
    array("warmongers_recital_yellow", "Common"),
    array("warmongers_recital_blue", "Common"),
    //talisman_of_dousing_yellow - Talisman of Dousing - No need for spellvoid
    array("memorial_ground_red", "Common"), //Memorial Ground
    array("memorial_ground_yellow", "Common"),
    array("memorial_ground_blue", "Common"),
    //
    array("bingo_red", "Majestic"), //Bingo
    array("firebreathing_red", "Majestic"), //Firebreathing
    array("cash_out_blue", "Majestic"), //Cash Out
    array("knick_knack_bricabrac_red", "Majestic"), //Knick Knack Bric-a-brac
    array("this_rounds_on_me_blue", "Majestic"), //This Round's on Me
    array("life_of_the_party_red", "Rare"), //Life of the Party
    array("life_of_the_party_yellow", "Rare"),
    array("life_of_the_party_blue", "Rare"),
    //high_striker_red-166 - High Striker - I've decided to omit this one, but if someone wants to add it in feel free
    array("pick_a_card_any_card_red", "Rare"), //Pick a Card, Any Card
    array("pick_a_card_any_card_yellow", "Rare"),
    array("pick_a_card_any_card_blue", "Rare"),
    array("bingo_red", "Rare"), //Smashing Good Time
    array("firebreathing_red", "Rare"),
    array("cash_out_blue", "Rare"),
    array("knick_knack_bricabrac_red", "Rare"), //Even Bigger Than That
    array("this_rounds_on_me_blue", "Rare"),
    array("life_of_the_party_red", "Rare"),
    //EVER176 through EVER193 are all potions.
    //I decided to omit all the potions for now, but feel free to add some/all

    array("erase_face_red", "Majestic"), //Erase Face
    array("vipox_red", "Majestic"), //Vipox
    array("that_all_you_got_yellow", "Majestic"), //That All You Got?
    array("fog_down_yellow", "Majestic"), //Fog Down
    array("flex_red", "Rare"), //Flex
    array("flex_yellow", "Rare"),
    array("flex_blue", "Rare"),
    array("fyendals_fighting_spirit_red", "Rare"), //Fyendal's Fighting Spirit
    array("fyendals_fighting_spirit_yellow", "Rare"),
    array("fyendals_fighting_spirit_blue", "Rare"),
    array("sift_red", "Rare"), //Sift
    array("sift_yellow", "Rare"),
    array("sift_blue", "Rare"),
    array("strategic_planning_red", "Rare"), //Strategic Planning
    array("strategic_planning_yellow", "Rare"),
    array("strategic_planning_blue", "Rare"),
    array("brothers_in_arms_red", "Common"), //Brothers in Arms
    array("brothers_in_arms_yellow", "Common"),
    array("brothers_in_arms_blue", "Common"),
    array("critical_strike_red", "Common"), //Critical Strike
    array("critical_strike_yellow", "Common"),
    array("critical_strike_blue", "Common"),
    //scar_for_a_scar_red-scar_for_a_scar_blue Scar for a Scar - Reprinted from WTR
    array("trade_in_red", "Common"), //Trade In
    array("trade_in_yellow", "Common"),
    array("trade_in_blue", "Common"),
    array("healing_balm_red", "Common"), //Healing Balm
    array("healing_balm_yellow", "Common"),
    array("healing_balm_blue", "Common"),
    array("sigil_of_protection_red", "Common"), //Sigil of Protection
    array("sigil_of_protection_yellow", "Common"),
    array("sigil_of_protection_blue", "Common"),
    array("oasis_respite_red", "Common"), //Oasis Respite
    array("oasis_respite_yellow", "Common"),
    array("oasis_respite_blue", "Common"),

    array("imperial_edict_red", "Majestic"), //Imperial Edict
    array("imperial_ledger_red", "Majestic"), //Imperial Ledger
    array("imperial_warhorn_red", "Majestic"), //Imperial Warhorn
  );

  return ProcessPool($CardRewardPool, $arrayParameters);
}
function GetPoolTalent($arrayParameters){

  $CardRewardPool = array(
    array("flashfreeze_red", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Flashfreeze
    array("entwine_ice_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Entwine Ice
    array("entwine_ice_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("entwine_ice_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("entwine_lightning_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Entwine Lightning
    array("entwine_lightning_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("entwine_lightning_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("invigorate_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Invigorate
    array("invigorate_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("invigorate_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("rejuvenate_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Rejuvenate
    array("rejuvenate_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("rejuvenate_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("pulse_of_volthaven_red", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Pulse of Volthaven
    array("channel_lake_frigid_blue", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Channel Lake Frigid
    array("blizzard_blue", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Blizzard
    array("frost_fang_red", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Frost Fang
    array("frost_fang_yellow", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("frost_fang_blue", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("ice_quake_red", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Ice Quake
    array("ice_quake_yellow", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("ice_quake_blue", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("weave_ice_red", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Weave Ice
    array("weave_ice_yellow", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("weave_ice_blue", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("icy_encounter_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Icy Encounter
    array("icy_encounter_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("icy_encounter_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("winters_grasp_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Winter's Grasp
    array("winters_grasp_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("winters_grasp_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("chill_to_the_bone_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Chill to the Bone
    array("chill_to_the_bone_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("chill_to_the_bone_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("polar_blast_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Polar Blast
    array("polar_blast_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("polar_blast_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("winters_bite_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Winter's Bite
    array("winters_bite_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("winters_bite_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("amulet_of_ice_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Amulet of Ice
    array("channel_thunder_steppe_yellow", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Channel Thunder Steppe
    array("blink_blue", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Blink
    array("flash_red", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Flash
    array("flash_yellow", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("flash_blue", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("weave_lightning_red", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Weave Lightning
    array("weave_lightning_yellow", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("weave_lightning_blue", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("lightning_press_red", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Lightning Press
    array("lightning_press_yellow", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("lightning_press_blue", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("ball_lightning_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Ball Lightning
    array("ball_lightning_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("ball_lightning_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("lightning_surge_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Lightning Surge
    array("lightning_surge_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("lightning_surge_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("heavens_claws_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Heaven's Claws
    array("heavens_claws_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("heavens_claws_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("shock_striker_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Shock Striker
    array("shock_striker_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("shock_striker_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("electrify_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Electrify
    array("electrify_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("electrify_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("amulet_of_lightning_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Amulet of Lightning

    array("channel_the_bleak_expanse_blue", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Channel the Bleak Expanse
    array("hypothermia_blue", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Hypothermia
    array("insidious_chill_blue", "Talent", "Majestic", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Insidious Chill
    array("isenhowl_weathervane_red", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Isenhowl Weathervane
    array("isenhowl_weathervane_yellow", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("isenhowl_weathervane_blue", "Talent", "Rare", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("arctic_incarceration_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Arctic Incarceration
    array("arctic_incarceration_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("arctic_incarceration_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("cold_snap_red", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"), //Cold Snap
    array("cold_snap_yellow", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),
    array("cold_snap_blue", "Talent", "Common", "Shiver", "Voltaire", "DeathDealer", "RedLiner"),

    array("thaw_red", "Talent", "Majestic", "Emberblade", "Kodachi", "Edge"), //Thaw
    array("liquefy_red", "Talent", "Majestic", "Emberblade", "Kodachi", "Edge"), //Liquify
    array("uprising_red", "Talent", "Majestic", "Emberblade", "Kodachi", "Edge"), //Uprising
    array("tome_of_firebrand_red", "Talent", "Majestic", "Emberblade", "Kodachi", "Edge"), //Tome of Firebrand
    array("red_hot_red", "Talent", "Rare", "Emberblade", "Kodachi", "Edge"), //Red Hot
    array("rise_up_red", "Talent", "Rare", "Emberblade", "Kodachi", "Edge"), //Rise Up
    array("blaze_headlong_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Blaze Headlong
    array("breaking_point_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Breaking Point
    array("burn_away_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Burn Away
    array("flameborn_retribution_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Flameborn Retribution
    array("flamecall_awakening_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Flamecall Awakening
    array("inflame_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Inflame
    array("lava_burst_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Lava Burst
    array("searing_touch_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Searing Touch
    array("stoke_the_flames_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Stoke the Flames
    array("phoenix_flame_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Phoenix Flame
    array("phoenix_flame_red", "Talent", "Common", "Emberblade", "Kodachi", "Edge"), //Phoenix Flame - Putting it in twice so it has a higher rate to be pulled in card rewards

  );

  return ProcessPool($CardRewardPool, $arrayParameters);
}

//TODO: Add slot (head, chest etc)
function GetPoolEquipment($arrayParameters){
  $CardRewardPool = array(
  array("tectonic_plating", "Bravo", "Legendary", "Chest"), //Tectonic Plating
  array("helm_of_isens_peak", "Bravo", "Legendary", "Head"), //Helm of Isen's Peak
  array("mask_of_momentum", "Fai", "Legendary", "Head"), //Mask of Momentum
  array("breaking_scales", "Fai", "Common", "Legs"), //Breaking Scales
  array("braveforge_bracers", "Dorinthea", "Legendary", "Arms"), //Braveforge Bracers
  array("refraction_bolters", "Dorinthea", "Common", "Legs"), //Refraction Bolters
  array("fyendals_spring_tunic", "Generic", "Legendary", "Chest"), //Fyendal's Spring Tunic
  array("hope_merchants_hood", "Generic", "Common", "Head"), //Hope Merchant's Hood
  array("heartened_cross_strap", "Generic", "Common", "Chest"), //Heartened Cross Strap
  array("goliath_gauntlet", "Generic", "Common"), //Goliath Gauntlet
  array("snapdragon_scalers", "Generic", "Common", "Legs"), //Snapdragon Scalers
  array("ironrot_helm", "Generic", "Common", "Head"), //Ironrot Helm
  //array("ironrot_plate", "Generic", "Common", "Chest"), //Ironrot Chest - Omitted due to being included in universal equipment
  array("ironrot_gauntlet", "Generic", "Common", "Arms"), //Ironrot Gauntlets
  array("ironrot_legs", "Generic", "Common", "Legs"), //Ironrot Boots

  array("skullbone_crosswrap", "Lexi", "Legendary", "Head"), //Skullbone Crosswrap
  array("bulls_eye_bracers", "Lexi", "Common"), //Bull's Eye Bracers
  array("arcanite_skullcap", "Generic", "Legendary", "Head"), //Arcanite Skullcap
  array("talismanic_lens", "Generic", "Common", "Head"), //Talismanic Lens
  array("vest_of_the_first_fist", "Generic", "Common", "Chest"), //Vest of the First Fist
  array("bracers_of_belief", "Generic", "Common", "Arms"), //Bracers of Belief
  array("mage_master_boots", "Generic", "Common", "Legs"), //Mage Master Boots
  //nullrune_hood - 158 Nullrune Boots omitted due to being included in universal equipment

  array("crater_fist", "Bravo", "Majestic", "Arms"), //Crater Fist
  array("breeze_rider_boots", "Fai", "Majestic", "Legs"), //Breeze Rider Boots
  array("courage_of_bladehold", "Dorinthea", "Majestic", "Chest"), //Courage of Bladehold
  array("perch_grapplers", "Lexi", "Majestic", "Legs"), //Perch Grapplers
  //gamblers_gloves - Omitted due to irrelevance... though there's definitely a world where this is relevant, though maybe not playable.

  array("valiant_dynamo", "Dorinthea", "Legendary", "Legs"), //Valiant Dynamo
  array("gallantry_gold", "Dorinthea", "Common", "Arms"), //Gallantry Gold
  array("blood_drop_brocade", "Generic", "Common", "Chest"), //Blood Drop Brocade
  array("stubby_hammerers", "Generic", "Common", "Arms"), //Stubby Hammerers
  array("time_skippers", "Generic", "Common", "Legs"), //Time Skippers
  array("ironhide_helm", "Generic", "Common", "Head"), //Ironhide Helm
  array("ironhide_plate", "Generic", "Common", "Chest"),
  array("ironhide_gauntlet", "Generic", "Common", "Arms"),
  array("ironhide_legs", "Generic", "Common", "Legs"), //Ironhide Boots

  array("heart_of_ice", "Lexi", "Legendary", "Chest"), //Heart of Ice
  array("coat_of_frost", "Lexi", "Common", "Chest"), //Coat of Frost
  array("shock_charmers", "Lexi", "Legendary", "Arms"), //Shock Charmers
  array("mark_of_lightning", "Lexi", "Common", "Arms"), //Mark of Lightning
  array("rampart_of_the_rams_head", "Bravo", "Legendary", "Offhand"), //Rampart of the Ram's Head
  array("rotten_old_buckler", "Bravo", "Common", "Offhand"), //Rotten Old Buckler
  array("new_horizon", "Lexi", "Legendary", "Head"), //New Horizon
  array("honing_hood", "Lexi", "Common", "Head"), //Honing Hood
  array("ragamuffins_hat", "Generic", "Common", "Head"), //Ragamuffin's Hat
  array("deep_blue", "Generic", "Common", "Chest"), //Deep Blue
  array("cracker_jax", "Generic", "Common"), //Cracker Jax
  array("runaways", "Generic", "Common", "Legs"), //Runaways

  array("earthlore_bounty", "Bravo", "Majestic", "Chest"), //Earthlore Bounty
  array("mask_of_the_pouncing_lynx", "Fai", "Majestic", "Head"), //Mask of the Pouncing Lynx
  array("helm_of_sharp_eye", "Dorinthea", "Majestic", "Head"), //Helm of Sharp Eye
  //arcane_lantern - Arcane Lantern (RARE) - omitted for now. I want to be able to tag the diff between Equips that interact with Arcane and those that don't before I implement the arcane ones.

  array("heat_wave", "Fai", "Common", "Arms"), //Heat Wave
  array("flamescale_furnace", "Fai", "Legendary", "Chest"), //Flamescale Furnace
  array("sash_of_sandikai", "Fai", "Common", "Chest"), //Sash of Sandikai
  array("coronet_peak", "Lexi", "Legendary", "Head"), //Coronet Peak
  array("glacial_horns", "Lexi", "Common", "Head"), //Glacial Horns
  array("tiger_stripe_shuko", "Fai", "Legendary", "Arms"), //Tiger Stripe Shuko
  array("tide_flippers", "Fai", "Common", "Legs"), //Tide Flippers
  array("crown_of_providence", "Generic", "Legendary", "Head"), //Crown of Providence
  array("helios_mitre", "Generic", "Common", "Head"), //Heliod's Mitre - Okay, not technically a common, but I'm okay with it going in the common pool if you are *wink*
  array("quelling_robe", "Generic", "Common", "Chest"), //Quelling Robe
  array("quelling_sleeves", "Generic", "Common", "Arms"), //Quelling Sleeves
  array("quelling_slippers", "Generic", "Common", "Legs"), //Quelling Slippers

  array("seasoned_saviour", "Bravo", "Majestic", "Offhand"), //Seasoned Saviour
  array("steelbraid_buckler", "Bravo", "Rare", "Offhand"), //Steelbraid Buckler
  array("blazen_yoroi", "Fai", "Majestic", "Chest"), //Blazing Yoroi
  array("tearing_shuko", "Fai", "Rare", "Arms"), //Tearing Shuko
  array("blacktek_whisperers", "Arakni", "Legendary", "Legs"), //Blacktek Whisperers
  array("mask_of_perdition", "Arakni", "Majestic", "Head"), //Mask of Perdition
  array("hornets_sting", "Lexi", "Rare", "Arms"), //Hornet's Sting
  //spell_fray_tiara thru 29 - Spellfray equipment. I do want to put these in the pool, but I'd like to tag them as arcane first and put them in my 2nd draft
  array("crown_of_dominion", "Generic", "Legendary", "Head"), //Crown of Dominion
  array("ornate_tessen", "Generic", "Rare", "Offhand"), //Ornate Tessen

  array("redback_shroud", "Arakni", "Legendary", "Chest"), //Redback Shroud
  array("mask_of_many_faces", "Fai", "Common", "Head"), //Mask of Many Faces
  array("trench_of_sunken_treasure", "Lexi", "Legendary", "Chest"), //Trench of Sunken Treasure
  array("wayfinders_crest", "Lexi", "Common", "Head"), //Wayfinder's Crest
  array("flick_knives", "Fai", "Arakni", "Legendary", "Arms"), //Flick Knives
  array("mask_of_shifting_perspectives", "Fai", "Arakni", "Common", "Head"), //Mask of Shifting Perspectives
  array("blade_cuff", "Fai", "Arakni", "Common", "Arms"), //Blade Cuff
  array("mask_of_malicious_manifestations", "Arakni", "Lexi", "Common", "Head"), //Mask of Malicious Manifestations
  array("mask_of_malicious_manifestations", "Arakni", "Lexi", "Common", "Arms"), //Toxic Tips
  array("vambrace_of_determination", "Generic", "Legendary", "Arms"), //Vambrace of Determination
  array("seekers_hood", "Generic", "Common", "Head"), //Seekers
  array("seekers_gilet", "Generic", "Common", "Chest"),
  array("seekers_mitts", "Generic", "Common", "Arms"),
  array("seekers_leggings", "Generic", "Common", "Legs"),
  array("silken_gi", "Generic", "Common", "Chest"), //Silken Gi
  array("threadbare_tunic", "Generic", "Common", "Chest"), //Threadbare Tunic
  array("fisticuffs", "Generic", "Common", "Arms"), //Fisticuffs
  array("fleet_foot_sandals", "Generic", "Common", "Legs"), //Fleet Foot Sandals
  );

  return ProcessPool($CardRewardPool, $arrayParameters);
}

function ProcessPool($CardRewardPool, $arrayOfParameters){

$arrayOfParameters = array_filter($arrayOfParameters);
$arrayOfParameters = array_values($arrayOfParameters);
$returnPool = array(); // Create an empty list of cards to be returned
$sizeParameters = count($arrayOfParameters);
$paramCheck = new SplFixedArray($sizeParameters); //Create a shadow of the parameters...
for ($i = 0; $i < $sizeParameters; $i++){ //... The same length as the list of parameters
  $paramCheck[$i] = false;
}
$eligible = true;
for($i = 0; $i < count($CardRewardPool); $i++){
  $eligible = true;
  for($j = 0; $j < $sizeParameters; $j++){
    $paramCheck[$j] = false;
  }
  for($j = 0; $j < $sizeParameters; $j++){
    for($k = 1; $k < count($CardRewardPool[$i]); $k++){
      if($arrayOfParameters[$j] == $CardRewardPool[$i][$k]){
        $paramCheck[$j] = true;
      }
    }
    if($paramCheck[$j] == false){
      $eligible = false;
      break;
    }
  }
  if($eligible) {
    array_push($returnPool, $CardRewardPool[$i][0]);
  }
}
  return $returnPool;
}

function ArrayAsString($arrayToBeStringed){
  $outString = "";
  for($i = 0; $i < count($arrayToBeStringed); $i++){
    if($i != 0) $outString .= ", ";
    $outString .= $arrayToBeStringed[$i];
  }
  return $outString;
}
