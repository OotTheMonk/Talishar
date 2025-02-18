  <?php

include "Constants.php";
include "CardDictionaries/WelcomeToRathe/WTRShared.php";
include "CardDictionaries/ArcaneRising/ARCShared.php";
include "CardDictionaries/ArcaneRising/ARCGeneric.php";
include "CardDictionaries/ArcaneRising/ARCMechanologist.php";
include "CardDictionaries/ArcaneRising/ARCRanger.php";
include "CardDictionaries/ArcaneRising/ARCRuneblade.php";
include "CardDictionaries/ArcaneRising/ARCWizard.php";
include "CardDictionaries/CrucibleOfWar/CRUShared.php";
include "CardDictionaries/Monarch/MONShared.php";
include "CardDictionaries/Monarch/MONGeneric.php";
include "CardDictionaries/Monarch/MONBrute.php";
include "CardDictionaries/Monarch/MONIllusionist.php";
include "CardDictionaries/Monarch/MONRuneblade.php";
include "CardDictionaries/Monarch/MONWarrior.php";
include "CardDictionaries/Monarch/MONTalent.php";
include "CardDictionaries/TalesOfAria/ELEShared.php";
include "CardDictionaries/TalesOfAria/ELEGuardian.php";
include "CardDictionaries/TalesOfAria/ELERanger.php";
include "CardDictionaries/TalesOfAria/ELERuneblade.php";
include "CardDictionaries/TalesOfAria/ELETalent.php";
include "CardDictionaries/Everfest/EVRShared.php";
include "CardDictionaries/Uprising/UPRShared.php";
include "CardDictionaries/card_names.php";
include "CardDictionaries/PlayAbilities.php";
include "CardDictionaries/HitEffects.php";
include "CardDictionaries/CurrentEffects.php";
include "CardDictionaries/ActivatedAbilities.php";
include "CardDictionaries/Keywords.php";
include "CardDictionaries/ClassicBattles/DVRShared.php";
include "CardDictionaries/ClassicBattles/RVDShared.php";
include "CardDictionaries/Dynasty/DYNShared.php";
include "CardDictionaries/Outsiders/OUTShared.php";
include "CardDictionaries/DuskTillDawn/DTDShared.php";
include "CardDictionaries/Roguelike/ROGUEShared.php";
include "CardDictionaries/FirstStrike/AURShared.php";
include "CardDictionaries/FirstStrike/TERShared.php";
include "CardDictionaries/PartTheMistveil/MSTShared.php";
include "CardDictionaries/ArmoryDecks/AAZShared.php";
include "CardDictionaries/Rosetta/ROSShared.php";
include "CardDictionaries/ArmoryDecks/AIOShared.php";
include "CardDictionaries/ArmoryDecks/AJVShared.php";
include "CardDictionaries/Hunted/HNTShared.php";

include "GeneratedCode/GeneratedCardDictionaries.php";

$CID_BloodRotPox = "bloodrot_pox";
$CID_Frailty = "frailty";
$CID_Inertia = "inertia";

$CID_TekloHead = "teklo_base_head";
$CID_TekloChest = "teklo_base_chest";
$CID_TekloArms = "teklo_base_arms";
$CID_TekloLegs = "teklo_base_legs";

function CardType($cardID, $from="")
{
  global $CS_AdditionalCosts, $currentPlayer;
  if (!$cardID || $cardID == "TRIGGER") return "";
  switch ($cardID) {
    case "parry_blade":
      return "W,E";
    case "thistle_bloom__life_yellow":
    case "arcane_seeds__life_red":
    case "burn_up__shock_red":
    case "pulsing_aether__life_red":
    case "comet_storm__shock_red":
    case "regrowth__shock_blue":
      if($from == "DECK" || $from == "DISCARD" || $from == "BANISH") return "A,I";
      if(function_exists("GetClassState")) {
        if(GetClassState($currentPlayer, $CS_AdditionalCosts) == "Both") return "A,I";
        elseif (IsMeldInstantName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "I";
        elseif (IsMeldActionName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "A";  
      }
      return "A,I";
    case "sanctuary_of_aria":
      return "Macro";
    case "summit_the_unforgiving":
      return "W";
    case "graphene_chelicera":
      return "W,T";
    default:
      break;
  }
  $set = CardSet($cardID);
  if ($set != "ROG" && $set != "DUM") {
    $setID = SetID($cardID);
    $number = intval(substr($setID, 3));
    if ($number < 400) return GeneratedCardType($cardID);
    else if ($set != "MON" && $set != "DYN" && $set != "HNT" && $setID != "UPR551" && $cardID != "teklovossen_the_mechropotent" && $cardID != "teklovossen_the_mechropotentb") return GeneratedCardType($cardID);
  }
  if ($set == "ROG") return ROGUECardType($cardID);
  switch ($cardID) {
    case "MON400":
    case "MON401":
    case "MON402":
      return "E";
    case "the_librarian":
      return "M";
    case "minerva_themis":
      return "M";
    case "lady_barthimont":
    case "lord_sutcliffe":
      return "M";
    case "UPR551":
      return "-";
    case "nitro_mechanoida":
      return "W";
    case "nitro_mechanoidb":
    case "teklovossen_the_mechropotentb":
      return "E";
    case "teklovossen_the_mechropotent":
      return "C";
    case "levia_redeemed":
      return "D";
    case "suraya_archangel_of_knowledge":
      return "-";
    case "DUMMY":
    case "DUMMYDISHONORED":
      return "C";
    case "the_hand_that_pulls_the_strings":
      return "M";
    default:
      return "";
  }
}

function CardTypeExtended($cardID, $from="") // used to handle evos
{
  switch ($cardID) {
    case "evo_steel_soul_memory_blue"://steel soul
    case "evo_steel_soul_processor_blue":
    case "evo_steel_soul_controller_blue":
    case "evo_steel_soul_tower_blue":
    case "evo_steel_soul_memory_blue_equip":
    case "evo_steel_soul_processor_blue_equip":
    case "evo_steel_soul_controller_blue_equip":
    case "evo_steel_soul_tower_blue_equip":
    case "evo_data_mine_yellow"://yellow 2 blocks
    case "evo_battery_pack_yellow":
    case "evo_cogspitter_yellow":
    case "evo_charging_rods_yellow":
    case "evo_data_mine_yellow_equip":
    case "evo_battery_pack_yellow_equip":
    case "evo_cogspitter_yellow_equip":
    case "evo_charging_rods_yellow_equip":
    case "evo_command_center_yellow"://yellow 3 blocks
    case "evo_engine_room_yellow":
    case "evo_smoothbore_yellow":
    case "evo_thruster_yellow":
    case "evo_command_center_yellow_equip":
    case "evo_engine_room_yellow_equip":
    case "evo_smoothbore_yellow_equip":
    case "evo_thruster_yellow_equip":
    case "evo_magneto_blue"://evo magneto
    case "evo_magneto_blue_equip":
      return "A,E";
    case "evo_circuit_breaker_red"://breakers
    case "evo_atom_breaker_red":
    case "evo_face_breaker_red":
    case "evo_mach_breaker_red":
    case "evo_circuit_breaker_red_equip":
    case "evo_atom_breaker_red_equip":
    case "evo_face_breaker_red_equip":
    case "evo_mach_breaker_red_equip":
    case "evo_zoom_call_yellow"://yellow 0 blocks
    case "evo_buzz_hive_yellow":
    case "evo_whizz_bang_yellow":
    case "evo_zip_line_yellow":
    case "evo_zoom_call_yellow_equip":
    case "evo_buzz_hive_yellow_equip":
    case "evo_whizz_bang_yellow_equip":
    case "evo_zip_line_yellow_equip":
    case "evo_recall_blue"://AB evos
    case "evo_heartdrive_blue":
    case "evo_shortcircuit_blue":
    case "evo_speedslip_blue":
    case "evo_recall_blue_equip":
    case "evo_heartdrive_blue_equip":
    case "evo_shortcircuit_blue_equip":
    case "evo_speedslip_blue_equip":
      return "I,E";
    default:
      break;
  }
  return CardType($cardID, $from);
}

function SetID($cardID)
{
  switch ($cardID) {
    case "teklovossen_the_mechropotentb":
    case "nitro_mechanoida":
    case "nitro_mechanoidb":
    case "nitro_mechanoidc":
      return GeneratedSetID(substr($cardID, 0, strlen($cardID) - 1));
    case "the_hand_that_pulls_the_strings":
      return "HNT407";
    default:
      return GeneratedSetID($cardID);
  }
}

function CardSubType($cardID, $uniqueID = -1)
{
  if (!$cardID) return "";
  switch ($cardID) {
    case "sanctuary_of_aria"://Technically false, but helps with Rosetta Limited
      return "Item";
    case "UPR439":
    case "UPR440":
    case "UPR441": //resolved sand cover
      return "Ash";
    case "kiss_of_death_red":
      return "Dagger,Attack";
    default:
      break;
  }
  if ($uniqueID > -1 && (IsModular($cardID) || $cardID == "frostbite")) {
    global $currentTurnEffects;
    for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
      $effectArr = explode("-", $currentTurnEffects[$i]);
      if ($effectArr[0] != "adaptive_plating" && $effectArr[0] != "adaptive_dissolver" && $effectArr[0] != "frostbite") continue;
      $effectArr = explode(",", $effectArr[1]);
      if ($effectArr[0] != $uniqueID) continue;
      if($effectArr[1] == "Base") return $effectArr[2];
      return $effectArr[1];
    }
    return "";
  }
  $set = CardSet($cardID);
  if ($set != "ROG" && $set != "DUM") {
    $setID = SetID($cardID);

    $number = intval(substr($setID, 3));
    if ($number < 400) return GeneratedCardSubtype($cardID);
    else if (
      $set != "MON" && $set != "DYN" && $cardID != "UPR551" && $cardID != "nitro_mechanoidc" && $cardID != "teklovossen_the_mechropotent" && $cardID != "teklovossen_the_mechropotentb" && $set != "MST")
      return GeneratedCardSubtype($cardID);
  }
  if ($set == "ROG") return ROGUECardSubtype($cardID);
  switch ($cardID) {
    case "MON400":
      return "Chest";
    case "MON401":
      return "Arms";
    case "MON402":
      return "Legs";
    case "UPR551":
      return "Ally";
    case "nitro_mechanoidb":
      return "Chest"; // Technically not true, but needed to work.
    case "nitro_mechanoidc":
      return "Item";
    case "suraya_archangel_of_knowledge":
      return "Angel,Ally";
    case "levia_redeemed":
      return "Demon";
    case "teklovossen_the_mechropotent":
      return "Evo";
    case "teklovossen_the_mechropotentb":
      return "Chest,Evo";
    case "MST000_inner_chi_blue":
    case "MST010_inner_chi_blue":
    case "MST032_inner_chi_blue":
    case "MST053_inner_chi_blue":
    case "MST095_inner_chi_blue":
    case "MST096_inner_chi_blue":
    case "MST097_inner_chi_blue":
    case "MST098_inner_chi_blue":
    case "MST099_inner_chi_blue":
    case "MST100_inner_chi_blue":
    case "MST101_inner_chi_blue":
    case "MST102_inner_chi_blue":
      return "Chi";
    case "evo_recall_blue_equip":
      return "Head,Evo";
    case "evo_heartdrive_blue_equip":
      return "Chest,Evo";
    case "evo_shortcircuit_blue_equip":
      return "Arms,Evo";
    case "evo_speedslip_blue_equip":
      return "Legs,Evo";
    default:
      return "";
  }
}

function CharacterHealth($cardID)
{
  $set = CardSet($cardID);
  if ($set != "ROG" && $set != "DUM") return GeneratedCharacterHealth($cardID);
  switch ($cardID) {
    case "DUMMY":
      return 9999;
    case "ROGUE001":
      return 6;
    case "ROGUE003":
      return 8;
    case "ROGUE004":
      return 14;
    case "ROGUE008":
      return 20;
    case "ROGUE006":
      return 14;
    case "ROGUE009":
      return 10;
    case "ROGUE010":
      return 14;
    case "ROGUE013":
      return 14;
    case "ROGUE014":
      return 6;
    case "ROGUE015":
      return 13;
    case "ROGUE016":
      return 8;
    case "ROGUE017":
      return 20;
    case "ROGUE018":
      return 10;
    case "ROGUE019":
      return 18;
    case "ROGUE020":
      return 6;
    case "ROGUE021":
      return 8;
    case "ROGUE022":
      return 10;
    case "ROGUE023":
      return 12;
    case "ROGUE024":
      return 15;
    case "ROGUE025":
      return 20;
    case "ROGUE026":
      return 99;
    case "ROGUE027":
      return 6;
    case "ROGUE028":
      return 14;
    case "ROGUE029":
      return 16;
    case "ROGUE030":
      return 14;
    case "ROGUE031":
      return 16;
    default:
      return 20;
  }
}

function CharacterIntellect($cardID)
{
  $cardID = ShiyanaCharacter($cardID);
  switch ($cardID) {
    case "data_doll_mkii":
      return 3;
    case "teklovossen_the_mechropotent":
      return 3;
    case "ROGUE001":
      return 3;
    case "ROGUE003":
      return 3;
    case "ROGUE004":
      return 3;
    case "ROGUE008":
      return 4;
    case "ROGUE006":
      return 3;
    case "ROGUE009":
      return 3;
    case "ROGUE010":
      return 4;
    case "ROGUE013":
      return 4;
    case "ROGUE014":
      return 3;
    case "ROGUE015":
      return 0;
    case "ROGUE016":
      return 3;
    case "ROGUE017":
      return 0;
    case "ROGUE018":
      return 4;
    case "ROGUE019":
      return 1;
    case "ROGUE020":
      return 3;
    case "ROGUE021":
      return 1;
    case "ROGUE022":
      return 3;
    case "ROGUE023":
      return 3;
    case "ROGUE024":
      return 3;
    case "ROGUE025":
      return 4;
    case "ROGUE026":
      return 3;
    case "ROGUE027":
      return 3;
    case "ROGUE028":
      return 4;
    case "ROGUE029":
      return 4;
    case "ROGUE030":
      return 4;
    case "ROGUE031":
      return 4;
    default:
      return 4;
  }
}

function CardSet($cardID)
{
  if (!$cardID) return "";
  if (substr($cardID, 0, 3) == "ROG") return "ROG";
  if (substr($cardID, 0, 3) == "DUM") return "DUM";
  $setID = SetID(explode("-", $cardID)[0]);
  return substr($setID, 0, 3);
}

function CardClass($cardID)
{
  global $currentPlayer, $CS_AdditionalCosts;
  if (!$cardID) return "";
  switch ($cardID) {
    case "thistle_bloom__life_yellow":
    case "arcane_seeds__life_red":
    case "vaporize__shock_yellow":
    case "burn_up__shock_red":
      if(function_exists("GetClassState")) {
        if (IsMeldInstantName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "NONE";
      }
      return "RUNEBLADE";
    case "rampant_growth__life_yellow":
    case "pulsing_aether__life_red":
    case "null__shock_yellow":
    case "comet_storm__shock_red":
      if(function_exists("GetClassState")) {
        if (IsMeldInstantName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "NONE";
      }
      return "WIZARD";
    case "regrowth__shock_blue":
      if(function_exists("GetClassState")) {
        if (IsMeldInstantName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "NONE";
      }
      return "RUNEBLADE";
    default:
      break;
  }
  $setID = SetID($cardID);
  $number = intval(substr($setID, 3));
  if ($number >= 400) {
    $set = substr($setID, 0, 3);
    switch ($set) {
      case "MON":
        if ($number == 404) return "ILLUSIONIST";
        else if ($number == 405) return "WARRIOR";
        else if ($number == 406) return "BRUTE";
        else if ($number == 407) return "RUNEBLADE";
        else return "NONE";
      case "UPR":
        if ($number >= 406 && $number <= 417) return "ILLUSIONIST";
        else if ($number >= 439 && $number <= 441) return "ILLUSIONIST";
        else if ($number == 551) return "ILLUSIONIST";
        else return "NONE";
    }
  }
  switch ($cardID) {
    case "suraya_archangel_of_knowledge":
      return "ILLUSIONIST";
    case "nitro_mechanoida":
    case "nitro_mechanoidb":
    case "nitro_mechanoidc":
      return "MECHANOLOGIST";
    case "teklovossen_the_mechropotent":
    case "teklovossen_the_mechropotentb":
      return "MECHANOLOGIST";
    default:
      break;
  }
  return GeneratedCardClass($cardID);
}

function CardTalent($cardID, $from="-")
{
  global $currentPlayer, $CS_AdditionalCosts;
  if (!$cardID) return "";
  switch ($cardID) {
    case "thistle_bloom__life_yellow":
    case "arcane_seeds__life_red":
    case "rampant_growth__life_yellow":
    case "pulsing_aether__life_red":
      if(function_exists("GetClassState") && $from == "-") {
        if(IsMeldLeftSideName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "NONE";
        return "EARTH";
      }
      return "EARTH";
    case "vaporize__shock_yellow":
    case "burn_up__shock_red":
    case "null__shock_yellow":
    case "comet_storm__shock_red":
      if(function_exists("GetClassState") && $from == "-") {
        if(IsMeldLeftSideName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "NONE";
        return "LIGHTNING";        
      }
      return "LIGHTNING";
    case "regrowth__shock_blue":
      if(function_exists("GetClassState")) {
        if (IsMeldRightSideName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "LIGHTNING";
        elseif (IsMeldLeftSideName(GetClassState($currentPlayer, $CS_AdditionalCosts))) return "EARTH";
      }
      return "EARTH,LIGHTNING";
    default:
      break;
  }
  $setID = SetID($cardID);
  $set = substr($setID, 0, 3);
  if ($set == "ROG") return ROGUECardTalent($cardID);
  $number = intval(substr($setID, 3));
  if ($number >= 400) {
    switch ($set) {
      case "MON":
        if ($number == 520) return "SHADOW";
        else return "NONE";
      case "UPR":
        if ($number >= 406 && $number <= 417) return "DRACONIC";
        else if ($number >= 439 && $number <= 441) return "DRACONIC";
        else return "NONE";
      case "DYN":
        if ($number == 612) return "LIGHT";
        else return "NONE";
    }
  }
  switch ($cardID) {
    case "teklovossen_the_mechropotent":
    case "teklovossen_the_mechropotentb":
      return "SHADOW";
    case "levia_redeemed":
      return "SHADOW";
    default:
      break;
  }
  return GeneratedCardTalent($cardID);
}

//Minimum cost of the card
function CardCost($cardID, $from="-")
{
  $cardID = ShiyanaCharacter($cardID);
  $set = CardSet($cardID);
  switch ($cardID) {
    case "imposing_visage_blue":
      return 3;
    case "mighty_windup_red":
    case "mighty_windup_yellow":
    case "mighty_windup_blue":
    case "agile_windup_red":
    case "agile_windup_yellow":
    case "agile_windup_blue":
    case "vigorous_windup_red":
    case "vigorous_windup_yellow":
    case "fruits_of_the_forest_blue":
    case "fruits_of_the_forest_yellow":
    case "fruits_of_the_forest_red":
    case "vigorous_windup_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I" && $from == "HAND") return 0;
      return 3;
    case "ripple_away_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I" && $from == "HAND") return 0;
      return 2;
    case "MST000_inner_chi_blue":
    case "MST010_inner_chi_blue":
    case "MST032_inner_chi_blue":
    case "MST053_inner_chi_blue":
    case "MST095_inner_chi_blue":
    case "MST096_inner_chi_blue":
    case "MST097_inner_chi_blue":
    case "MST098_inner_chi_blue":
    case "MST099_inner_chi_blue":
    case "MST100_inner_chi_blue":
    case "MST101_inner_chi_blue":
    case "MST102_inner_chi_blue":
      return -1;
    case "trip_the_light_fantastic_blue":
    case "trip_the_light_fantastic_yellow":
    case "trip_the_light_fantastic_red":
    case "tipoff_red":
    case "tipoff_yellow":
    case "tipoff_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I" && $from == "HAND") return 0;
      return 1;
    case "haunting_rendition_red":
    case "mental_block_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I" && $from == "HAND") return 0;
      return -1;
    case "chorus_of_the_amphitheater_red":
    case "chorus_of_the_amphitheater_yellow":
    case "chorus_of_the_amphitheater_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") return 0;
      return 2;
    case "photon_splicing_red":
    case "photon_splicing_yellow":
    case "photon_splicing_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") return 0;
      return 1;
    case "two_sides_to_the_blade_red":
    case "roiling_fissure_blue":
      return 1;
    default:
      break;
  }
  if ($set != "ROG" && $set != "DUM") {
    return GeneratedCardCost($cardID);
  }
  if ($set == "ROG") return ROGUECardCost($cardID);
}

function AbilityCost($cardID)
{
  global $currentPlayer, $phase;
  $cardID = ShiyanaCharacter($cardID);
  $set = CardSet($cardID);
  $class = CardClass($cardID);
  $subtype = CardSubtype($cardID);
  if ($cardID == "restless_coalescence_yellow") {
    $abilityType = GetResolvedAbilityType($cardID);
    if ($abilityType == "I") return 0;
  }
  if ($class == "ILLUSIONIST" && DelimStringContains($subtype, "Aura")) {
    if (SearchCharacterForCard($currentPlayer, "luminaris")) return 0;
    if (SearchCharacterForCard($currentPlayer, "iris_of_reality")) return 3;
    if (SearchCharacterForCard($currentPlayer, "reality_refractor")) return 2;
  }
  if (SearchCharacterForCard($currentPlayer, "cosmo_scroll_of_ancestral_tapestry") && HasWard($cardID, $currentPlayer) && DelimStringContains($subtype, "Aura")) return 1;

  if (DelimStringContains($subtype, "Dragon") && SearchCharacterActive($currentPlayer, "storm_of_sandikai")) return 0;
  if ($set == "WTR") return WTRAbilityCost($cardID);
  else if ($set == "ARC") return ARCAbilityCost($cardID);
  else if ($set == "CRU") return CRUAbilityCost($cardID);
  else if ($set == "MON") return MONAbilityCost($cardID);
  else if ($set == "ELE") return ELEAbilityCost($cardID);
  else if ($set == "EVR") return EVRAbilityCost($cardID);
  else if ($set == "UPR") return UPRAbilityCost($cardID);
  else if ($set == "DVR") return DVRAbilityCost($cardID);
  else if ($set == "RVD") return RVDAbilityCost($cardID);
  else if ($set == "DYN") return DYNAbilityCost($cardID);
  else if ($set == "OUT") return OUTAbilityCost($cardID);
  else if ($set == "DTD") return DTDAbilityCost($cardID);
  else if ($set == "TCC") return TCCAbilityCost($cardID);
  else if ($set == "EVO") return EVOAbilityCost($cardID);
  else if ($set == "HVY") return HVYAbilityCost($cardID);
  else if ($set == "AKO") return AKOAbilityCost($cardID);
  else if ($set == "MST") return MSTAbilityCost($cardID);
  else if ($set == "ROG") return ROGUEAbilityCost($cardID);
  else if ($set == "ROS") return ROSAbilityCost($cardID);
  else if ($set == "TER") return TERAbilityCost($cardID);
  else if ($set == "AIO") return AIOAbilityCost($cardID);
  else if ($set == "AJV") return AJVAbilityCost($cardID);
  else if ($set == "HNT") return HNTAbilityCost($cardID);
  return 0;
}

function DynamicCost($cardID)
{
  global $currentPlayer;
  switch ($cardID) {
    case "staunch_response_red":
    case "staunch_response_yellow":
    case "staunch_response_blue":
      return "2,6";
    case "spark_of_genius_yellow":
      return "0,2,4,6,8,10,12";
    case "sonata_arcanix_red":
      if(SearchCurrentTurnEffects("bloodsheath_skeleta-NAA", $currentPlayer) || SearchCurrentTurnEffects("bloodsheath_skeleta-AA", $currentPlayer)) {
        return "0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58,60,62,64,66,68,70,72,74,76,78,80,82,84,86,88,90,92,94,96,98,100,102,104,106,108,110";
      }
      return "0,2,4,6,8,10,12,14,16,18,20";
    case "imposing_visage_blue":
      return "3,4,5,6,7,8,9,10,11,12,13,14,15";
    case "scour_blue":
      $myAurasCount = SearchCount(SearchAura($currentPlayer, "", "", 0));
      $otherPlayerAurasCount = SearchCount(SearchAura(($currentPlayer == 1 ? 2 : 1), "", "", 0));
      return $myAurasCount > $otherPlayerAurasCount ? GetIndices(SearchCount(SearchAura($currentPlayer, "", "", 0)) + 1) : GetIndices(SearchCount(SearchAura(($currentPlayer == 1 ? 2 : 1), "", "", 0)) + 1);
    case "ice_eternal_blue":
      return "0,2,4,6,8,10,12,14,16,18,20";
    case "hyper_scrapper_blue":
      return "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20";
    case "moonshot_yellow":
      return "0,2,4,6,8,10,12,14,16,18,20";
    case "meganetic_lockwave_blue":
      return "0,3,6,9,12,15,18,21,24,27,30";
    case "system_reset_yellow":
      return "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20";
    case "tectonic_rift_blue":
      return "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";
    case "sonata_fantasmia_blue":
      if(SearchCurrentTurnEffects("bloodsheath_skeleta-NAA", $currentPlayer) || SearchCurrentTurnEffects("bloodsheath_skeleta-AA", $currentPlayer)) {
        return "0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58,60,62,64,66,68,70,72,74,76,78,80,82,84,86,88,90,92,94,96,98,100,102,104,106,108,110";
      }
      return "0,2,4,6,8,10,12,14,16,18,20";
    case "up_the_ante_blue":
      return "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";
    case "reel_in_blue":
      return "0,1,2,3,4,5,6,7,8,9,10,11,12";
    case "sonata_galaxia_red":
      if(SearchCurrentTurnEffects("bloodsheath_skeleta-NAA", $currentPlayer) || SearchCurrentTurnEffects("bloodsheath_skeleta-AA", $currentPlayer)) {
        return "0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58,60,62,64,66,68,70,72,74,76,78,80,82,84,86,88,90,92,94,96,98,100,102,104,106,108,110";
      }
      return "0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58,60";
    case "supercell_blue":
      return GetIndices(SearchCount(SearchItemsByName($currentPlayer, "Hyper Driver")) + 1);
    case "germinate_blue":
      if(SearchCurrentTurnEffects("bloodsheath_skeleta-NAA", $currentPlayer) || SearchCurrentTurnEffects("bloodsheath_skeleta-AA", $currentPlayer)) {
        return "0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58,60,62,64,66,68,70,72,74,76,78,80,82,84,86,88,90,92,94,96,98,100,102,104,106,108,110";
      }
      return "0,2,4,6,8,10,12,14,16,18,20";
    case "roiling_fissure_blue":
      return "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";  
    default:
      return "";
  }
}

function PitchValue($cardID)
{
  if (!$cardID) return "";
  $set = CardSet($cardID);
  if (CardType($cardID) == "M" || CardSubType($cardID) == "Landmark") return -1;
  switch ($cardID) {
    case "MST000_inner_chi_blue":
    case "MST010_inner_chi_blue":
    case "MST032_inner_chi_blue":
    case "MST053_inner_chi_blue":
    case "MST095_inner_chi_blue":
    case "MST096_inner_chi_blue":
    case "MST097_inner_chi_blue":
    case "MST098_inner_chi_blue":
    case "MST099_inner_chi_blue":
    case "MST100_inner_chi_blue":
    case "MST101_inner_chi_blue":
    case "MST102_inner_chi_blue":
      return 3;
    default:
      break;
  }
  if ($set == "LGS") {
    switch ($cardID) {
      case "jackolantern_yellow":
        return 2;
      case "jackolantern_blue":
        return 3;
      default:
        break;
    }
  }
  if ($set != "ROG" && $set != "DUM") {
    return GeneratedPitchValue($cardID);
  }
  if ($set == "ROG") return ROGUEPitchValue($cardID);
}

function BlockValue($cardID)
{
  global $defPlayer;
  if (!$cardID) return "";
  $set = CardSet($cardID);
  if ($cardID == "mutated_mass_blue") return SearchPitchForNumCosts($defPlayer) * 2;
  if ($cardID == "fractal_replication_red") return FractalReplicationStats("Block");
  if ($cardID == "arcanite_fortress") return SearchCount(SearchMultiZone($defPlayer, "MYCHAR:type=E;nameIncludes=Arcanite"));
  if ($set != "ROG" && $set != "DUM") {
    $setID = SetID($cardID);
    $number = intval(substr($setID, 3));
    if ($number < 400 || ($set != "MON" && $set != "DYN" && $set != "MST" && $set != "HNT" && $cardID != "teklovossen_the_mechropotent" && $cardID != "teklovossen_the_mechropotentb")) return GeneratedBlockValue($cardID);
  }
  if ($set == "ROG") return ROGUEBlockValue($cardID);
  switch ($cardID) {
    case "MON400":
    case "MON401":
    case "MON402":
      return 0;
    case "the_librarian":
    case "minerva_themis":
    case "lady_barthimont":
    case "lord_sutcliffe":
      return 3;
    case "nitro_mechanoida":
      return -1;
    case "nitro_mechanoidb":
      return 5;
    case "teklovossen_the_mechropotent":
      return -1;
    case "teklovossen_the_mechropotentb":
      return 6;
    case "DUMMYDISHONORED":
      return -1;
    case "MST000_inner_chi_blue":
    case "MST010_inner_chi_blue":
    case "MST032_inner_chi_blue":
    case "MST053_inner_chi_blue":
    case "MST095_inner_chi_blue":
    case "MST096_inner_chi_blue":
    case "MST097_inner_chi_blue":
    case "MST098_inner_chi_blue":
    case "MST099_inner_chi_blue":
    case "MST100_inner_chi_blue":
    case "MST101_inner_chi_blue":
    case "MST102_inner_chi_blue":
      return -1;
    case "evo_recall_blue_equip":
    case "evo_heartdrive_blue_equip":
    case "evo_shortcircuit_blue_equip":
    case "evo_speedslip_blue_equip":
      return 0;
    case "the_hand_that_pulls_the_strings":
      return 4;
    default:
      return 3;
  }
}

function AttackValue($cardID, $index=-1, $base=false)
{
  global $mainPlayer, $currentPlayer, $CS_NumNonAttackCards, $CS_Num6PowDisc, $CS_NumAuras, $CS_NumCardsDrawn;
  if (!$cardID) return "";
  $set = CardSet($cardID);
  $class = CardClass($cardID);
  $subtype = CardSubtype($cardID);
  $defPlayer = $mainPlayer == 1 ? 2 : 1;

  //Only weapon that gains power, NOT on their attack
  if (!$base) {
    if ($cardID == "anothos") return SearchCount(SearchPitch($mainPlayer, minCost: 3)) >= 2 ? 6 : 4;
    if ($cardID == "nebula_blade") return GetClassState($mainPlayer, $CS_NumNonAttackCards) > 0 ? 4 : 1;
    if ($cardID == "titans_fist") return SearchCount(SearchPitch($mainPlayer, minCost: 3)) >= 1 ? 4 : 3;
    if ($cardID == "hammer_of_havenhold") return SearchPitchForCard($mainPlayer, "chivalry_blue") > -1 ? 4 : 3;
    if ($cardID == "ball_breaker") return GetClassState($mainPlayer, $CS_Num6PowDisc) >= 1 ? 4 : 3;
    if ($cardID == "high_riser") return GetClassState($mainPlayer, $CS_NumCardsDrawn) >= 1 ? 4 : 3;
    if ($cardID == "rotwood_reaper") return (GetClassState($mainPlayer, $CS_NumAuras) > 0 ? 4 : 2);
    if ($cardID == "mark_of_the_huntsman") {
      if (!IsHeroAttackTarget()) return 1;
      else return CheckMarked($defPlayer) ? 2 : 1;
    }
  }
  if ($class == "ILLUSIONIST" && DelimStringContains($subtype, "Aura")) {
    if (SearchCharacterForCard($mainPlayer, "luminaris")) return 1;
    if (SearchCharacterForCard($mainPlayer, "iris_of_reality")) return 4;
    if (SearchCharacterForCard($mainPlayer, "reality_refractor")) return 5;
    if (SearchCharacterForCard($mainPlayer, "cosmo_scroll_of_ancestral_tapestry")) {
      if ($index != -1) {
        return WardAmount($cardID, $mainPlayer, $index);
      }
    }
  }
  if ($cardID == "mutated_mass_blue") return SearchPitchForNumCosts($mainPlayer) * 2;
  else if ($cardID == "fractal_replication_red") return FractalReplicationStats("Attack");
  else if ($cardID == "spectral_procession_red") return CountAura("spectral_shield", $currentPlayer);
  if ($set != "ROG" && $set != "DUM") {
    $setID = SetID($cardID);
    $number = intval(substr($setID, 3));
    if ($number < 400 || ($set != "MON" && $set != "DYN"))
    return GeneratedAttackValue($cardID);
  }
  if ($set == "ROG") return ROGUEAttackValue($cardID);
  switch ($cardID) {
    case "nitro_mechanoida":
      return 5;
    case "suraya_archangel_of_knowledge":
      return 4;
    case "teklovossen_the_mechropotent":
      return 6;
    default:
      return 0;
  }
}

function HasGoAgain($cardID): bool|int
{
  switch ($cardID) {
    case "beckoning_mistblade":
    case "shifting_winds_of_the_mystic_beast_blue":
    case "wind_chakra_red":
    case "wind_chakra_yellow":
    case "wind_chakra_blue":
    case "companion_of_the_claw_red":
    case "companion_of_the_claw_yellow":
    case "companion_of_the_claw_blue":
    case "harmony_of_the_hunt_red":
    case "harmony_of_the_hunt_yellow":
    case "harmony_of_the_hunt_blue":
    case "tiger_form_incantation_red":
    case "tiger_form_incantation_yellow":
    case "tiger_form_incantation_blue":
    case "first_tenet_of_chi_moon_blue":
    case "first_tenet_of_chi_tide_blue":
    case "first_tenet_of_chi_wind_blue":
    case "spectral_manifestations_red":
    case "spectral_manifestations_yellow":
    case "spectral_manifestations_blue":
    case "tiger_taming_khakkara":
    case "biting_breeze_red":
    case "biting_breeze_yellow":
    case "biting_breeze_blue":
    case "untamed_red":
    case "untamed_yellow":
    case "untamed_blue":
    case "prismatic_leyline_yellow":
    case "water_the_seeds_red":
    case "water_the_seeds_yellow":
    case "water_the_seeds_blue":
    case "line_it_up_yellow":
    case "arcane_seeds__life_red":
    case "burn_up__shock_red":
    case "heartbeat_of_candlehold_blue":
    case "channel_the_millennium_tree_red":
    case "earths_embrace_blue":
    case "fry_red":
    case "fry_yellow":
    case "fry_blue":
    case "sizzle_red":
    case "sizzle_yellow":
    case "flourish_yellow":
    case "thrive_yellow":
    case "flourish_blue":
    case "arc_lightning_yellow":
    case "harvest_season_red":
    case "harvest_season_yellow":
    case "harvest_season_blue":
    case "strong_yield_red":
    case "strong_yield_yellow":
    case "strong_yield_blue":
    case "sigil_of_earth_blue":
    case "condemn_to_slaughter_red":
    case "machinations_of_dominion_blue":
    case "succumb_to_temptation_yellow":
    case "condemn_to_slaughter_yellow":
    case "condemn_to_slaughter_blue":
    case "malefic_incantation_red":
    case "malefic_incantation_yellow":
    case "malefic_incantation_blue":
    case "sigil_of_the_arknight_blue":
    case "arcane_cussing_red":
    case "arcane_cussing_yellow":
    case "arcane_cussing_blue":
    case "deadwood_dirge_red":
    case "deadwood_dirge_yellow":
    case "deadwood_dirge_blue":
    case "sigil_of_deadwood_blue":
    case "exploding_aether_red":
    case "exploding_aether_yellow":
    case "exploding_aether_blue":
    case "call_to_the_grave_blue":
    case "sigil_of_cycles_blue":
    case "sigil_of_fyendal_blue":
    case "unsheathed_red":
    case "regrowth__shock_blue":
    case "heavy_industry_power_plant":
    case "channel_mount_isen_blue":
    case "hunters_klaive":
    case "mark_of_the_huntsman":
    case "orbweaver_spinneret_red":
    case "orbweaver_spinneret_yellow":
    case "orbweaver_spinneret_blue":
    case "kunai_of_retribution":
    case "ignite_red":
    case "wrath_of_retribution_red":
    case "blood_drop_red":
    case "blood_line_red":
    case "blood_runs_deep_red":
    case "devotion_never_dies_red":
    case "fire_tenet_strike_first_red":
    case "fire_tenet_strike_first_yellow":
    case "fire_tenet_strike_first_blue":
    case "grow_claws_red":
    case "grow_claws_yellow":
    case "grow_claws_blue":
    case "tag_the_target_red":
    case "tag_the_target_yellow":
    case "tag_the_target_blue":
    case "trap_and_release_red":
    case "trap_and_release_yellow":
    case "trap_and_release_blue":
    case "sharpened_senses_yellow":
    case "twist_and_turn_red":
    case "twist_and_turn_yellow":
    case "twist_and_turn_blue":
    case "agility_stance_yellow":
    case "flurry_stance_red":
    case "power_stance_blue":
    case "cut_deep_red":
    case "cut_deep_yellow":
    case "cut_deep_blue":
    case "hunt_a_killer_red":
    case "hunt_a_killer_yellow":
    case "hunt_a_killer_blue":
    case "knife_through_butter_red":
    case "knife_through_butter_yellow":
    case "knife_through_butter_blue":
    case "point_of_engagement_red":
    case "point_of_engagement_yellow":
    case "point_of_engagement_blue":
    case "sworn_vengeance_red":
    case "sworn_vengeance_yellow":
    case "sworn_vengeance_blue":
    case "coat_of_allegiance":
    case "oath_of_loyalty_red":
    case "hunt_to_the_ends_of_rathe_red":
    case "pain_in_the_backside_red":
    case "up_sticks_and_run_red":
    case "up_sticks_and_run_yellow":
    case "up_sticks_and_run_blue":
    case "pick_up_the_point_red":
    case "pick_up_the_point_yellow":
    case "pick_up_the_point_blue":
    case "poisoned_blade_red":
    case "poisoned_blade_yellow":
    case "poisoned_blade_blue":
    case "throw_yourself_at_them_red":
    case "throw_yourself_at_them_yellow":
    case "throw_yourself_at_them_blue":
    case "savor_bloodshed_red":
    case "cut_from_the_same_cloth_red":
    case "cut_from_the_same_cloth_yellow":
    case "cut_from_the_same_cloth_blue":
    case "dual_threat_yellow":
    case "relentless_pursuit_blue":
    case "outed_red":
    case "trot_along_blue":
    case "public_bounty_red":
    case "public_bounty_yellow":
    case "public_bounty_blue":
    case "roiling_fissure_blue":
    case "spur_locked_blue":
      return true;
    case "war_cry_of_themis_yellow":
      return GetResolvedAbilityType($cardID) == "A";
  }
  $set = CardSet($cardID);
  if ($set == "ROG") return ROGUEHasGoAgain($cardID);
  else return GeneratedGoAgain($cardID);
}

function GetAbilityType($cardID, $index = -1, $from = "-")
{
  global $currentPlayer, $mainPlayer;
  $cardID = ShiyanaCharacter($cardID);
  $set = CardSet($cardID);
  $subtype = CardSubtype($cardID);
  if ($from == "PLAY" && ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && DelimStringContains($subtype, "Aura")) {
    if (SearchCharacterForCard($currentPlayer, "luminaris") || SearchCharacterForCard($currentPlayer, "iris_of_reality") || SearchCharacterForCard($currentPlayer, "reality_refractor")) return "AA";
  }
  if ($from == "PLAY" && DelimStringContains($subtype, "Aura") && SearchCharacterForCard($currentPlayer, "cosmo_scroll_of_ancestral_tapestry") && HasWard($cardID, $currentPlayer) && $currentPlayer == $mainPlayer) return "AA";
  if (DelimStringContains($subtype, "Dragon") && SearchCharacterActive($currentPlayer, "storm_of_sandikai")) return "AA";
  if ($set == "WTR") return WTRAbilityType($cardID, $index, $from);
  else if ($set == "ARC") return ARCAbilityType($cardID, $index);
  else if ($set == "CRU") return CRUAbilityType($cardID, $index);
  else if ($set == "MON") return MONAbilityType($cardID, $index, $from);
  else if ($set == "ELE") return ELEAbilityType($cardID, $index, $from);
  else if ($set == "EVR") return EVRAbilityType($cardID, $index, $from);
  else if ($set == "UPR") return UPRAbilityType($cardID, $index);
  else if ($set == "DVR") return DVRAbilityType($cardID);
  else if ($set == "RVD") return RVDAbilityType($cardID);
  else if ($set == "DYN") return DYNAbilityType($cardID, $index);
  else if ($set == "OUT") return OUTAbilityType($cardID, $index);
  else if ($set == "DTD") return DTDAbilityType($cardID, $index);
  else if ($set == "TCC") return TCCAbilityType($cardID, $index);
  else if ($set == "EVO") return EVOAbilityType($cardID, $index, $from);
  else if ($set == "HVY") return HVYAbilityType($cardID, $index, $from);
  else if ($set == "AKO") return AKOAbilityType($cardID, $index, $from);
  else if ($set == "MST") return MSTAbilityType($cardID, $index, $from);
  else if ($set == "AAZ") return AAZAbilityType($cardID, $index, $from);
  else if ($set == "ROG") return ROGUEAbilityType($cardID, $index);
  else if ($set == "ROS") return ROSAbilityType($cardID);
  else if ($set == "ASB") return ASBAbilityType($cardID, $index);
  else if ($set == "TER") return TERAbilityType($cardID);
  else if ($set == "AIO") return AIOAbilityType($cardID, $index, $from);
  else if ($set == "AJV") return AJVAbilityType($cardID);
  else if ($set == "HNT") return HNTAbilityType($cardID);
  else if ($cardID == "blaze_firemind") return "I";
  else if ($cardID == "magrar") return "A";
}

function GetAbilityTypes($cardID, $index = -1, $from = "-"): string
{
  return match ($cardID) {
    "teklo_plasma_pistol", "jinglewood_smash_hit", "plasma_barrel_shot" => "A,AA",
    "barbed_castaway" => "I,I",
    "mighty_windup_red", "mighty_windup_yellow", "mighty_windup_blue", "agile_windup_red", "agile_windup_yellow", "agile_windup_blue", "vigorous_windup_red", "vigorous_windup_yellow", "vigorous_windup_blue", "restless_coalescence_yellow", 
    "trip_the_light_fantastic_red", "trip_the_light_fantastic_yellow", "trip_the_light_fantastic_blue", "fruits_of_the_forest_red", "fruits_of_the_forest_yellow", "fruits_of_the_forest_blue", "ripple_away_blue", "under_the_trapdoor_blue", "reapers_call_red", "reapers_call_yellow", "reapers_call_blue",
    "tipoff_red", "tipoff_yellow", "tipoff_blue" => "I,AA",
    "chorus_of_the_amphitheater_red", "chorus_of_the_amphitheater_yellow", "chorus_of_the_amphitheater_blue", "arcane_twining_red", "arcane_twining_yellow", "arcane_twining_blue", "photon_splicing_red", "photon_splicing_yellow", "photon_splicing_blue", "war_cry_of_themis_yellow" => "I,A",
    "haunting_rendition_red", "mental_block_blue" => "B,I",
    "shelter_from_the_storm_red" => "I,DR",
    "war_cry_of_bellona_yellow" => "I,AR",
    default => "",
  };
}

function GetAbilityNames($cardID, $index = -1, $from = "-"): string
{
  global $currentPlayer, $mainPlayer, $combatChain, $layers, $actionPoints, $CS_PlayIndex, $CS_NumActionsPlayed, $CS_NextWizardNAAInstant, $combatChainState, $CCS_EclecticMag;
  global $defPlayer;
  $character = &GetPlayerCharacter($currentPlayer);
  $auras = &GetAuras($currentPlayer);
  $names = "";
  if ($index == -1) $index = GetClassState($currentPlayer, $CS_PlayIndex);
  switch ($cardID) {
    case "teklo_plasma_pistol":
    case "plasma_barrel_shot":
      if ($index == -1) return "";
      $rv = "Add_a_steam_counter";
      if ($character[$index + 2] > 0 && !SearchCurrentTurnEffects("kabuto_of_imperial_authority", $mainPlayer)) $rv .= ",Attack";
      return $rv;
    case "barbed_castaway":
      if(!SearchCurrentTurnEffects("barbed_castaway-Load", $currentPlayer)) return "Aim";
      if(!SearchCurrentTurnEffects("barbed_castaway-Aim", $currentPlayer)) return "Load";
      return "Load,Aim";
    case "jinglewood_smash_hit":
      if ($index == -1) return "";
      return "Create_tokens,Smash_Jinglewood";
    case "mighty_windup_red":
    case "mighty_windup_yellow":
    case "mighty_windup_blue":
    case "agile_windup_red":
    case "agile_windup_yellow":
    case "agile_windup_blue":
    case "vigorous_windup_red":
    case "vigorous_windup_yellow":
    case "vigorous_windup_blue":
    case "trip_the_light_fantastic_red":
    case "trip_the_light_fantastic_yellow":
    case "trip_the_light_fantastic_blue":
    case "fruits_of_the_forest_red":
    case "fruits_of_the_forest_yellow":
    case "fruits_of_the_forest_blue":
    case "ripple_away_blue":
    case "under_the_trapdoor_blue":
    case "reapers_call_red":
    case "reapers_call_yellow":
    case "reapers_call_blue":
    case "tipoff_red":
    case "tipoff_yellow":
    case "tipoff_blue":
      $names = "Ability";
      if ($currentPlayer == $mainPlayer && count($combatChain) == 0 && count($layers) <= LayerPieces() && $actionPoints > 0){
        $warmongersPeace = SearchCurrentTurnEffects("WarmongersPeace", $currentPlayer);
        $underEdict = SearchCurrentTurnEffects("imperial_edict_red-" . GamestateSanitize(CardName($cardID)), $currentPlayer);
        if (!$warmongersPeace && !$underEdict) {
          if (!SearchCurrentTurnEffects("oath_of_loyalty_red", $currentPlayer) || SearchCurrentTurnEffects("fealty", $currentPlayer)) $names .= ",Attack";
        }
      }
      return $names;
    case "haunting_rendition_red": case "mental_block_blue":
      return "Block,Ability";
    case "restless_coalescence_yellow":
      if ($auras[$index + 3] > 0) $names = "Instant";
      if (SearchCurrentTurnEffects("red_in_the_ledger_red", $currentPlayer) && GetClassState($currentPlayer, $CS_NumActionsPlayed) >= 1) {
        return $names;
      } else if ($currentPlayer == $mainPlayer && count($combatChain) == 0 && count($layers) <= LayerPieces() && $actionPoints > 0 && $auras[$index + 1] == 2 && !SearchCurrentTurnEffects("kabuto_of_imperial_authority", $currentPlayer)) {
        $names != "" ? $names .= ",Attack" : $names = "-,Attack";
      }
      return $names;
    case "chorus_of_the_amphitheater_red":
    case "chorus_of_the_amphitheater_yellow":
    case "chorus_of_the_amphitheater_blue":
    case "arcane_twining_red":
    case "arcane_twining_yellow":
    case "arcane_twining_blue":
    case "photon_splicing_red":
    case "photon_splicing_yellow":
    case "photon_splicing_blue":
    case "war_cry_of_themis_yellow":
      $names = "Ability";
      if(GetClassState($currentPlayer, $CS_NextWizardNAAInstant)) $names .= ",Action";
      elseif($combatChainState[$CCS_EclecticMag]) $names .= ",Action";
      elseif($currentPlayer == $mainPlayer && count($combatChain) == 0 && count($layers) <= LayerPieces() && $actionPoints > 0) $names .= ",Action";
      if($from != "HAND") $names = "-,Action";
      return $names;
    case "shelter_from_the_storm_red":
      $names = "Ability";
      $dominateRestricted = $from == "HAND" && CachedDominateActive() && CachedNumDefendedFromHand() >= 1 && NumDefendedFromHand() >= 1;
      $effectRestricted = $from == "HAND" && !IsDefenseReactionPlayable($cardID, $from);
      if ($from != "HAND") $names = "-,Defense Reaction";
      elseif ($currentPlayer == $defPlayer && count($combatChain) > 0 && !$dominateRestricted && !$effectRestricted && IsReactionPhase()) $names .= ",Defense Reaction";
      return $names;
    case "war_cry_of_bellona_yellow":
      $names = "Ability";
      $hasRaydn = false;//CardNameContains($combatChain[0], "Raydn", $mainPlayer, true);
      $char = GetPlayerCharacter($currentPlayer);
      for ($i = 0; $i < count($char); $i += CharacterPieces()) {
        if (CardNameContains($char[$i], "Raydn", $currentPlayer)) $hasRaydn = true;
      }
      if ($from != "HAND") $names = "-,Attack Reaction";
      elseif ($currentPlayer == $mainPlayer && count($combatChain) > 0 && IsReactionPhase() && $hasRaydn) $names .= ",Attack Reaction";
      return $names;
    default:
      return "";
  }
}

function GetAbilityIndex($cardID, $index, $abilityName)
{
  $names = explode(",", GetAbilityNames($cardID, $index));
  $abilityName = GamestateUnsanitize($abilityName);
  $countNames = count($names);
  for ($i = 0; $i < $countNames; ++$i) {
    if ($abilityName == $names[$i]) return $i;
  }
  return 0;
}

function GetResolvedAbilityType($cardID, $from = "-")
{
  global $currentPlayer, $CS_AbilityIndex;

  $abilityIndex = GetClassState($currentPlayer, $CS_AbilityIndex);
  $abilityTypes = GetAbilityTypes($cardID, from: $from);
  if ($abilityTypes == "" || $abilityIndex == "-") return GetAbilityType($cardID, -1, $from);
  $abilityTypes = explode(",", $abilityTypes);
  if (isset($abilityTypes[$abilityIndex])) {
    return $abilityTypes[$abilityIndex];
  } else return "";
}

function GetResolvedAbilityName($cardID, $from = "-"): string
{
  global $currentPlayer, $CS_AbilityIndex;
  $abilityIndex = GetClassState($currentPlayer, $CS_AbilityIndex);
  $abilityNames = GetAbilityNames($cardID, -1, $from);
  if ($abilityNames == "" || $abilityIndex == "-") return "";
  $abilityNames = explode(",", $abilityNames);
  if (isset($abilityNames[$abilityIndex])) return $abilityNames[$abilityIndex];
  else return "";
}

function IsPlayable($cardID, $phase, $from, $index = -1, &$restriction = null, $player = "", $pitchRestriction = ""): bool
{
  global $currentPlayer, $CS_NumActionsPlayed, $combatChainState, $CCS_BaseAttackDefenseMax, $CS_NumNonAttackCards, $CS_NumAttackCards;
  global $CCS_ResourceCostDefenseMin, $CCS_CardTypeDefenseRequirement, $actionPoints, $mainPlayer, $defPlayer, $CCS_NumUsedInReactions;
  global $CombatChain, $combatChain, $layers;
  if ($player == "") $player = $currentPlayer;
  $otherPlayer = $player == 1 ? 2 : 1;
  $myArsenal = &GetArsenal($player);
  $myAllies = &GetAllies($player);
  $character = &GetPlayerCharacter($player);
  $myHand = &GetHand($player);
  $banish = new Banish($player);
  $auras = &GetAuras($player);
  $discard = &GetDiscard($currentPlayer);
  $restriction = "";
  $cardType = CardType($cardID);
  $subtype = CardSubType($cardID);
  $abilityType = GetAbilityType($cardID, $index, $from);
  $abilityTypes = GetAbilityTypes($cardID, $index, $from);
  if ($phase == "P" && $from != "HAND") return false;
  if ($phase == "B" && $from == "BANISH") return false;
  if ($phase == "B" && $from == "THEIRBANISH") return false;
  if ($from == "BANISH") {
    $banishCard = $banish->Card($index);
    if (!(PlayableFromBanish($banishCard->ID(), $banishCard->Modifier()) || AbilityPlayableFromBanish($banishCard->ID(), $banishCard->Modifier()))) return false;
  }
  if ($from == "THEIRBANISH") {
    $theirBanish = new Banish($otherPlayer);
    $banishCard = $theirBanish->Card($index);
    if (!(PlayableFromOtherPlayerBanish($banishCard->ID(), $banishCard->Modifier()))) return false;
  } else if ($from == "GY" && !PlayableFromGraveyard($cardID)) return false;
  if ($from == "DECK" && ($character[5] == 0 || $character[1] < 2 || $character[0] != "dash_io" && $character[0] != "dash_database" || CardCost($cardID, $from) > 1 || !SubtypeContains($cardID, "Item", $player) || !ClassContains($cardID, "MECHANOLOGIST", $player))) return false;
  if (TypeContains($cardID, "E", $player) && $character[$index + 12] == "DOWN" && HasCloaked($cardID, $player) == "UP") return false;
  if ($phase == "B") {
    if (TypeContains($cardID, "E", $player) && $character[$index + 6] == 1) return false;
    if (IsBlockRestricted($cardID, $restriction, $player)) return false;
  }
  if ($phase != "B" && $from == "CHAR" && $character[$index + 1] != "2") return false;
  if ($phase != "B" && TypeContains($cardID, "E", $player) && GetCharacterGemState($player, $cardID) == 0 && (ManualTunicSetting($player) == 0 && $cardID != "fyendals_spring_tunic")) return false;
  if ($from == "CHAR" && $phase != "B" && $character[$index + 8] == "1") {
    $restriction = "Frozen";
    return false;
  }
  if ($from == "PLAY" && DelimStringContains($subtype, "Ally") && $phase != "B" && isset($myAllies[$index + 3]) && $myAllies[$index + 3] == "1") {
    $restriction = "Frozen";
    return false;
  }
  if ($from == "ARS" && $phase != "B" && $myArsenal[$index + 4] == "1") {
    $restriction = "Frozen";
    return false;
  }
  if ($phase != "P" && $cardType == "DR" && IsAllyAttackTarget() && $currentPlayer != $mainPlayer) return false;
  if ($phase != "P" && $cardType == "AR" && IsAllyAttacking() && $currentPlayer == $mainPlayer) return false;
  if ($CombatChain->HasCurrentLink() && ($phase == "B" || (($phase == "D" || $phase == "INSTANT") && $cardType == "DR"))) {
    if ($from == "HAND") {
      if (!DelimStringContains($abilityTypes, "I", true) && CachedDominateActive() && CachedNumDefendedFromHand() >= 1 && NumDefendedFromHand() >= 1) return false;
      if (CachedTotalAttack() <= 2 && (SearchCharacterForCard($mainPlayer, "benji_the_piercing_wind") || SearchCurrentTurnEffects("benji_the_piercing_wind-SHIYANA", $mainPlayer)) && (SearchCharacterActive($mainPlayer, "benji_the_piercing_wind") || SearchCharacterActive($mainPlayer, "shiyana_diamond_gemini")) && CardType($CombatChain->AttackCard()->ID()) == "AA") return false;
    }
    if (CachedOverpowerActive() && CachedNumActionBlocked() >= 1) {
      if (DelimStringContains($cardType, "A") || $cardType == "AA") return false;
      if (SubtypeContains($cardID, "Evo") && $cardID != "teklovossen_the_mechropotentb" && $cardID != "nitro_mechanoidb") {
        if (CardType(GetCardIDBeforeTransform($cardID)) == "A") return false;
      }
    }
  }
  if ($phase == "B" && $from == "ARS" && !(($cardType == "AA" && SearchCurrentTurnEffects("art_of_war_yellow-2", $player)) || $cardID == "down_and_dirty_red" || HasAmbush($cardID))) return false;
  if ($phase == "B" || $phase == "D") {
    if ($cardType == "AA") {
      $baseAttackMax = $combatChainState[$CCS_BaseAttackDefenseMax];
      if ($baseAttackMax > -1 && AttackValue($cardID) > $baseAttackMax) return false;
    }
    if ($CombatChain->AttackCard()->ID() == "regicide_blue" && $phase == "B" && SearchBanishForCardName($player, $cardID) > -1) return false;
    $resourceMin = $combatChainState[$CCS_ResourceCostDefenseMin];
    if ($resourceMin > -1 && CardCost($cardID, $from) < $resourceMin && $cardType != "E") return false;
    if ($combatChainState[$CCS_CardTypeDefenseRequirement] == "Attack_Action" && $cardType != "AA") return false;
    if ($combatChainState[$CCS_CardTypeDefenseRequirement] == "Non-attack_Action" && $cardType != "A") return false;
  }
  if ($CombatChain->AttackCard()->ID() == "regicide_blue" && $cardType == "DR") return SearchBanishForCardName($player, $cardID) == -1;
  if ($from != "PLAY" && $phase == "B" && $cardType != "DR") return BlockValue($cardID) > -1;
  if (($phase == "P" || $phase == "CHOOSEHANDCANCEL") && IsPitchRestricted($cardID, $restriction, $from, $index, $pitchRestriction, phase:$phase)) return false;
  elseif ($phase == "CHOOSEHANDCANCEL" && $from == "HAND") {
    if (count($layers) > 0) {
      $topLayer = $layers[0];
      return match($topLayer) {
        "great_library_of_solana" => ColorContains($cardID, 2, $currentPlayer),
        "arakni_tarantula" => ClassContains($cardID, "ASSASSIN", $currentPlayer),
        default => true
      };
    }
  }
  elseif ($phase == "P") return true;
  if ($from != "PLAY" && $phase == "P" && PitchValue($cardID) > 0) return true;
  $isStaticType = IsStaticType($cardType, $from, $cardID);
  if ($isStaticType) $cardType = GetAbilityType($cardID, $index, $from);
  if ($cardType == "") return false;
  if (RequiresDiscard($cardID) || $cardID == "enlightened_strike_red") {
    if ($from == "HAND" && count($myHand) < 2) return false;
    else if (count($myHand) < 1) return false;
  }
  if (EffectPlayCardConstantRestriction($cardID, CardType($cardID), $restriction, $phase)) return false;
  if ($phase != "B" && $phase != "P" && !str_contains($phase, "CHOOSE") && IsPlayRestricted($cardID, $restriction, $from, $index, $player)) return false;
  if ($phase == "M" && $subtype == "Arrow") {
    if ($from != "ARS") return false;
    if (!SubtypeContains($character[CharacterPieces()], "Bow") && !SubtypeContains($character[CharacterPieces() * 2], "Bow")) return false;
  }
  if (SearchCurrentTurnEffects("three_of_a_kind_red", $player) && !$isStaticType && $from != "ARS") return false;
  if (SearchCurrentTurnEffects("red_in_the_ledger_red", $player)) {
    if (!HasMeld($cardID) && (DelimStringContains($cardType, "A") || $cardType == "AA") && !str_contains($abilityTypes, "I") && GetClassState($player, $CS_NumActionsPlayed) >= 1) return false;
    if (str_contains($abilityTypes, "I") && ($from == "BANISH" || $from == "THEIRBANISH")) return false;
  }
  if (SearchCurrentTurnEffects("immobilizing_shot_red", $player) && !$isStaticType && DelimStringContains($cardType, "A") && GetClassState($player, $CS_NumNonAttackCards) >= 1) return false;
  if (SearchCurrentTurnEffects("immobilizing_shot_red", $player) && !$isStaticType && $cardType == "AA" && GetClassState($player, $CS_NumAttackCards) >= 1) return false;
  if ($CombatChain->HasCurrentLink()
    && $CombatChain->AttackCard()->ID() == "exude_confidence_red"
    && $player == $defPlayer
    && ($abilityType == "I" || DelimStringContains($cardType, "I") || str_contains($abilityTypes, "I"))) {
    $restriction = "Exude Confidance";
    $exudeAttack = AttackValue("exude_confidence_red");
    for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
      if (DelimStringContains(CardType($combatChain[$i]), "AA")) {
        $attackValue = AttackValue($combatChain[$i]);
        if ($attackValue + $combatChain[$i + 5] >= $exudeAttack + $combatChain[5]) {
          $restriction = "";
        }
      }
    }
    if ($restriction == "Exude Confidance") return false;
  }
  if (SearchCurrentTurnEffects("exude_confidence_red", $mainPlayer) && $player == $defPlayer && ($abilityType == "I" || DelimStringContains($cardType, "I")) && !str_contains($phase, "CHOOSE")) {
    $restriction = "Exude Confidance";
    return false;
  }
  if ($cardID == "restless_coalescence_yellow" && $from == "PLAY") {
    if ($auras[$index + 1] == 2 && $currentPlayer == $mainPlayer && $actionPoints > 0) return true;
    if (SearchCurrentTurnEffectsForUniqueID($auras[$index + 6]) != -1 && CanPlayInstant($phase) && $auras[$index + 3] > 0) return true;
    if ($auras[$index + 1] != 2 || $auras[$index + 3] <= 0) return false;
  }
  if ($cardID == "the_hand_that_pulls_the_strings" && $from == "ARS" && SearchArsenalForCard($currentPlayer, $cardID, "DOWN") != "" && $phase == "A") return true;
  if ((DelimStringContains($cardType, "I") || CanPlayAsInstant($cardID, $index, $from)) && CanPlayInstant($phase)) return true;
  if ($from == "PLAY" && AbilityPlayableFromCombatChain($cardID) && $phase != "B") return true;
  if ((DelimStringContains($cardType, "A") || $cardType == "AA") && $actionPoints < 1) return false;
  if ($cardID == "nitro_mechanoida" || $cardID == "teklovossen_the_mechropotent") {
    if (($phase == "M" && $mainPlayer == $currentPlayer)) {
      $charIndex = FindCharacterIndex($currentPlayer, $cardID);
      switch ($cardID) {
        case "nitro_mechanoida":
          return $character[$charIndex + 2] > 0;
        case "teklovossen_the_mechropotent":
          return $character[$charIndex + 2] > 1;
      }
    }
    return false;
  }
  switch ($cardType) {
    case "A":
      return $phase == "M";
    case "AA":
      return $phase == "M";
    case "AR":
      return $phase == "A";
    case "DR":
      if ($phase != "D") return false;
      if (!IsDefenseReactionPlayable($cardID, $from)) {
        $restriction = "Defense reaction not playable.";
        return false;
      }
      return true;
    case "Macro":
      return $phase == "M";
    default:
      return false;
  }
}

function IsBlockRestricted($cardID, &$restriction = null, $player = "")
{
  global $CombatChain, $mainPlayer, $CS_NumCardsDrawn, $CS_NumVigorDestroyed, $CS_NumMightDestroyed, $CS_NumAgilityDestroyed, $currentTurnEffects;
  global $defPlayer;
  if (IsEquipment($cardID, $player) && !CanBlockWithEquipment()) {
    $restriction = "This attack disallows blocking with equipment";
    return true;
  }
  if (IsEquipment($cardID, $player)) {
    $char = GetPlayerCharacter($player);
    if ($char[FindCharacterIndex($player, $cardID) + 12] == "DOWN") {
      return true;
    }
  }
  if (SearchCurrentTurnEffects("stasis_cell_blue-B-" . $cardID, $player)) {
    $restriction = "stasis_cell_blue";
    return true;
  }
  if ($CombatChain->AttackCard()->ID() == "heavy_artillery_red" || $CombatChain->AttackCard()->ID() == "heavy_artillery_yellow" || $CombatChain->AttackCard()->ID() == "heavy_artillery_blue") {
    if (CardCost($cardID) < EvoUpgradeAmount($mainPlayer) && CardType($cardID) == "AA") {
      $restriction = $CombatChain->AttackCard()->ID();
      return true;
    }
  };
  //modal cards dominate and overpower restriction
  if (IsDominateActive() && NumDefendedFromHand() >= 1 && GetAbilityTypes($cardID) != "") return true;
  if (IsOverpowerActive() && NumActionsBlocking() >= 1 && GetAbilityTypes($cardID) != "") {
    if (CardTypeExtended($cardID) == "A" || CardTypeExtended($cardID) == "AA") return true;
  }
  //current turn effects
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $defPlayer) {
      $effectArr = explode(",", $currentTurnEffects[$i]);
      $effectID = $effectArr[0];
      switch ($effectID) {
        case "chains_of_eminence_red":
          if (GamestateSanitize(NameOverride($cardID)) == $effectArr[1]) return true;
          break;
        default:
          break;
      }
    }
  }
  if(SubtypeContains($cardID, "Aura", $player) && !CanBlockWithAura()) return true;
  switch ($cardID) {
    case "face_adversity":
      return GetClassState($mainPlayer, $CS_NumCardsDrawn) == 0;
    case "confront_adversity":
      return GetClassState($mainPlayer, $CS_NumVigorDestroyed) == 0;
    case "embrace_adversity":
      return GetClassState($mainPlayer, $CS_NumMightDestroyed) == 0;
    case "overcome_adversity":
      return GetClassState($mainPlayer, $CS_NumAgilityDestroyed) == 0;
    default:
      break;
  }
  return false;
}

function CanBlockWithEquipment()
{
  global $CombatChain, $mainPlayer;
  switch ($CombatChain->AttackCard()->ID()) {
    case "burn_rubber_red":
      return !SearchCurrentTurnEffects("burn_rubber_red", $mainPlayer);
    case "out_pace_red":
    case "out_pace_yellow":
    case "out_pace_blue":
    case "lay_waste_red":
    case "lay_waste_yellow":
    case "lay_waste_blue":
      return false;
    default:
      return true;
  }
}

function CanBlockWithAura()
{
  global $CombatChain;
  switch ($CombatChain->AttackCard()->ID()) {
    case "cut_through_the_facade_red":
      return false;
    default:
      return true;
  }
}

function GoesWhereAfterResolving($cardID, $from = null, $player = "", $playedFrom = "", $stillOnCombatChain = 1, $additionalCosts = "-")
{
  global $currentPlayer, $CS_NumWizardNonAttack, $CS_NumBoosted, $mainPlayer, $CS_NumBluePlayed, $CS_NumAttacks;
  if ($player == "") $player = $currentPlayer;
  $otherPlayer = $player == 2 ? 1 : 2;
  if (($from == "THEIRBANISH" || $playedFrom == "THEIRBANISH")) {
    switch ($cardID) {
      case "dig_up_dinner_blue":
        return "THEIRBANISH";
      case "sacred_art_undercurrent_desires_blue":
      case "sacred_art_immortal_lunar_shrine_blue":
      case "sacred_art_jade_tiger_domain_blue":
        if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) return "-";
        else if ($additionalCosts != "-") {
          $modes = explode(",", $additionalCosts);
          for ($i = 0; $i < count($modes); ++$i) {
            if ($modes[$i] == "Transcend") return "-";
          }
        }
        return "THEIRDISCARD";
      case "a_drop_in_the_ocean_blue":
      case "homage_to_ancestors_blue":
      case "pass_over_blue":
      case "path_well_traveled_blue":
      case "preserve_tradition_blue":
      case "rising_sun_setting_moon_blue":
      case "stir_the_pot_blue":
      case "the_grain_that_tips_the_scale_blue":
        if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) return "-";
        else return "THEIRDISCARD";
      default:
        return "THEIRDISCARD";
    }
  }

  $goesWhereEffect = GoesWhereEffectsModifier($cardID, $from, $player);
  if ($goesWhereEffect != -1) return $goesWhereEffect;
  if (($from == "COMBATCHAIN" || $from == "CHAINCLOSING") && $player != $mainPlayer && CardType($cardID) != "DR") return "GY"; //If it was blocking, don't put it where it would go if it was played
  $subtype = CardSubType($cardID);
  $type = CardType($cardID);
  if (HasMeld($cardID) && $additionalCosts == "Both" && $from != "MELD") return "-";
  if (DelimStringContains($type, "W")) return "-";
  if (DelimStringContains($subtype, "Invocation") || DelimStringContains($subtype, "Ash") || $cardID == "UPR439" || $cardID == "UPR440" || $cardID == "UPR441" || $cardID == "teklovossen_the_mechropotent") return "-";
  if (DelimStringContains($subtype, "Construct")) {
    switch ($cardID) {
      case "construct_nitro_mechanoid_yellow":
        if (CheckIfConstructNitroMechanoidConditionsAreMet($currentPlayer) == "") return "-";
        break;
      case "singularity_red":
        if (CheckIfSingularityConditionsAreMet($currentPlayer) == "") return "-";
        break;
      default:
        break;
    }

  }
  switch ($cardID) {
    case "remembrance_yellow":
      return "BANISH";
    case "gaze_the_ages_blue":
      if (substr($from, 0, 5) != "THEIR") return GetClassState($player, $CS_NumWizardNonAttack) >= 2 ? "HAND" : "GY";
      else return GetClassState($player, $CS_NumWizardNonAttack) >= 2 ? "THEIRHAND" : "THEIRDISCARD";
    case "soul_shield_yellow":
      return ($from == "CHAINCLOSING" && $stillOnCombatChain ? "SOUL" : "GY");
    case "soul_food_yellow":
      return "SOUL";
    case "sonata_arcanix_red":
      return "BANISH";
    case "pulse_of_candlehold_yellow":
      return "BANISH";
    case "evergreen_red":
    case "evergreen_yellow":
    case "evergreen_blue":
      if ($playedFrom == "ARS" && $from == "CHAINCLOSING") return "BOTDECK";
      if (substr($from, 0, 5) != "THEIR") return "GY";
      else return "THEIRDISCARD";
    case "sow_tomorrow_red":
    case "sow_tomorrow_yellow":
    case "sow_tomorrow_blue":
      return "-";
    case "invigorating_light_red":
    case "invigorating_light_yellow":
    case "invigorating_light_blue":
      return ($from == "CHAINCLOSING" && SearchCurrentTurnEffects($cardID, $mainPlayer) ? "SOUL" : "GY");
    case "ray_of_hope_yellow":
      $theirChar = &GetPlayerCharacter($otherPlayer);
      return (PlayerHasLessHealth($player) && TalentContains($theirChar[0], "SHADOW") ? "SOUL" : "GY");
    case "guardian_of_the_shadowrealm_red":
      return ($from == "BANISH" ? "HAND" : "GY");
    case "rotary_ram_red":
    case "rotary_ram_yellow":
    case "rotary_ram_blue":
      return (GetClassState($player, $CS_NumBoosted) > 0 ? "BOTDECK" : "GY");
    case "timekeepers_whim_red":
    case "timekeepers_whim_yellow":
    case "timekeepers_whim_blue":
      if ($player != $mainPlayer && substr($from, 0, 5) != "THEIR") return "BOTDECK";
      else if ($player != $mainPlayer) return "THEIRBOTDECK";
      else return "GY";
    case "double_strike_red":
      if ($from == "COMBATCHAIN" && !SearchCurrentTurnEffects($cardID, $player)) {
        AddCurrentTurnEffect($cardID, $player);
        return "BANISH,TCC";
      } else SearchCurrentTurnEffects($cardID, $player, 1);
      return "GY";
    case "dig_up_dinner_blue":
      return "BANISH";
    case "fabricate_red":
      return "-";
    case "sacred_art_undercurrent_desires_blue":
    case "sacred_art_immortal_lunar_shrine_blue":
    case "sacred_art_jade_tiger_domain_blue":
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) return "-";
      else if ($additionalCosts != "-") {
        $modes = explode(",", $additionalCosts);
        for ($i = 0; $i < count($modes); ++$i) {
          if ($modes[$i] == "Transcend") return "-";
        }
      }
      return "GY";
    case "a_drop_in_the_ocean_blue":
    case "homage_to_ancestors_blue":
    case "pass_over_blue":
    case "path_well_traveled_blue":
    case "preserve_tradition_blue":
    case "rising_sun_setting_moon_blue":
    case "stir_the_pot_blue":
    case "the_grain_that_tips_the_scale_blue":
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) return "-";
      else return "GY";
    case "relentless_pursuit_blue":
      if (GetClassState($currentPlayer, $CS_NumAttacks) > 0) return "BOTDECK";
      else return "GY";
    case "the_hand_that_pulls_the_strings":
      return "-";
    default:
      return "GY";
  }
}

function GoesWhereEffectsModifier($cardID, $from, $player)
{
  global $currentTurnEffects;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $effectID = ExtractCardID($currentTurnEffects[$i]);
    if ($currentTurnEffects[$i + 1] == $player) {
      switch ($effectID) {
        case "blossoming_spellblade_red":
          if ($from == "BANISH" && SearchCurrentTurnEffectsForUniqueID($cardID) != -1) {
            RemoveCurrentTurnEffect($i);
            return "BANISH";
          }
          break;
        case "amulet_of_oblation_blue":
          $effectArr = explode("-", $currentTurnEffects[$i]);
          if ($cardID == $effectArr[1]) {
            RemoveCurrentTurnEffect($i);
            return "BOTDECK";
          }
          break;
        case "under_the_trapdoor_blue":
          if ($cardID == $currentTurnEffects[$i + 2]) {
            RemoveCurrentTurnEffect($i);
            return "BANISH";
          }
        default:
          break;
      }
    }
  }
  return -1;
}

function CanPlayInstant($phase)
{
  if ($phase == "M") return true;
  if ($phase == "A") return true;
  if ($phase == "D") return true;
  if ($phase == "INSTANT") return true;
  return false;
}

function IsPitchRestricted($cardID, &$restrictedBy, $from = "", $index = -1, $pitchRestriction = "", $phase = "P")
{
  global $playerID, $currentTurnEffects;
  $resources = &GetResources($playerID);
  $otherPlayer = $playerID == 1 ? 2 : 1;
  if(PitchValue($cardID) <= 0) return true; //Can't pitch mentors or landmarks
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $playerID) {
      $effectArr = explode(",", $currentTurnEffects[$i]);
      $effectID = $effectArr[0];
      switch ($effectID) {
        case "chains_of_eminence_red":
          if (GamestateSanitize(NameOverride($cardID)) == $effectArr[1]) {
            $restrictedBy = "chains_of_eminence_red";
            return true;
          }
        default:
          break;
      }
    }
  }
  if (SearchCurrentTurnEffects("frost_lock_blue-3", $playerID) && CardCost($cardID, $from) == 0) {
    $restrictedBy = "frost_lock_blue";
    return true;
  }
  if (ColorContains($cardID, 1, $playerID) && SearchCurrentTurnEffects("barbed_undertow_red-1", $playerID)) {
    $restrictedBy = "barbed_undertow_red";
    return true;
  } else if (ColorContains($cardID, 2, $playerID) && SearchCurrentTurnEffects("barbed_undertow_red-2", $playerID)) {
    $restrictedBy = "barbed_undertow_red";
    return true;
  } else if (ColorContains($cardID, 3, $playerID) && SearchCurrentTurnEffects("barbed_undertow_red-3", $playerID)) {
    $restrictedBy = "barbed_undertow_red";
    return true;
  }
  if (CardCareAboutChiPitch($pitchRestriction) && !SubtypeContains($cardID, "Chi") && $resources[0] < 3) return true;
  $foundNullTime = SearchItemForModalities(GamestateSanitize(NameOverride($cardID)), $otherPlayer, "null_time_zone_blue") != -1;
  $foundNullTime = $foundNullTime || SearchItemForModalities(GamestateSanitize(NameOverride($cardID)), $playerID, "null_time_zone_blue") != -1;
  if(($phase == "P" || $phase == "CHOOSEHANDCANCEL") && $foundNullTime){
    $restrictedBy = "null_time_zone_blue";
    return true;
  }
  return false;
}

function IsPlayRestricted($cardID, &$restriction, $from = "", $index = -1, $player = "", $resolutionCheck = false)
{
  global $CS_NumBoosted, $combatChain, $CombatChain, $combatChainState, $currentPlayer, $mainPlayer, $CS_Num6PowBan, $CS_NumCardsDrawn;
  global $CS_DamageTaken, $CS_NumFusedEarth, $CS_NumFusedIce, $CS_NumFusedLightning, $CS_NumNonAttackCards, $CS_DamageDealt, $defPlayer, $CS_NumCardsPlayed, $CS_NumLightningPlayed;
  global $CS_NumAttackCards, $CS_NumBloodDebtPlayed, $layers, $CS_HitsWithWeapon, $CS_AtksWWeapon, $CS_CardsEnteredGY, $CS_NumRedPlayed, $CS_NumPhantasmAADestroyed;
  global $CS_Num6PowDisc, $CS_HighestRoll, $CS_NumCrouchingTigerPlayedThisTurn, $CCS_WagersThisLink, $CCS_LinkBaseAttack, $chainLinks, $CS_NumInstantPlayed, $CS_PowDamageDealt;
  global $CS_TunicTicks, $CS_NumActionsPlayed, $CCS_NumUsedInReactions;
  if ($player == "") $player = $currentPlayer;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $character = &GetPlayerCharacter($player);
  $myHand = &GetHand($player);
  $myArsenal = &GetArsenal($player);
  $myItems = &GetItems($player);
  $mySoul = &GetSoul($player);
  $discard = new Discard($player);
  $otherPlayerDiscard = &GetDiscard($otherPlayer);
  $type = CardType($cardID);
  if (IsStaticType($type, $from, $cardID)) $type = GetResolvedAbilityType($cardID, $from);
  if (CardCareAboutChiPitch($cardID) && SearchHand($player, subtype: "Chi") == "") return true;
  if (SearchCurrentTurnEffects("crush_the_weak_red", $player) && CardType($cardID) == "AA" && AttackValue($cardID) <= 3) {
    $restriction = "crush_the_weak_red";
    return true;
  }
  if (SearchCurrentTurnEffects("herald_of_judgment_yellow", $player) && $from == "BANISH") {
    $restriction = "herald_of_judgment_yellow";
    return true;
  }
  if (SearchCurrentTurnEffects("light_it_up_yellow", $player) && TypeContains($cardID, "E", $player)) {
    $restriction = "light_it_up_yellow";
    return true;
  }
  if (SearchCurrentTurnEffects("frost_lock_blue-3", $player) && CardCost($cardID, $from) == 0 && !IsStaticType(CardType($cardID), $from, $cardID)) {
    $restriction = "frost_lock_blue";
    return true;
  }
  if (SearchCurrentTurnEffects("imperial_edict_red-" . GamestateSanitize(CardName($cardID)), $player)) {
    if ($from != "PLAY" && $from != "EQUIP" && !DelimStringContains(GetAbilityNames($cardID, $from), "Ability", true)) {
      $restriction = "imperial_edict_red";
      return true;
    }
  } //Can't be played
  if (SearchCurrentTurnEffects("stasis_cell_blue-" . $cardID, $player)) {
    $restriction = "stasis_cell_blue";
    return true;
  } //Can't be activated
  if (CardType($cardID) == "A" 
    && $from != "PLAY" 
    && GetClassState($player, $CS_NumNonAttackCards) >= 1 
    && (SearchItemsForCard("signal_jammer_blue", 1) != "" || SearchItemsForCard("signal_jammer_blue", 2) != "") 
    && (GetAbilityTypes($cardID) == "" || !DelimStringContains(GetAbilityTypes($cardID), "I"))
    ){
    $restriction = "signal_jammer_blue";
    return true;
  }
  if ($player != $mainPlayer && SearchAlliesActive($mainPlayer, "themai")) {
    $restriction = "themai";
    return true;
  }
  if (EffectPlayCardRestricted($cardID, $type, $from, resolutionCheck: $resolutionCheck) != "") {
    $restriction = true;
    return true;
  }
  if (EffectAttackRestricted($cardID, $type, $from, index:$index) != "" && $currentPlayer == $mainPlayer) {
    $restriction = true;
    return true;
  }
  switch ($cardID) {
    case "breaking_scales":
      return !$CombatChain->HasCurrentLink() || !HasCombo($CombatChain->AttackCard()->ID());
    case "ancestral_empowerment_red":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "NINJA", $player) || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "braveforge_bracers":
      return GetClassState($player, $CS_HitsWithWeapon) == 0;
    case "rout_red":
    case "singing_steelblade_yellow":
    case "overpower_red":
    case "overpower_yellow":
    case "overpower_blue":
    case "glint_the_quicksilver_blue":
    case "biting_blade_red":
    case "biting_blade_yellow":
    case "biting_blade_blue":
    case "stroke_of_foresight_red":
    case "stroke_of_foresight_yellow":
    case "stroke_of_foresight_blue":
      return !$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer);
    case "ironsong_response_red":
    case "ironsong_response_yellow":
    case "ironsong_response_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!RepriseActive()) return false;
      return !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer);
    case "fyendals_spring_tunic":
      if ($character[$index + 2] == 3) return false;
      if ($currentPlayer != $mainPlayer) return true; //only tick up on your own turn
      if (ManualTunicSetting($player) && GetClassState($player, piece: $CS_TunicTicks) == 0) {
        if (GetClassState($player, $CS_NumCardsPlayed) == 0) return false;
      }
      return true;
    case "snapdragon_scalers":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (CardType($CombatChain->AttackCard()->ID()) != "AA") return true;
      if (CardCost($CombatChain->AttackCard()->ID()) > 1) return true;
      return false;
    case "demolition_crew_red":
    case "demolition_crew_yellow":
    case "demolition_crew_blue":
      return SearchCount(SearchHand($currentPlayer, "", "", -1, 2)) < 2;
    case "flock_of_the_feather_walkers_red":
    case "flock_of_the_feather_walkers_yellow":
    case "flock_of_the_feather_walkers_blue":    
      return SearchCount(SearchHand($currentPlayer, "", "", -1, 0)) < 2;
    case "pummel_red":
    case "pummel_yellow":
    case "pummel_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      $subtype = CardSubtype($CombatChain->AttackCard()->ID());
      if ($subtype == "Club" || $subtype == "Hammer" || (CardType($CombatChain->AttackCard()->ID()) == "AA" && CardCost($CombatChain->AttackCard()->ID(), "CC") >= 2)) return false;
      return true;
    case "razor_reflex_red":
    case "razor_reflex_yellow":
    case "razor_reflex_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      $subtype = CardSubtype($CombatChain->AttackCard()->ID());
      if ($subtype == "Sword" || $subtype == "Dagger" || (CardType($CombatChain->AttackCard()->ID()) == "AA" && CardCost($CombatChain->AttackCard()->ID(), "CC") <= 1)) return false;
      return true;
    case "teklo_foundry_heart":
      return GetClassState($player, $CS_NumBoosted) < 1;
    case "achilles_accelerator":
      return GetClassState($player, $CS_NumBoosted) < 1;
    case "maximum_velocity_red":
      return GetClassState($player, $CS_NumBoosted) < 3;
    case "induction_chamber_red":
      return $CombatChain->HasCurrentLink()
        && $from == "PLAY"
        && (!ClassContains($CombatChain->AttackCard()->ID(), "MECHANOLOGIST", $player)
          || $myItems[$index + 1] == 0
          || CardSubtype($CombatChain->AttackCard()->ID()) != "Pistol"
          || $myItems[$index + 2] != 2);
    case "cognition_nodes_blue":
      return ($CombatChain->HasCurrentLink() && $from == "PLAY" && ($myItems[$index + 1] == 0 || CardType($CombatChain->AttackCard()->ID()) != "AA" || $myItems[$index + 2] != 2));
    case "skullbone_crosswrap":
      return !ArsenalHasFaceDownCard($player);
    case "twinning_blade_yellow":
    case "unified_decree_yellow":
      return !$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer);
    case "out_for_blood_red":
    case "out_for_blood_yellow":
    case "out_for_blood_blue":
      return !$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer);
    case "shiyana_diamond_gemini":
      return IsPlayRestricted(ShiyanaCharacter("shiyana_diamond_gemini"), $restriction, $from, $index, $player);
    case "feign_death_yellow":
      return !HasTakenDamage($player);
    case "tripwire_trap_red":
    case "pitfall_trap_yellow":
    case "rockslide_trap_blue":
      return $from != "ARS";
    case "rattle_bones_red":
      return SearchCount(SearchDiscard($player, "AA", "", -1, -1, "RUNEBLADE")) == 0;
    case "aetherize_blue":
      return count($layers) == 0 || SearchLayer($player, "I", "", 1) == "";
    case "lunging_press_blue":
      return !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "reinforce_the_line_red":
    case "reinforce_the_line_yellow":
    case "reinforce_the_line_blue":
      $found = SearchCombatChainLink($player, "AA");
      return $found == "" || $found == "0";
    case "great_library_of_solana":
      return $from == "PLAY" && SearchCount(SearchHand($player, "", "", -1, -1, "", "", false, false, 2)) < 2;
    case "prism_sculptor_of_arc_light":
    case "prism":
      return count($mySoul) == 0;
    case "ser_boltyn_breaker_of_dawn":
    case "boltyn":
      return count($mySoul) == 0 || !HasIncreasedAttack();
    case "beacon_of_victory_yellow":
      return count($mySoul) == 0;
    case "celestial_cataclysm_yellow":
      return count($mySoul) < 3;
    case "blinding_beam_red":
    case "blinding_beam_yellow":
    case "blinding_beam_blue":
      return !$CombatChain->HasCurrentLink();
    case "graveling_growl_red":
    case "graveling_growl_yellow":
    case "graveling_growl_blue":
      return GetClassState($player, $CS_Num6PowBan) == 0;
    case "endless_maw_red":
    case "endless_maw_yellow":
    case "endless_maw_blue":
    case "writhing_beast_hulk_red":
    case "writhing_beast_hulk_yellow":
    case "writhing_beast_hulk_blue":
    case "convulsions_from_the_bellows_of_hell_red":
    case "convulsions_from_the_bellows_of_hell_yellow":
    case "convulsions_from_the_bellows_of_hell_blue":
    case "boneyard_marauder_red":
    case "boneyard_marauder_yellow":
    case "boneyard_marauder_blue":
    case "dread_screamer_red":
    case "dread_screamer_yellow":
    case "dread_screamer_blue":
    case "hungering_slaughterbeast_red":
    case "hungering_slaughterbeast_yellow":
    case "hungering_slaughterbeast_blue":
    case "unworldly_bellow_red":
    case "unworldly_bellow_yellow":
    case "unworldly_bellow_blue":
      return $discard->NumCards() < 3;
    case "doomsday_blue":
      return SearchCount(SearchBanish($player, "", "", -1, -1, "", "", true)) < 6;
    case "eclipse_blue":
      return GetClassState($player, $CS_NumBloodDebtPlayed) < 6;
    case "soul_harvest_blue":
      return $discard->NumCards() < 6;
    case "aether_ironweave":
      return GetClassState($player, $CS_NumAttackCards) == 0 || GetClassState($player, $CS_NumNonAttackCards) == 0;
    case "blood_drop_brocade":
      return GetClassState($player, $CS_DamageTaken) == 0 && GetClassState($otherPlayer, $CS_DamageTaken) == 0;
    case "rally_the_rearguard_red":
    case "rally_the_rearguard_yellow":
    case "rally_the_rearguard_blue":
      if (isset($combatChain[$index + 7]) && $from == "PLAY") return SearchCurrentTurnEffects($cardID, $player, false, true) == $combatChain[$index + 7];
      else return false;
    case "memorial_ground_red":
    case "memorial_ground_yellow":
    case "memorial_ground_blue":
      $maxCost = 2;
      if ($cardID == "memorial_ground_yellow") $maxCost = 1;
      elseif ($cardID == "memorial_ground_blue") $maxCost = 0;
      return SearchDiscard($player, "AA", "", $maxCost) == "";
    case "lexi_livewire":
    case "lexi":
    case "crown_of_seeds":
      return !ArsenalHasFaceDownCard($player);
    case "plume_of_evergrowth":
      $found = CombineSearches(SearchDiscard($player, "AA", talent: "EARTH"), SearchDiscard($player, "A", talent: "EARTH"));
      $found = CombineSearches(SearchDiscard($player, "I", talent: "EARTH"), $found);
      return $found == "";
    case "tome_of_harvests_blue":
      return $from == "ARS" || ArsenalEmpty($player);
    case "summerwood_shelter_red":
    case "summerwood_shelter_yellow":
    case "summerwood_shelter_blue":
      $found = CombineSearches(SearchCombatChainLink($defPlayer, "A", talent: "EARTH,ELEMENTAL"), SearchCombatChainLink($defPlayer, "AA", talent: "EARTH,ELEMENTAL"));
      return $found == "" || $found == "0";
    case "sow_tomorrow_red":
    case "sow_tomorrow_yellow":
    case "sow_tomorrow_blue":
      return SowTomorrowIndices($player, $cardID) == "";
    case "amulet_of_earth_blue":
      return $from == "PLAY" && GetClassState($player, $CS_NumFusedEarth) == 0;
    case "blizzard_blue":
      if ($CombatChain->HasCurrentLink()) return false;//If there's an attack, there's a valid target
      return !HasAttackLayer();
    case "amulet_of_ice_blue":
      return $from == "PLAY" && GetClassState($player, $CS_NumFusedIce) == 0;
    case "lightning_press_red":
    case "lightning_press_yellow":
    case "lightning_press_blue":
      if (count($layers) == 0 && !$CombatChain->HasCurrentLink()) return true;
      if (SearchCount(SearchCombatChainLink($currentPlayer, type: "AA", maxCost: 1)) > 0) return false;
      for ($i = 0; $i < count($layers); $i += LayerPieces()) {
        if (strlen($layers[$i]) == 6 && CardType($layers[$i]) == "AA" && CardCost($layers[$i]) <= 1) return false;
      }
      return true;
    case "shock_striker_red":
    case "shock_striker_yellow":
    case "shock_striker_blue":
      return SearchCurrentTurnEffects($cardID, $player);
    case "amulet_of_lightning_blue":
      return $from == "PLAY" && GetClassState($player, $CS_NumFusedLightning) == 0;
    case "spellbound_creepers":
      return GetClassState($player, $CS_NumAttackCards) == 0; // Blocked/Played
    case "sutcliffes_suede_hides":
      return !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA" || GetClassState($currentPlayer, $CS_NumNonAttackCards) == 0;
    case "ragamuffins_hat":
      return count($myHand) != 1;
    case "deep_blue":
      return count($myHand) == 0;
    case "runaways":
      return !HasTakenDamage($player);
    case "shatter_yellow":
      return !$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) || Is1H($CombatChain->AttackCard()->ID());
    case "blade_runner_red":
    case "blade_runner_yellow":
    case "blade_runner_blue":
      return !$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) || !Is1H($CombatChain->AttackCard()->ID());
    case "in_the_swing_red":
    case "in_the_swing_yellow":
    case "in_the_swing_blue":
      return GetClassState($player, $CS_AtksWWeapon) < 1 || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer);
    case "crown_of_reflection":
      return $player != $mainPlayer;
    case "even_bigger_than_that_red":
    case "even_bigger_than_that_yellow":
    case "even_bigger_than_that_blue":
      return GetClassState($player, piece: $CS_PowDamageDealt) == 0;
    case "amulet_of_assertiveness_yellow":
      return $from == "PLAY" && count($myHand) < 4;
    case "amulet_of_echoes_blue":
      return $from == "PLAY" && IsAmuletOfEchoesRestricted($from, $otherPlayer);
    case "amulet_of_havencall_blue":
      return ($from == "PLAY" && count($myHand) > 0);
    case "amulet_of_ignition_yellow":
      return ($from == "PLAY" && GetClassState($player, $CS_NumCardsPlayed) >= 1);
    case "helm_of_sharp_eye":
      return !IsWeaponGreaterThanTwiceBasePower();
    case "amulet_of_oblation_blue":
      return $from == "PLAY" && (GetClassState(1, $CS_CardsEnteredGY) == 0 && GetClassState(2, $CS_CardsEnteredGY) == 0 || !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA");
    case "run_through_yellow":
      return (!$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) || CardSubType($CombatChain->AttackCard()->ID()) != "Sword");
    case "thrust_red":
    case "blade_flash_blue":
      return !$CombatChain->HasCurrentLink() || CardSubType($CombatChain->AttackCard()->ID()) != "Sword";
    case "combustion_point_red":
      return (!$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA" || (!ClassContains($CombatChain->AttackCard()->ID(), "NINJA", $player) && !TalentContains($CombatChain->AttackCard()->ID(), "DRACONIC", $currentPlayer)));
    case "flamescale_furnace":
      return GetClassState($player, $CS_NumRedPlayed) == 0;
    case "sash_of_sandikai":
      return GetClassState($player, $CS_NumRedPlayed) == 0;
    case "liquefy_red":
      return !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "tome_of_firebrand_red":
      return ($player != $mainPlayer || NumDraconicChainLinks() < 4);
    case "ghostly_touch":
      return ($character[$index + 2] < 2 && !SearchCurrentTurnEffects($cardID, $player));
    case "frightmare_red":
      return GetClassState($player, $CS_NumPhantasmAADestroyed) < 1;
    case "semblance_blue":
      if ($CombatChain->HasCurrentLink()) return !ClassContains($CombatChain->AttackCard()->ID(), "ILLUSIONIST", $player);
      else if (count($layers) != 0) return !ClassContains($layers[0], "ILLUSIONIST", $player);
      return true;
    case "tide_flippers":
      return !$CombatChain->HasCurrentLink() || AttackValue($CombatChain->AttackCard()->ID()) > 2 || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "rapid_reflex_red":
    case "rapid_reflex_yellow":
    case "rapid_reflex_blue":
      return !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA" || CardCost($CombatChain->AttackCard()->ID()) > 0;
    case "waning_moon":
      return GetClassState($player, $CS_NumNonAttackCards) == 0;
    case "alluvion_constellas":
      return $character[$index + 2] < 2;
    case "spellfire_cloak":
      return $player == $mainPlayer;
    case "rewind_blue":
      return SearchLayer($otherPlayer, "A") == "";
    //Invocations must target Ash
    case "silken_form":
    case "invoke_dracona_optimai_red":
    case "invoke_tomeltai_red":
    case "invoke_dominia_red":
    case "invoke_azvolai_red":
    case "invoke_cromai_red":
    case "invoke_kyloria_red":
    case "invoke_miragai_red":
    case "invoke_nekria_red":
    case "invoke_ouvia_red":
    case "invoke_themai_red":
    case "invoke_vynserakai_red":
    case "invoke_yendurai_red":
    case "skittering_sands_red":
    case "skittering_sands_yellow":
    case "skittering_sands_blue":
    case "sand_cover_red":
    case "sand_cover_yellow":
    case "sand_cover_blue":
      return SearchCount(SearchPermanents($player, "", "Ash")) < 1;
    case "rok":
      return count(GetHand($player)) != 0;
    case "savage_beatdown_red":
      return GetClassState($mainPlayer, $CS_Num6PowDisc) > 0 ? 0 : 1;
    case "rumble_grunting_red":
    case "rumble_grunting_yellow":
    case "rumble_grunting_blue":
      return GetClassState($mainPlayer, $CS_Num6PowDisc) > 0 ? 0 : 1;
    case "hanabi_blaster":
      return $character[$index + 2] < 2;
    case "puncture_red":
    case "puncture_yellow":
    case "puncture_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      $subtype = CardSubType($CombatChain->AttackCard()->ID());
      return $subtype != "Sword" && $subtype != "Dagger";
    case "blacktek_whisperers":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $mainPlayer) || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "mask_of_perdition":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $mainPlayer) || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "shred_red":
    case "shred_yellow":
    case "shred_blue":
      return NumCardsBlocking() < 1 || !ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $mainPlayer);
    case "cut_to_the_chase_red":
    case "cut_to_the_chase_yellow":
    case "cut_to_the_chase_blue":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $mainPlayer) || ContractType($CombatChain->AttackCard()->ID()) == "";
    case "point_the_tip_red":
    case "point_the_tip_yellow":
    case "point_the_tip_blue":
      $arsenalHasFaceUp = ArsenalHasFaceUpArrowCard($mainPlayer);
      if (!$arsenalHasFaceUp) $restriction = "There must be a face up arrow in your arsenal.";
      return !$arsenalHasFaceUp;
    case "invoke_suraya_yellow":
      return CountAura("spectral_shield", $currentPlayer) < 1;
    case "uzuri_switchblade":
    case "uzuri":
      return !$CombatChain->HasCurrentLink() || !HasStealth($CombatChain->AttackCard()->ID()) || count($myHand) == 0;
    case "spike_with_bloodrot_red":
    case "spike_with_frailty_red":
    case "spike_with_inertia_red":
    case "razors_edge_red":
    case "razors_edge_yellow":
    case "razors_edge_blue":
      return !$CombatChain->HasCurrentLink() || !HasStealth($CombatChain->AttackCard()->ID()) || (CardType($CombatChain->AttackCard()->ID()) != "AA");
    case "silverwind_shuriken_blue":
      return ($from == "PLAY" ? !$CombatChain->HasCurrentLink() || !HasCombo($CombatChain->AttackCard()->ID()) : false);
    case "trench_of_sunken_treasure":
      return !ArsenalHasFaceDownCard($player);
    case "flick_knives":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!SearchCharacterAliveSubtype($player, "Dagger", true) && SearchCombatChainAttacks($player, subtype:"Dagger") == "") {
        $restriction = "No dagger to throw";
        return true;
      }
      return false;
    case "concealed_blade_blue":
      return !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA" || (!ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $mainPlayer) && !ClassContains($CombatChain->AttackCard()->ID(), "NINJA", $mainPlayer));
    case "short_and_sharp_red":
    case "short_and_sharp_yellow":
    case "short_and_sharp_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      $subtype = CardSubtype($CombatChain->AttackCard()->ID());
      if ($subtype == "Dagger" || (CardType($CombatChain->AttackCard()->ID()) == "AA" && AttackValue($CombatChain->AttackCard()->ID()) <= 2)) return false;
      return true;
    case "mask_of_malicious_manifestations":
      return (count($myHand) + count($myArsenal)) < 2;
    case "death_touch_red":
    case "death_touch_yellow":
    case "death_touch_blue":
      return $from == "HAND";
    case "virulent_touch_red":
    case "virulent_touch_yellow":
    case "virulent_touch_blue":
      return $from == "HAND";
    case "threadbare_tunic":
      return count($myHand) > 0;
    case "fisticuffs":
      return !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "fleet_foot_sandals":
      return !$CombatChain->HasCurrentLink() || (CardType($CombatChain->AttackCard()->ID()) != "AA" && !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer)) || AttackValue($CombatChain->AttackCard()->ID()) > 1;
    case "prism_awakener_of_sol":
    case "prism_advent_of_thrones":
      return (count($mySoul) == 0 || $character[5] == 0 || SearchPermanents($player, subtype: "Figment") == "");
    case "luminaris_celestial_fury":
      return !$CombatChain->HasCurrentLink() || (!str_contains(NameOverride($CombatChain->AttackCard()->ID(), $mainPlayer), "Herald") && !SubtypeContains($CombatChain->AttackCard()->ID(), "Angel", $mainPlayer));
    case "angelic_descent_red":
    case "angelic_descent_yellow":
    case "angelic_descent_blue":
      return !$CombatChain->HasCurrentLink() || !str_contains(NameOverride($CombatChain->AttackCard()->ID(), $mainPlayer), "Herald");
    case "angelic_wrath_red":
    case "angelic_wrath_yellow":
    case "angelic_wrath_blue":
      return !$CombatChain->HasCurrentLink() || !str_contains(NameOverride($CombatChain->AttackCard()->ID(), $mainPlayer), "Herald");
    case "celestial_reprimand_red":
    case "celestial_reprimand_yellow":
    case "celestial_reprimand_blue":
      return count($combatChain) < (CombatChainPieces() * 2) || !str_contains(NameOverride($CombatChain->AttackCard()->ID(), $mainPlayer), "Herald");
    case "celestial_resolve_red":
    case "celestial_resolve_yellow":
    case "celestial_resolve_blue":
      return count($combatChain) < (CombatChainPieces() * 2) || GetChainLinkCards($defPlayer, nameContains: "Herald") == "";
    case "v_for_valor_red":
    case "v_for_valor_yellow":
    case "v_for_valor_blue":
      $hand = &GetHand($currentPlayer);
      return $from == "PLAY" && count($hand) == 0;
    case "resounding_courage_red":
    case "resounding_courage_yellow":
    case "resounding_courage_blue"://Resounding Courage
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer) || !TalentContains($CombatChain->AttackCard()->ID(), "LIGHT", $mainPlayer);
    case "radiant_view":
    case "radiant_raiment":
    case "radiant_touch":
    case "radiant_flow":
      return count($mySoul) == 0;
    case "spoiled_skull":
      $index = CombineSearches(SearchBanish($player, "AA"), SearchBanish($player, "A"));
      $cleanIndexes = RemoveCardSameNames($player, $index, GetBanish($player));
      return SearchCount($cleanIndexes) < 3;
    case "oblivion_blue":
      return CountAura("runechant", $currentPlayer) != 6;
    case "levia_redeemed":
      return $from != "PLAY" || SearchCount(SearchBanish($currentPlayer, bloodDebtOnly: true)) < 13;
    case "rugged_roller":
      return GetClassState($currentPlayer, $CS_HighestRoll) != 6;
    case "chorus_of_ironsong_yellow":
      return !$CombatChain->HasCurrentLink() || !CardNameContains($CombatChain->AttackCard()->ID(), "Dawnblade", $mainPlayer, true);
    case "apocalypse_automaton_red":
      return EvoUpgradeAmount($player) == 0;//Restricted if no EVOs
    case "mask_of_three_tails":
      return HitsInCombatChain() < 3 || $player != $mainPlayer;
    case "blood_scent":
      return GetClassState($player, $CS_NumCrouchingTigerPlayedThisTurn) == 0;
    case "symbiosis_shot":
      return $character[$index + 2] <= 0;
    case "maxx_the_hype_nitro":
    case "maxx_nitro": //Maxx Nitro
    case "teklovossen_esteemed_magnate":
    case "teklovossen": //Teklovossen
      return $character[5] == 0;
    case "cogwerx_base_head":
    case "cogwerx_base_chest":
    case "cogwerx_base_arms":
    case "cogwerx_base_legs":
      return $character[$index + 2] == 0 || GetClassState($player, $CS_NumBoosted) == 0;
    case "prismatic_lens_yellow":
    case "quantum_processor_yellow":
      if ($from == "PLAY") return $myItems[$index + 2] != 2; else return false;
    case "dissolving_shield_red":
    case "dissolving_shield_yellow":
    case "dissolving_shield_blue":
      if ($from == "PLAY") return $myItems[$index + 1] == 0; else return false;
    case "moonshot_yellow":
      return GetClassState($player, $CS_NumBoosted) <= 0;
    case "shriek_razors":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $mainPlayer) || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "evo_command_center_yellow_equip":
    case "evo_engine_room_yellow_equip":
    case "evo_smoothbore_yellow_equip":
    case "evo_thruster_yellow_equip":
    case "evo_data_mine_yellow_equip":
    case "evo_battery_pack_yellow_equip":
    case "evo_cogspitter_yellow_equip":
    case "evo_charging_rods_yellow_equip":
      return !EvoHasUnderCard($currentPlayer, $index);
    case "good_time_chapeau":
      return CountItem("gold", $currentPlayer) <= 0;
    case "kassai_of_the_golden_sand":
    case "kassai":
      return SearchCount(SearchDiscard($currentPlayer, pitch: 1)) < 2 || SearchCount(SearchDiscard($currentPlayer, pitch: 2)) < 2;
    case "prized_galea":
      return !TypeContains($CombatChain->AttackCard()->ID(), "W", $currentPlayer);
    case "hood_of_red_sand":
      return SearchCount(SearchDiscard($currentPlayer, pitch: 1)) < 1 || SearchCount(SearchDiscard($currentPlayer, pitch: 2)) < 1 || !CardSubtype($CombatChain->AttackCard()->ID()) == "Sword";
    case "blade_flurry_red":
      return !$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer);
    case "shift_the_tide_of_battle_yellow":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer) || CachedTotalAttack() <= AttackValue($CombatChain->AttackCard()->ID());
    case "cut_the_deck_red":
    case "cut_the_deck_yellow":
    case "cut_the_deck_blue":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer);
    case "fatal_engagement_red":
    case "fatal_engagement_yellow":
    case "fatal_engagement_blue":
      return !$CombatChain->HasCurrentLink() || NumAttacksBlocking() == 0;
    case "take_the_upper_hand_red":
    case "take_the_upper_hand_yellow":
    case "take_the_upper_hand_blue":
      return !$CombatChain->HasCurrentLink() || $combatChainState[$CCS_WagersThisLink] == 0;
    case "agile_engagement_red":
    case "agile_engagement_yellow":
    case "agile_engagement_blue":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer);
    case "vigorous_engagement_red":
    case "vigorous_engagement_yellow":
    case "vigorous_engagement_blue":
      return !$CombatChain->HasCurrentLink() || !ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer);
    case "cintari_sellsword":
      return GetClassState($player, $CS_AtksWWeapon) <= 0;
    case "balance_of_justice":
      return GetClassState($otherPlayer, $CS_NumCardsDrawn) < 2;
    case "graven_call":
      if ($from == "GY") return CountItem("silver", $currentPlayer) < 2; else return false;
    case "pass_over_blue":
      return count($otherPlayerDiscard) <= 0;
    case "preserve_tradition_blue":
      return CombineSearches(SearchDiscard($player, "A"), SearchDiscard($player, "AA")) == "";
    case "run_roughshod_blue":
      return GetClassState($mainPlayer, $CS_Num6PowDisc) > 0 ? 0 : 1;
    case "tide_chakra_red":
    case "tide_chakra_yellow":
    case "tide_chakra_blue":
    case "hiss_red":
    case "hiss_yellow":
    case "hiss_blue":
    case "intimate_inducement_red":
    case "intimate_inducement_yellow":
    case "intimate_inducement_blue":
    case "venomous_bite_red":
    case "venomous_bite_yellow":
    case "venomous_bite_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      if ((CardType($CombatChain->AttackCard()->ID()) == "AA" && (ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $player) || TalentContains($CombatChain->AttackCard()->ID(), "MYSTIC", $player)))) return false;
      return true;
    case "fang_strike":
    case "slither":
      return !$CombatChain->HasCurrentLink() || CardType($CombatChain->AttackCard()->ID()) != "AA";
    case "truths_retold":
    case "uphold_tradition":
    case "aqua_seeing_shell":
    case "skycrest_keikoi":
    case "skybody_keikoi":
    case "skyhold_keikoi":
    case "skywalker_keikoi":
      $charIndex = FindCharacterIndex($player, $cardID);
      return $character[$charIndex + 12] != "DOWN";
    case "waves_of_aqua_marine":
    case "aqua_laps":
      $charIndex = FindCharacterIndex($player, $cardID);
      return $character[$charIndex + 12] != "DOWN" || !$CombatChain->HasCurrentLink();
    case "a_drop_in_the_ocean_blue":
    case "path_well_traveled_blue":
    case "the_grain_that_tips_the_scale_blue":
      if ($CombatChain->HasCurrentLink()) return false;//If there's an attack, there's a valid target
      if (count($chainLinks) > 0) return false; //If there's an attack on previous chain links, there's a valid target
      return !HasAttackLayer();
    case "just_a_nick_red":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (HasStealth($CombatChain->AttackCard()->ID())) return false;
      if ($combatChainState[$CCS_LinkBaseAttack] <= 1 && CardType($CombatChain->AttackCard()->ID()) == "AA") return false;
      return true;
    case "astral_etchings_red":
    case "astral_etchings_yellow":
    case "astral_etchings_blue":
      $haveAuraWard = 0;
      $auras = &GetAuras($player);
      foreach ($auras as &$aura) {
        if (HasWard($aura, $player)){
          $haveAuraWard = +1;
        } 
       }
      return $haveAuraWard <= 0;
    case "bloodtorn_bodice":
      $auras = &GetAuras($player);
      return Count($auras) <= 0;
    case "maul_yellow":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (CardNameContains($CombatChain->AttackCard()->ID(), "Crouching Tiger", $player)) return false;
      if ($combatChainState[$CCS_LinkBaseAttack] <= 1 && CardType($CombatChain->AttackCard()->ID()) == "AA") return false;
      return true;
    case "longdraw_halfglove":
      return (count($myHand) + count($myArsenal)) < 2;
    case "shadowrealm_horror_red":
      return $discard->NumCards() < 3;
    case "enigma_new_moon":
      return GetPlayerNumEquipment($player, "DOWN") <= 0;
    case "aurora_shooting_star":
    case "aurora":
      return GetClassState($player, $CS_NumLightningPlayed) == 0;
    case "oscilio_constella_intelligence":
    case "oscilio":
      return SearchCount(SearchMultiZone($player, "MYHAND:type=I")) == 0;
    case "seeds_of_tomorrow_blue":
      return $from == "ARS" || ArsenalEmpty($player);
    case "solar_plexus":
      return count($mySoul) == 0;
    case "hidden_agenda":
      return !ArsenalHasFaceDownArrowCard($player);
    case "flight_path":
      return !HasAimCounter();
    case "well_grounded":
      return SearchCount(SearchBanish($player, talent: "EARTH")) < 4;
    case "twinkle_toes":
      return GetClassState($player, $CS_NumInstantPlayed) <= 0;
    case "hood_of_second_thoughts":
    case "bruised_leather":
    case "four_finger_gloves":
      return !HasTakenDamage($player);
    case "inklined_cloak":
      return !HasAuraWithSigilInName($currentPlayer);
    case "cerebellum_processor_blue":
      if ($from == "PLAY") return $myItems[$index + 2] != 2;
      else return false;
    case "arakni_black_widow":
    case "arakni_funnel_web":
    case "arakni_redback":
      if (!($CombatChain->HasCurrentLink() && ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $currentPlayer))) return true;
      if (SearchHand($currentPlayer, class:"ASSASSIN") == "") return true;
      return false;
    case "arakni_orbweaver":
      if (SearchHand($currentPlayer, class:"ASSASSIN") == "") return true;
      return false;
    case "arakni_tarantula":
      if (!($CombatChain->HasCurrentLink() && SubtypeContains($CombatChain->AttackCard()->ID(), "Dagger", $currentPlayer))) return true;
      if (SearchHand($currentPlayer, class:"ASSASSIN") == "") return true;
      return false;
    case "take_up_the_mantle_yellow":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (HasStealth($CombatChain->AttackCard()->ID()) && DelimStringContains(CardType($CombatChain->AttackCard()->ID()), "AA", true)) return false;
      return true;
    case "tarantula_toxin_red":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (HasStealth($CombatChain->AttackCard()->ID()) && NumCardsBlocking() > 0) return false;
      if (SubtypeContains($CombatChain->AttackCard()->ID(), "Dagger", $currentPlayer)) return false;
      return true;
    case "stains_of_the_redback_red":
    case "stains_of_the_redback_yellow":
    case "stains_of_the_redback_blue":
      return !$CombatChain->HasCurrentLink() || !HasStealth($CombatChain->AttackCard()->ID());
    case "two_sides_to_the_blade_red":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (HasStealth($CombatChain->AttackCard()->ID())) return false;
      if (SubtypeContains($CombatChain->AttackCard()->ID(), "Dagger", $currentPlayer)) return false;
      return true;
    case "hunts_end_red";
      if (!$CombatChain->HasCurrentLink()) return true;
      if (CountAura("fealty", $currentPlayer) < 3) return true;
      $subtype = CardSubType($CombatChain->AttackCard()->ID());
      return $subtype != "Dagger";
    case "long_whisker_loyalty_red":
      if (!$CombatChain->HasCurrentLink()) return true;
      // This next line is based on my interpretation of the card. It seems to require you to pick all 3 modes
      // if you have 3 draconic chain links, and you can't pick the first mode without a dagger attack
      // this could change with release notes
      if (NumDraconicChainLinks() > 2 && !SubtypeContains($CombatChain->CurrentAttack(), "Dagger", $currentPlayer)) return true;
      if (NumDraconicChainLinks() > 0) {
        // make sure you have at least one dagger equipped
        $mainCharacter = &GetPlayerCharacter($mainPlayer);
        for ($i = 0; $i < count($mainCharacter); $i += CharacterPieces()) {
          if (SubtypeContains($mainCharacter[$i], "Dagger", $mainPlayer)) return false;
        }
        return true;
      }
      // you can play it, but it won't do anything
      return false;
    case "affirm_loyalty_red":
    case "endear_devotion_red":
    case "blistering_blade_red":
    case "dynastic_dedication_red":
    case "imperial_intent_red":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!SubtypeContains($CombatChain->CurrentAttack(), "Dagger", $currentPlayer)) return true;
      return false;
    case "brothers_of_flame_red":
      if (NumDraconicChainLinks() < 2) return true;
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!SubtypeContains($CombatChain->CurrentAttack(), "Dagger", $currentPlayer)) return true;
      return false;
    case "scalding_iron_red":
    case "searing_gaze_red":
    case "sizzling_steel_red":
    case "stabbing_pain_red":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!SubtypeContains($CombatChain->CurrentAttack(), "Dagger", $currentPlayer)) return true;
      return false;
    case "sisters_of_fire_red":
      if (NumDraconicChainLinks() < 2) return true;
    case "jagged_edge_red":
      return !$CombatChain->HasCurrentLink() || !TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer);
    case "provoke_blue":
      return !$CombatChain->HasCurrentLink();
    case "diced_red":
    case "diced_yellow":
    case "diced_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      return CardSubType($CombatChain->AttackCard()->ID()) != "Dagger";
    case "dragonscaler_flight_path":
      if(!$CombatChain->HasCurrentLink()) return true;
      if(!TalentContains($CombatChain->AttackCard()->ID(), "DRACONIC", $currentPlayer)) return true;
      return false;
    case "vow_of_vengeance":
      $otherChar = &GetPlayerCharacter($otherPlayer);
      if (!CardNameContains($otherChar[0], "Arakni")) return true;
      break;
    case "hand_of_vengeance":
      $otherChar = &GetPlayerCharacter($otherPlayer);
      if (!CardNameContains($otherChar[0], "Arakni")) return true;
      break;
    case "path_of_vengeance":
      $otherChar = &GetPlayerCharacter($otherPlayer);
      if (!CardNameContains($otherChar[0], "Arakni")) return true;
      break;
    case "oath_of_loyalty_red":
      return GetClassState($currentPlayer, piece: $CS_NumActionsPlayed) > 0;
    case "danger_digits":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!SearchCharacterAliveSubtype($player, "Dagger", true) && SearchCombatChainAttacks($player, subtype:"Dagger") == "") {
        $restriction = "No dagger to throw";
        return true;
      }
      return false;
    case "throw_dagger_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!SearchCharacterAliveSubtype($player, "Dagger", true) && SearchCombatChainAttacks($player, subtype:"Dagger") == "") {
        $restriction = "No dagger to throw";
        return true;
      }
      return false;
    case "starting_point":
      return $combatChainState[$CCS_NumUsedInReactions] == 0;
    case "perforate_yellow":
      // make sure you have at least one dagger equipped
      $mainCharacter = &GetPlayerCharacter($mainPlayer);
      for ($i = 0; $i < count($mainCharacter); $i += CharacterPieces()) {
        if (SubtypeContains($mainCharacter[$i], "Dagger", $mainPlayer)) return false;
      }
      return true;
    case "to_the_point_red":
    case "to_the_point_yellow":
    case "to_the_point_blue":
    case "incision_red":
    case "incision_yellow":
    case "incision_blue":
    case "scar_tissue_red":
    case "scar_tissue_yellow":
    case "scar_tissue_blue":
    case "take_a_stab_red":
    case "take_a_stab_yellow":
    case "take_a_stab_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      return !DelimStringContains(CardSubType($CombatChain->AttackCard()->ID()), "Dagger");
    case "bunker_beard":
      if (!$CombatChain->HasCurrentLink()) return true;
      return (SearchArsenal($currentPlayer, type:"A") == "") && SearchArsenal($currentPlayer, type:"AA");
    case "outed_red":
      return CheckMarked($currentPlayer);
    case "lay_low_yellow":
      return CheckMarked($currentPlayer);
    case "exposed_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (CheckMarked($currentPlayer)) return true;
      return false;
    case "put_in_context_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (AttackValue($CombatChain->AttackCard()->ID()) > 3) return true;
      return false;
    case "nip_at_the_heels_blue":
      if (!$CombatChain->HasCurrentLink()) return true;
      if (AttackValue($CombatChain->AttackCard()->ID()) > 3) return true;
      return false;
    case "war_cry_of_bellona_yellow":
      if ($from == "HAND") return false;
      if (!$CombatChain->HasCurrentLink()) return true;
      if (!CardNameContains($CombatChain->AttackCard()->ID(), "Raydn", $mainPlayer, true)) return true;
      return false;
    case "the_hand_that_pulls_the_strings":
      return ArsenalFaceDownCard($player) == "";
    default:
      return false;
  }
}

function IsDefenseReactionPlayable($cardID, $from)
{
  global $CombatChain, $mainPlayer;
  if (($CombatChain->AttackCard()->ID() == "command_and_conquer_red" || $CombatChain->AttackCard()->ID() == "back_stab_red" || $CombatChain->AttackCard()->ID() == "back_stab_yellow" || $CombatChain->AttackCard()->ID() == "back_stab_blue" || $CombatChain->AttackCard()->ID() == "widowmaker_red" || $CombatChain->AttackCard()->ID() == "widowmaker_yellow" || $CombatChain->AttackCard()->ID() == "widowmaker_blue" || $CombatChain->AttackCard()->ID() == "wreck_havoc_red" || $CombatChain->AttackCard()->ID() == "wreck_havoc_yellow" || $CombatChain->AttackCard()->ID() == "wreck_havoc_blue") && CardType($cardID) == "DR") return false;
  if ($CombatChain->AttackCard()->ID() == "exude_confidence_red") if (!ExudeConfidenceReactionsPlayable()) return false;
  if ($from == "HAND" && CardSubType($CombatChain->AttackCard()->ID()) == "Arrow" && SearchCharacterForCard($mainPlayer, "dreadbore")) return false;
  if (CurrentEffectPreventsDefenseReaction($from)) return false;
  if (SearchCurrentTurnEffects("exude_confidence_red", $mainPlayer)) return false;
  if (SearchCurrentTurnEffects("imperial_seal_of_command_red", $mainPlayer) && CardType($cardID) == "DR") return false;
  return true;
}

function IsAction($cardID, $from="")
{
  if(IsStaticType($cardID, $from)) {
    $abilityType = GetAbilityType($cardID, from: $from);
    if ($abilityType == "A" || $abilityType == "AA") return true;
  }
  else {
    $cardType = CardType($cardID, $from);
    if (DelimStringContains($cardType, "A") || $cardType == "AA") return true;
  }
  return false;
}

function IsActionCard($cardID)
{
  $cardType = CardType($cardID);
  if (DelimStringContains($cardType, "A") || $cardType == "AA") return true;
  return false;
}

function GoesOnCombatChain($phase, $cardID, $from, $currentPlayer)
{
  global $layers, $combatChain;
  switch ($cardID) {
    case "mighty_windup_red":
    case "mighty_windup_yellow":
    case "mighty_windup_blue":
    case "agile_windup_red":
    case "agile_windup_yellow":
    case "agile_windup_blue":
    case "vigorous_windup_red":
    case "vigorous_windup_yellow":
    case "vigorous_windup_blue":
    case "trip_the_light_fantastic_blue":
    case "trip_the_light_fantastic_yellow":
    case "trip_the_light_fantastic_red":
    case "fruits_of_the_forest_blue":
    case "fruits_of_the_forest_yellow":
    case "fruits_of_the_forest_red":
    case "ripple_away_blue":
    case "under_the_trapdoor_blue":
    case "reapers_call_red":
    case "reapers_call_yellow":
    case "reapers_call_blue":
    case "tipoff_red":
    case "tipoff_yellow":
    case "tipoff_blue":
      return ($phase == "B" && count($layers) == 0) || GetResolvedAbilityType($cardID, $from) == "AA";
    case "restless_coalescence_yellow":
      return GetResolvedAbilityType($cardID, $from) == "AA";
    case "shelter_from_the_storm_red":
      return GetResolvedAbilityType($cardID, $from) == "DR";
    case "war_cry_of_bellona_yellow":
      return $phase == "B" || GetResolvedAbilityType($cardID, $from) == "AR";
    case "quickdodge_flexors":
      for ($i = 0; $i < count(value: $combatChain); $i += CombatChainPieces()) {
        if ($combatChain[$i] == $cardID) return false;
      }
      return true;
    case "bunker_beard":
      return $phase == "B";
    default:
      break;
  }
  if (canBeAddedToChainDuringDR($cardID) && $phase == "D") return true;
  if ($phase != "B" && $from == "EQUIP" || $from == "PLAY") $cardType = GetResolvedAbilityType($cardID, $from);
  else if ($phase == "M" && $cardID == "guardian_of_the_shadowrealm_red" && $from == "BANISH") $cardType = GetResolvedAbilityType($cardID, $from);
  else $cardType = CardType($cardID);
  if ($phase == "B" && count($layers) == 0) return true; //Anything you play during these combat phases would go on the chain
  if (DelimStringContains($cardType, "I")) return false; //Instants as yet never go on the combat chain
  if (($phase == "A" || $phase == "D") && DelimStringContains($cardType, "A")) return false; //Non-attacks played as instants never go on combat chain
  if ($cardType == "AR") return true; // Technically wrong, AR goes to the graveyard instead of remaining on the active chain link. CR 2.4.0 - 8.1.2b
  if ($cardType == "DR") return true;
  if (($phase == "M" || $phase == "ATTACKWITHIT") && $cardType == "AA") return true; //If it's an attack action, it goes on the chain
  return false;
}

function IsStaticType($cardType, $from = "", $cardID = "")
{
  if (DelimStringContains($cardType, "C") || DelimStringContains($cardType, "E") || DelimStringContains($cardType, "W") || DelimStringContains($cardType, "D")) return true;
  if ($from == "PLAY") return true;
  if ($from == "ARS" && DelimStringContains($cardType, "M")) return true;
  if ($cardID != "" && $from == "BANISH" && AbilityPlayableFromBanish($cardID)) return true;
  return false;
}

function HasBladeBreak($cardID)
{
  global $defPlayer, $CID_TekloHead, $CID_TekloChest, $CID_TekloArms, $CID_TekloLegs;
  switch ($cardID) {
    case "mask_of_momentum":
    case "fyendals_spring_tunic":
    case "ironrot_helm":
    case "ironrot_plate":
    case "ironrot_gauntlet":
    case "ironrot_legs":
      return true;
    case "skullbone_crosswrap":
      return true;
    case "perch_grapplers":
      return true;
    case "vestige_of_sol":
      return true;
    case "heart_of_ice":
    case "rotten_old_buckler":
    case "new_horizon":
    case "spellbound_creepers":
      return true;
    case "mask_of_the_pouncing_lynx":
    case "silver_palms":
      return true;
    case "ironrot_helm":
    case "ironrot_legs":
      return true;
    case "bone_vizier":
      return true;
    case "coronet_peak":
    case "tiger_stripe_shuko":
    case "crown_of_providence":
      return true;
    case "blazen_yoroi":
    case "hornets_sting":
    case "amethyst_tiara":
      return true;
    case "mask_of_many_faces":
    case "trench_of_sunken_treasure":
    case "wayfinders_crest":
    case "flick_knives":
    case "mask_of_shifting_perspectives":
    case "blade_cuff":
    case "mask_of_malicious_manifestations":
    case "toxic_tips":
      return true;
    case "vambrace_of_determination":
      return SearchCurrentTurnEffects($cardID . "-BB", $defPlayer); //Vambrace of determination
    case "scowling_flesh_bag":
      return true;
    case "frontline_helm":
    case "frontline_plating":
    case "frontline_gauntlets":
    case "frontline_legs":
      return true;
    case $CID_TekloHead:
    case $CID_TekloChest:
    case $CID_TekloArms:
    case $CID_TekloLegs:
      return true;
    case "adaptive_plating":
      return true;
    case "teklo_base_head":
    case "teklo_base_chest":
    case "teklo_base_arms":
    case "teklo_base_legs":
      return true;
    case "evo_command_center_yellow_equip":
    case "evo_engine_room_yellow_equip":
    case "evo_smoothbore_yellow_equip":
    case "evo_thruster_yellow_equip":
      return true;
    case "EVO418":
    case "EVO419":
    case "EVO420":
    case "EVO421":
      return true;
    case "evo_data_mine_yellow_equip":
    case "evo_battery_pack_yellow_equip":
    case "evo_cogspitter_yellow_equip":
    case "evo_charging_rods_yellow_equip":
      return true;
    case "golden_glare":
    case "parry_blade":
      return true;
    case "gauntlet_of_might":
    case "flat_trackers":
    case "vigor_girth":
      return true;
    case "face_adversity":
    case "confront_adversity":
    case "embrace_adversity":
    case "overcome_adversity":
      return true;
    case "headliner_helm":
    case "stadium_centerpiece":
    case "ticket_puncher":
    case "grandstand_legplates":
    case "bloodied_oval":
      return true;
    case "mask_of_recurring_nightmares":
      return true;
    case "twelve_petal_kasaya":
      return true;
    case "heirloom_of_tiger_hide":
      $char = &GetPlayerCharacter($defPlayer);
      $index = FindCharacterIndex($defPlayer, $cardID);
      return $char[$index + 12] == "UP";
    case "stride_of_reprisal":
    case "mask_of_wizened_whiskers":
      return true;
    case "traverse_the_universe":
      return true;
    case "stonewall_gauntlet":
      return true;
    case "helm_of_halos_grace":
    case "bracers_of_bellonas_grace":
    case "warpath_of_winged_grace":
      return true;
    case "helm_of_lignum_vitae":
    case "flash_of_brilliance":
    case "face_purgatory":
      return true;
    case "ollin_ice_cap":
    case "rootbound_trunks":
      return true;
    case "mask_of_deceit":
    case "kabuto_of_imperial_authority":
    case "vow_of_vengeance":
    case "heart_of_vengeance":
    case "hand_of_vengeance":
    case "path_of_vengeance":
    case "blood_splattered_vest":
    case "leap_frog_vocal_sac":
    case "leap_frog_slime_skin":
    case "leap_frog_gloves":
    case "leap_frog_leggings":
    case "red_alert_visor":
    case "red_alert_vest":
    case "red_alert_gloves":
    case "red_alert_boots":
    case "tremorshield_sabatons":
    case "misfire_dampener":
      return true;
    default:
      return false;
  }
}

function HasBattleworn($cardID)
{
  global $defPlayer;
  switch ($cardID) {
    case "scabskin_leathers":
    case "barkbone_strapping":
    case "tectonic_plating":
    case "helm_of_isens_peak":
    case "breaking_scales":
    case "braveforge_bracers":
    case "refraction_bolters":
      return true;
    case "teklo_foundry_heart":
    case "grasp_of_the_arknight":
    case "arcanite_skullcap":
      return true;
    case "breeze_rider_boots":
      return true;
    case "valiant_dynamo":
    case "gallantry_gold":
    case "hooves_of_the_shadowbeast":
    case "aether_ironweave":
      return true;
    case "skull_crushers":
    case "helm_of_sharp_eye":
      return true;
    case "gallantry_gold":
      return true;
    case "beaten_trackers":
    case "seasoned_saviour":
    case "tearing_shuko":
    case "galvanic_bender":
    case "blacktek_whisperers":
    case "mask_of_perdition":
      return true;
    case "redback_shroud":
      return true;
    case "blood_scent":
    case "pouncing_paws":
    case "evo_tekloscope_blue_equip":
    case "evo_energy_matrix_blue_equip":
    case "evo_scatter_shot_blue_equip":
    case "evo_rapid_fire_blue_equip":
      return true;
    case "hyperx3":
      return true;
    case "teklovossen_the_mechropotentb":
    case "evo_tekloscope_blue_equip":
    case "evo_energy_matrix_blue_equip":
    case "evo_scatter_shot_blue_equip":
    case "evo_rapid_fire_blue_equip":
    case "shriek_razors":
      return true;
    case "evo_sentry_base_head_red_equip":
    case "evo_sentry_base_chest_red_equip":
    case "evo_sentry_base_arms_red_equip":
    case "evo_sentry_base_legs_red_equip":
      return true;
    case "monstrous_veil":
    case "hood_of_red_sand":
      return true;
    case "heirloom_of_snake_hide":
      $char = &GetPlayerCharacter($defPlayer);
      $index = FindCharacterIndex($defPlayer, $cardID);
      return $char[$index + 12] == "UP";
    case "arousing_wave":
    case "undertow_stilettos":
      return true;
    case "longdraw_halfglove":
      return true;
    case "hide_tanner":
      return true;
    case "sharp_shooters":
    case "flight_path":
      return true;
    case "heavy_industry_gear_shift":
      return true;
    case "lightning_greaves":
      return true;
    case "aether_bindings_of_the_third_age"://Aether Bindings
      return true;
    case "dragonscaler_flight_path":
      return true;
    default:
      return false;
  }
}

function HasTemper($cardID)
{
  switch ($cardID) {
    case "crater_fist":
    case "courage_of_bladehold":
    case "bloodsheath_skeleta":
      return true;
    case "stalagmite_bastion_of_isenloft":
      return true;
    case "earthlore_bounty":
      return true;
    case "flamescale_furnace":
      return true;
    case "steelbraid_buckler":
    case "nitro_mechanoidb":
      return true;
    case "soulbond_resolve":
    case "bastion_of_unity":
    case "ironsong_versus":
    case "dyadic_carapace":
      return true;
    case "bastion_of_duty":
    case "civic_peak":
    case "civic_duty":
    case "civic_guide":
    case "civic_steps":
      return true;
    case "warband_of_bellona":
    case "evo_steel_soul_memory_blue_equip":
    case "evo_steel_soul_processor_blue_equip":
    case "evo_steel_soul_controller_blue_equip":
    case "evo_steel_soul_tower_blue_equip":
      return true;
    case "apex_bonebreaker":
    case "knucklehead":
    case "raw_meat":
    case "aurum_aegis":
    case "stonewall_impasse":
    case "gauntlets_of_iron_will":
    case "good_time_chapeau":
    case "stand_ground":
    case "grains_of_bloodspill":
    case "prized_galea":
    case "beckon_applause":
    case "evo_magneto_blue_equip":
    case "savage_sash":
    case "heavy_industry_surveillance":
    case "heavy_industry_power_plant":
    case "heavy_industry_ram_stop":
      return true;
    case "gauntlets_of_the_boreal_domain":
      return true;
    case "barkskin_of_the_millennium_tree":
      return true;
    case "tectonic_crust":
      return true;
    default:
      return false;
  }
}

function HasGuardwell($cardID)
{
  switch ($cardID) {
    case "balance_of_justice":
    case "arcanite_fortress":
    case "blade_beckoner_helm":
    case "blade_beckoner_plating":
    case "blade_beckoner_gauntlets":
    case "blade_beckoner_boots":
      return true;
    default:
      return false;
  }
}

function HasPiercing($cardID, $from = "")
{
  // TODO see what breaks this
  // $cardID = substr($cardID, 0, 6);
  switch ($cardID) {
    case "spiders_bite":
    case "spiders_bite":
    case "spiders_bite":
    case "nerve_scalpel":
    case "nerve_scalpel":
    case "orbitoclast":
    case "orbitoclast":
    case "scale_peeler":
    case "scale_peeler": //Weapons with Piercing
    case "graven_call":
    case "hunters_klaive":
      return true;
    case "precision_press_red":
    case "precision_press_yellow":
    case "precision_press_blue":
    case "puncture_red":
    case "puncture_yellow":
    case "puncture_blue": //Warrior NAA + Reactions
    case "visit_the_imperial_forge_red":
    case "visit_the_imperial_forge_yellow":
    case "visit_the_imperial_forge_blue":
      return (!IsPlayRestricted($cardID, $restriction, $from) || IsCombatEffectActive($cardID));
    case "drill_shot_red":
    case "drill_shot_yellow":
    case "drill_shot_blue": // Arrows
      return HasAimCounter();
    default:
      return false;
  }
}

function HasTower($cardID)
{
  switch ($cardID) {
    case "colossal_bearing_red":
    case "lay_down_the_law_red":
    case "smack_of_reality_red":
    case "colossal_bearing_red":
    case "lay_down_the_law_red":
    case "smack_of_reality_red":
      return true;
    default:
      return false;
  }
}

function RequiresDiscard($cardID)
{
  switch ($cardID) {
    case "alpha_rampage_red":
    case "bloodrush_bellow_yellow":
    case "reckless_swing_blue":
    case "breakneck_battery_red":
    case "breakneck_battery_yellow":
    case "breakneck_battery_blue":
    case "savage_feast_red":
    case "savage_feast_yellow":
    case "savage_feast_blue":
    case "savage_swing_red":
    case "savage_swing_yellow":
    case "savage_swing_blue":
    case "wrecker_romp_red":
    case "wrecker_romp_yellow":
    case "wrecker_romp_blue":
    case "primeval_bellow_red":
    case "primeval_bellow_yellow":
    case "primeval_bellow_blue":
    case "barraging_big_horn_red":
    case "barraging_big_horn_yellow":
    case "barraging_big_horn_blue":
    case "swing_fist_think_later_red":
    case "swing_fist_think_later_yellow":
    case "swing_fist_think_later_blue":
    case "savage_beatdown_red":
    case "madcap_charger_red":
    case "madcap_charger_yellow":
    case "madcap_charger_blue":
    case "madcap_muscle_red":
    case "madcap_muscle_yellow":
    case "madcap_muscle_blue":
      return true;
    default:
      return false;
  }
}

function RequiresBanish($cardID)
{
  switch ($cardID) {
    case "expendable_limbs_blue":
    case "ram_raider_red":
    case "ram_raider_yellow":
    case "ram_raider_blue":
    case "shaden_scream_red":
    case "shaden_scream_yellow":
    case "shaden_scream_blue":
    case "shaden_swing_red":
    case "shaden_swing_yellow":
    case "shaden_swing_blue":
    case "tribute_to_demolition_red":
    case "tribute_to_demolition_yellow":
    case "tribute_to_demolition_blue":
    case "tribute_to_the_legions_of_doom_red":
    case "tribute_to_the_legions_of_doom_yellow":
    case "tribute_to_the_legions_of_doom_blue":
      return true;
    default:
      return false;
  }
}

function HasBeatChest($cardID)
{
  switch ($cardID) {
    case "rawhide_rumble_red":
    case "rawhide_rumble_yellow":
    case "rawhide_rumble_blue":
    case "assault_and_battery_red":
    case "assault_and_battery_yellow":
    case "assault_and_battery_blue":
    case "pound_town_red":
    case "pound_town_yellow":
    case "pound_town_blue":
    case "bonebreaker_bellow_red":
    case "bonebreaker_bellow_yellow":
    case "bonebreaker_bellow_blue":
      return true;
    default:
      return false;
  }
}

function ETASteamCounters($cardID)
{
  switch ($cardID) {
    case "aether_sink_yellow":
      return 1;
    case "teklo_core_blue":
    case "convection_amplifier_red":
      return 2;
    case "hyper_driver_red":
      return 3;
    case "dissipation_shield_yellow":
      return 4;
    case "optekal_monocle_blue":
      return 5;
    case "absorption_dome_yellow":
      return 0;
    case "plasma_purifier_red":
      return 0;
    case "dissolution_sphere_yellow":
      return 1;
    case "signal_jammer_blue":
      return 1;
    case "teklo_pounder_blue":
      return 3;
    case "plasma_mainline_red":
      return 5;
    case "hyper_driver_red":
      return 3;
    case "hyper_driver_yellow":
      return 2;
    case "hyper_driver_blue":
      return 1;
    case "prismatic_lens_yellow":
      return 1;
    case "quantum_processor_yellow":
      return 1;
    case "tick_tock_clock_red":
      return 1;
    case "polarity_reversal_script_red":
    case "penetration_script_yellow":
    case "security_script_blue":
      return 1;
    case "backup_protocol_red_red":
    case "backup_protocol_yel_yellow":
    case "backup_protocol_blu_blue":
      return 1;
    case "boom_grenade_red":
    case "boom_grenade_yellow":
    case "boom_grenade_blue":
      return 1;
    case "mini_forcefield_red":
      return 4;
    case "dissolving_shield_red":
    case "mini_forcefield_yellow":
      return 3;
    case "dissolving_shield_yellow":
    case "mini_forcefield_blue":
      return 2;
    case "dissolving_shield_blue":
      return 1;
    case "grinding_gears_blue":
      return 1;
    case "hadron_collider_red":
      return 4;
    case "hadron_collider_yellow":
      return 3;
    case "hadron_collider_blue":
      return 2;
    case "overload_script_red":
    case "mhz_script_yellow":
    case "autosave_script_blue":
      return 1;
    case "cerebellum_processor_blue":
      return 2;
    case "null_time_zone_blue":
      return 2;
    default:
      return 0;
  }
}

function AbilityHasGoAgain($cardID)
{
  global $currentPlayer;
  $cardID = ShiyanaCharacter($cardID);
  $set = CardSet($cardID);
  $class = CardClass($cardID);
  $subtype = CardSubtype($cardID);
  $abilityType = GetResolvedAbilityType($cardID);
  if ($class == "ILLUSIONIST" && DelimStringContains($subtype, "Aura") && SearchCharacterForCard($currentPlayer, "iris_of_reality") && $abilityType == "AA") return true;
  if ($set == "WTR") return WTRAbilityHasGoAgain($cardID);
  else if ($set == "ARC") return ARCAbilityHasGoAgain($cardID);
  else if ($set == "CRU") return CRUAbilityHasGoAgain($cardID);
  else if ($set == "MON") return MONAbilityHasGoAgain($cardID);
  else if ($set == "ELE") return ELEAbilityHasGoAgain($cardID);
  else if ($set == "EVR") return EVRAbilityHasGoAgain($cardID);
  else if ($set == "UPR") return UPRAbilityHasGoAgain($cardID);
  else if ($set == "DYN") return DYNAbilityHasGoAgain($cardID);
  else if ($set == "OUT") return OUTAbilityHasGoAgain($cardID);
  else if ($set == "DTD") return DTDAbilityHasGoAgain($cardID);
  else if ($set == "TCC") return TCCAbilityHasGoAgain($cardID);
  else if ($set == "EVO") return EVOAbilityHasGoAgain($cardID);
  else if ($set == "HVY") return HVYAbilityHasGoAgain($cardID);
  else if ($set == "AKO") return AKOAbilityHasGoAgain($cardID);
  else if ($set == "AAZ") return AAZAbilityHasGoAgain($cardID);
  else if ($set == "ROG") return ROGUEAbilityHasGoAgain($cardID);
  else if ($set == "ROS") return ROSAbilityHasGoAgain($cardID);
  else if ($set == "AIO") return AIOAbilityHasGoAgain($cardID);
  else if ($set == "AJV") return AJVAbilityHasGoAgain($cardID);
  else if ($set == "HNT") return HNTAbilityHasGoAgain($cardID);
  switch ($cardID) {
    case "blossom_of_spring":
    case "blossom_of_spring":
    case "blossom_of_spring":
      return true;
    default:
      return false;
  }
}

function DoesEffectGrantOverpower($cardID): bool
{
  $cardID = ShiyanaCharacter($cardID);
  return match ($cardID) {
    "betsy_skin_in_the_game", "betsy", "the_golden_son_yellow", "down_but_not_out_red", "down_but_not_out_yellow", "down_but_not_out_blue", "log_fall_red", "log_fall_yellow", "machinations_of_dominion_blue" => true,
    default => false,
  };
}

function DoesEffectGrantDominate($cardID): bool
{
  global $combatChainState, $CCS_AttackFused;
  $cardID = ShiyanaCharacter($cardID);
  switch ($cardID) {
    case "bravo_showstopper":
    case "bravo":
    case "regurgitating_slog_red":
    case "regurgitating_slog_yellow":
    case "regurgitating_slog_blue":
    case "pedal_to_the_metal_red":
    case "pedal_to_the_metal_yellow":
    case "pedal_to_the_metal_blue":
    case "convection_amplifier_red":
    case "azalea_ace_in_the_hole":
    case "azalea":
    case "predatory_assault_red":
    case "predatory_assault_yellow":
    case "predatory_assault_blue":
    case "emerging_dominance_red":
    case "emerging_dominance_yellow":
    case "emerging_dominance_blue":
    case "push_forward_red-2":
    case "push_forward_yellow-2":
    case "push_forward_blue-2":
    case "high_speed_impact_red":
    case "high_speed_impact_yellow":
    case "high_speed_impact_blue":
    case "spill_blood_red":
    case "writhing_beast_hulk_red":
    case "writhing_beast_hulk_yellow":
    case "writhing_beast_hulk_blue":
    case "convulsions_from_the_bellows_of_hell_red":
    case "convulsions_from_the_bellows_of_hell_yellow":
    case "convulsions_from_the_bellows_of_hell_blue":
    case "consuming_aftermath_red":
    case "consuming_aftermath_yellow":
    case "consuming_aftermath_blue":
    case "pulping_red":
    case "pulping_yellow":
    case "pulping_blue":
    case "pound_for_pound_red":
    case "pound_for_pound_yellow":
    case "pound_for_pound_blue":
    case "lady_barthimont":
    case "oaken_old_red":
    case "glacial_footsteps_red":
    case "glacial_footsteps_yellow":
    case "glacial_footsteps_blue":
    case "shiver-2":
    case "flake_out_red":
    case "flake_out_yellow":
    case "flake_out_blue":
    case "flashfreeze_red-DOMATK":
    case "entwine_ice_red":
    case "entwine_ice_yellow":
    case "entwine_ice_blue":
    case "polar_blast_red":
    case "polar_blast_yellow":
    case "polar_blast_blue":
    case "tear_asunder_blue":
    case "bravo_star_of_the_show":
    case "valda_brightaxe":
    case "rise_up_red":
    case "buckle_blue":
    case "ROGUE710-DO":
    case "figment_of_tenacity_yellow":
    case "metis_archangel_of_tenacity":
    case "crumble_to_eternity_blue":
      return true;
    case "weave_ice_red":
    case "weave_ice_yellow":
    case "weave_ice_blue":
      return $combatChainState[$CCS_AttackFused] == 1;
    case "murky_water_red":
      return true;
    case "stone_rain_red":
      return true;
    case "gauntlets_of_the_boreal_domain-I":
      return true;
    default:
      return false;
  }
}

function CharacterNumUsesPerTurn($cardID)
{
  switch ($cardID) {
    case "bravo_showstopper":
    case "bravo":
      return 999;
    case "voltaire_strike_twice":
      return 2;
    case "helios_mitre":
      return 999;
    case "emperor_dracai_of_aesir":
    case "seerstone":
      return 999;
    case "nitro_mechanoida":
      return 999;
    case "barbed_castaway":
      return 2;
    case "stasis_cell_blue":
      return 999;
    case "teklovossen_the_mechropotent":
      return 999;
    case "nuu_alluring_desire":
    case "nuu":
    case "enigma_new_moon":
      return 999;
    case "sanctuary_of_aria":
      return 999;
    case "quickdodge_flexors":
      return 999;
    default:
      return 1;
  }
}

//Active (2 = Always Active, 1 = Yes, 0 = No)
function CharacterDefaultActiveState($cardID)
{
  switch ($cardID) {
    case "refraction_bolters":
      return 1;
    // case "fyendals_spring_tunic":
    //   return 0;
    case "vest_of_the_first_fist":
      return 1;
    case "breeze_rider_boots":
    case "metacarpus_node":
      return 1;
    case "hooves_of_the_shadowbeast":
      return 1;
    case "plume_of_evergrowth":
    case "shock_charmers":
    case "mark_of_lightning":
      return 1;
    case "halo_of_illumination":
    case "dream_weavers":
    case "ebon_fold":
    case "MON400":
    case "MON401":
    case "MON402":
      return 1;
    case "spell_fray_tiara":
    case "spell_fray_cloak":
    case "spell_fray_gloves":
    case "spell_fray_leggings":
      return 1;
    case "mask_of_the_pouncing_lynx":
      return 1;
    case "silken_form":
    case "heat_wave":
    case "conduit_of_frostburn":
    case "quelling_robe":
    case "quelling_sleeves":
    case "quelling_slippers":
      return 0;
    case "beaten_trackers":
      return 1;
    case "quiver_of_abyssal_depths":
    case "quiver_of_rustling_leaves":
    case "driftwood_quiver":
      return 1;
    case "shroud_of_darkness":
    case "cloak_of_darkness":
    case "grasp_of_darkness":
    case "dance_of_darkness":
      return 0;
    case "levia_redeemed":
      return 0;
    case "evo_circuit_breaker_red_equip":
    case "evo_atom_breaker_red_equip":
    case "evo_face_breaker_red_equip":
    case "evo_mach_breaker_red_equip":
      return 1;
    case "hide_tanner":
      return 1;
    case "grains_of_bloodspill":
    case "meridian_pathway":
    case "longdraw_halfglove":
      return 1;
    case "aether_crackers":
    case "hard_knuckle":
      return 1;
    case "verdance_thorn_of_the_rose":
    case "verdance":
    case "arcanite_fortress":
    case "widow_veil_respirator":
    case "widow_back_abdomen":
    case "widow_claw_tarsus":
    case "widow_web_crawler":
      return 1;
    case "blood_splattered_vest":
      return 1;
    case "leap_frog_vocal_sac"://leapfrog equipment
    case "leap_frog_slime_skin":
    case "leap_frog_gloves":
    case "leap_frog_leggings":
      return 1;
    default:
      return 2;
  }
}

//Hold priority for triggers (2 = Always hold, 1 = Hold, 0 = Don't Hold)
function AuraDefaultHoldTriggerState($cardID): int
{
  return match ($cardID) {
    "forged_for_war_yellow", "show_time_blue", "blessing_of_deliverance_red", "blessing_of_deliverance_yellow", "blessing_of_deliverance_blue", "emerging_power_red", "emerging_power_yellow", "emerging_power_blue", "stonewall_confidence_red", "stonewall_confidence_yellow", "stonewall_confidence_blue",
    "seismic_surge", "eloquence", "tome_of_aeo_blue", "fog_down_yellow", "sigil_of_protection_red", "sigil_of_protection_yellow", "sigil_of_protection_blue", "runeblood_incantation_red", "runeblood_incantation_yellow", "runeblood_incantation_blue", "pyroglyphic_protection_red",
    "pyroglyphic_protection_yellow", "pyroglyphic_protection_blue", "emerging_avalanche_red", "emerging_avalanche_yellow", "emerging_avalanche_blue", "strength_of_sequoia_red", "strength_of_sequoia_yellow", "strength_of_sequoia_blue", "embolden_red", "embolden_yellow", "embolden_blue",
    "embodiment_of_earth", "embodiment_of_lightning", "frostbite", "stamp_authority_blue", "towering_titan_red", "towering_titan_yellow", "towering_titan_blue", "emerging_dominance_red", "emerging_dominance_yellow", "emerging_dominance_blue", "zen_state",
    "runeblood_barrier_yellow", "soul_shackle", "channel_mount_isen_blue" => 0,
    "runechant", "spellbane_aegis", "spellbane_aegis" => 1,
    default => 2
  };
}

function ItemDefaultHoldTriggerState($cardID)
{
  switch ($cardID) {
    case "teklo_core_blue":
    case "dissipation_shield_yellow":
    case "talisman_of_dousing_yellow":
    case "dissolution_sphere_yellow":
    case "signal_jammer_blue":
      return 1;
    default:
      return 2;
  }
}

function IsCharacterActive($player, $index)
{
  $character = &GetPlayerCharacter($player);
  return $character[$index + 9] == "1" || $character[$index + 9] == "2";
}

function HasReprise($cardID)
{
  switch ($cardID) {
    case "glint_the_quicksilver_blue":
    case "rout_red":
    case "singing_steelblade_yellow":
    case "overpower_red":
    case "overpower_yellow":
    case "overpower_blue":
    case "ironsong_response_red":
    case "ironsong_response_yellow":
    case "ironsong_response_blue":
    case "biting_blade_red":
    case "biting_blade_yellow":
    case "biting_blade_blue":
    case "stroke_of_foresight_red":
    case "stroke_of_foresight_yellow":
    case "stroke_of_foresight_blue":
    case "unified_decree_yellow":
    case "out_for_blood_red":
    case "out_for_blood_yellow":
    case "out_for_blood_blue":
      return true;
    default:
      return false;
  }
}

//Is it active AS OF THIS MOMENT?
function RepriseActive()
{
  global $currentPlayer, $mainPlayer;
  if ($currentPlayer == $mainPlayer) return CachedNumDefendedFromHand() > 0;
  else return 0;
}

function HasCombo($cardID)
{
  switch ($cardID) {
    case "lord_of_wind_blue":
    case "mugenshi_release_yellow":
    case "hurricane_technique_yellow":
    case "pounding_gale_red":
    case "fluster_fist_red":
    case "fluster_fist_yellow":
    case "fluster_fist_blue":
    case "blackout_kick_red":
    case "blackout_kick_yellow":
    case "blackout_kick_blue":
    case "open_the_center_red":
    case "open_the_center_yellow":
    case "open_the_center_blue":
    case "rising_knee_thrust_red":
    case "rising_knee_thrust_yellow":
    case "rising_knee_thrust_blue":
    case "whelming_gustwave_red":
    case "whelming_gustwave_yellow":
    case "whelming_gustwave_blue":
      return true;
    case "find_center_blue":
    case "flood_of_force_yellow":
    case "herons_flight_red":
    case "crane_dance_red":
    case "crane_dance_yellow":
    case "crane_dance_blue":
    case "rushing_river_red":
    case "rushing_river_yellow":
    case "rushing_river_blue":
      return true;
    case "break_tide_yellow":
    case "winds_of_eternity_blue":
    case "hundred_winds_red":
    case "hundred_winds_yellow":
    case "hundred_winds_blue":
      return true;
    case "tiger_swipe_red":
    case "pouncing_qi_red":
    case "pouncing_qi_yellow":
    case "pouncing_qi_blue":
    case "qi_unleashed_red":
    case "qi_unleashed_yellow":
    case "qi_unleashed_blue":
      return true;
    case "cyclone_roundhouse_yellow":
    case "dishonor_blue":
    case "bonds_of_ancestry_red":
    case "bonds_of_ancestry_yellow":
    case "bonds_of_ancestry_blue":
    case "recoil_red":
    case "recoil_yellow":
    case "recoil_blue":
    case "spinning_wheel_kick_red":
    case "spinning_wheel_kick_yellow":
    case "spinning_wheel_kick_blue":
    case "back_heel_kick_red":
    case "back_heel_kick_yellow":
    case "back_heel_kick_blue":
    case "descendent_gustwave_red":
    case "descendent_gustwave_yellow":
    case "descendent_gustwave_blue":
    case "onetwo_punch_red":
    case "onetwo_punch_yellow":
    case "onetwo_punch_blue":
    case "mauling_qi_red":
      return true;
    case "chase_the_tail_red":
    case "aspect_of_tiger_body_red":
    case "aspect_of_tiger_soul_yellow":
    case "aspect_of_tiger_mind_blue":
    case "breed_anger_red":
    case "breed_anger_yellow":
    case "breed_anger_blue":
      return true;
    case "gustwave_of_the_second_wind_red":
      return true;
    case "retrace_the_past_blue"://retrace the past
      return true;
    default:
      return false;
  }
}

function ComboActive($cardID = "")
{
  global $CombatChain, $chainLinkSummary, $mainPlayer, $chainLinks;
  if ($cardID == "" && $CombatChain->HasCurrentLink()) $cardID = $CombatChain->AttackCard()->ID();
  if ($cardID == "") return false;
  if (count($chainLinkSummary) == 0) return false;//No combat active if no previous chain links
  $lastAttackNames = explode(",", $chainLinkSummary[count($chainLinkSummary) - ChainLinkSummaryPieces() + 4]);
  for ($i = 0; $i < count($lastAttackNames); ++$i) {
    $lastAttackName = GamestateUnsanitize($lastAttackNames[$i]);
    if (SearchCurrentTurnEffects("amnesia_red", $mainPlayer)) $lastAttackName = "";
    switch ($cardID) {
      case "lord_of_wind_blue":
        if ($lastAttackName == "Mugenshi: RELEASE") return true;
        break;
      case "mugenshi_release_yellow":
        if ($lastAttackName == "Whelming Gustwave") return true;
        break;
      case "hurricane_technique_yellow":
        if ($lastAttackName == "Rising Knee Thrust") return true;
        break;
      case "pounding_gale_red":
        if ($lastAttackName == "Open the Center") return true;
        break;
      case "fluster_fist_red":
      case "fluster_fist_yellow":
      case "fluster_fist_blue":
        if ($lastAttackName == "Open the Center") return true;
        break;
      case "blackout_kick_red":
      case "blackout_kick_yellow":
      case "blackout_kick_blue":
        if ($lastAttackName == "Rising Knee Thrust") return true;
        break;
      case "open_the_center_red":
      case "open_the_center_yellow":
      case "open_the_center_blue":
        if ($lastAttackName == "Head Jab") return true;
        break;
      case "rising_knee_thrust_red":
      case "rising_knee_thrust_yellow":
      case "rising_knee_thrust_blue":
        if ($lastAttackName == "Leg Tap") return true;
        break;
      case "whelming_gustwave_red":
      case "whelming_gustwave_yellow":
      case "whelming_gustwave_blue":
        if ($lastAttackName == "Surging Strike") return true;
        break;
      case "find_center_blue":
        if ($lastAttackName == "Crane Dance") return true;
        break;
      case "flood_of_force_yellow":
        if ($lastAttackName == "Rushing River" || $lastAttackName == "Flood of Force") return true;
        break;
      case "herons_flight_red":
        if ($lastAttackName == "Crane Dance") return true;
        break;
      case "crane_dance_red":
      case "crane_dance_yellow":
      case "crane_dance_blue":
        if ($lastAttackName == "Soulbead Strike") return true;
        break;
      case "rushing_river_red":
      case "rushing_river_yellow":
      case "rushing_river_blue":
        if ($lastAttackName == "Torrent of Tempo") return true;
        break;
      case "break_tide_yellow":
        if ($lastAttackName == "Rushing River" || $lastAttackName == "Flood of Force") return true;
        break;
      case "winds_of_eternity_blue":
        if ($lastAttackName == "Hundred Winds") return true;
        break;
      case "hundred_winds_red":
      case "hundred_winds_yellow":
      case "hundred_winds_blue":
        if ($lastAttackName == "Hundred Winds") return true;
        break;
      case "tiger_swipe_red":
      case "pouncing_qi_red":
      case "pouncing_qi_yellow":
      case "pouncing_qi_blue":
      case "qi_unleashed_red":
      case "qi_unleashed_yellow":
      case "qi_unleashed_blue":
        if ($lastAttackName == "Crouching Tiger") return true;
        break;
      case "cyclone_roundhouse_yellow":
        if ($lastAttackName == "Spinning Wheel Kick") return true;
        break;
      case "dishonor_blue":
        if ($lastAttackName == "Bonds of Ancestry") return true;
        break;
      case "bonds_of_ancestry_red":
      case "bonds_of_ancestry_yellow":
      case "bonds_of_ancestry_blue":
        if (str_contains($lastAttackName, "Gustwave")) return true;
        break;
      case "recoil_red":
      case "recoil_yellow":
      case "recoil_blue":
        if ($lastAttackName == "Head Jab") return true;
        break;
      case "spinning_wheel_kick_red":
      case "spinning_wheel_kick_yellow":
      case "spinning_wheel_kick_blue":
        if ($lastAttackName == "Twin Twisters" || $lastAttackName == "Spinning Wheel Kick") return true;
        break;
      case "back_heel_kick_red":
      case "back_heel_kick_yellow":
      case "back_heel_kick_blue":
        if ($lastAttackName == "Twin Twisters") return true;
        break;
      case "descendent_gustwave_red":
      case "descendent_gustwave_yellow":
      case "descendent_gustwave_blue":
        if ($lastAttackName == "Surging Strike") return true;
        break;
      case "onetwo_punch_red":
      case "onetwo_punch_yellow":
      case "onetwo_punch_blue":
        if ($lastAttackName == "Head Jab") return true;
        break;
      case "mauling_qi_red":
        if ($lastAttackName == "Crouching Tiger") return true;
        break;
      case "chase_the_tail_red":
        if ($lastAttackName == "Crouching Tiger") return true;
        break;
      case "aspect_of_tiger_body_red":
        return ColorContains($chainLinks[count($chainLinks) - 1][0], 1, $mainPlayer);
      case "aspect_of_tiger_soul_yellow":
        return ColorContains($chainLinks[count($chainLinks) - 1][0], 2, $mainPlayer);
      case "aspect_of_tiger_mind_blue":
        return ColorContains($chainLinks[count($chainLinks) - 1][0], 3, $mainPlayer);
      case "breed_anger_red":
      case "breed_anger_yellow":
      case "breed_anger_blue":
        if ($lastAttackName == "Crouching Tiger") return true;
        break;
      case "gustwave_of_the_second_wind_red":
        if ($lastAttackName == "Surging Strike") return true;
        break;
      case "retrace_the_past_blue":
        if (str_contains($lastAttackName, "Gustwave")) return true;
        break;
      default:
        break;
    }
  }
  return false;
}

function HasBloodDebt($cardID)
{
  global $currentPlayer;
  $char = GetPlayerCharacter($currentPlayer);
  if ($char[0] == "levia_redeemed") return false;
  switch ($cardID) {
    case "deep_rooted_evil_yellow";
    case "mark_of_the_beast_yellow";
    case "shadow_of_blasmophet_red";
    case "endless_maw_red":
    case "endless_maw_yellow":
    case "endless_maw_blue";
    case "writhing_beast_hulk_red":
    case "writhing_beast_hulk_yellow":
    case "writhing_beast_hulk_blue";
    case "boneyard_marauder_red":
    case "boneyard_marauder_yellow":
    case "boneyard_marauder_blue";
    case "deadwood_rumbler_red":
    case "deadwood_rumbler_yellow":
    case "deadwood_rumbler_blue";
    case "dread_screamer_red":
    case "dread_screamer_yellow":
    case "dread_screamer_blue";
    case "graveling_growl_red":
    case "graveling_growl_yellow":
    case "graveling_growl_blue";
    case "hungering_slaughterbeast_red":
    case "hungering_slaughterbeast_yellow":
    case "hungering_slaughterbeast_blue";
    case "shadow_of_ursur_blue":
    case "invert_existence_blue":
    case "unhallowed_rites_red":
    case "unhallowed_rites_yellow":
    case "unhallowed_rites_blue":
    case "seeping_shadows_red":
    case "seeping_shadows_yellow":
    case "seeping_shadows_blue":
    case "bounding_demigon_red":
    case "bounding_demigon_yellow":
    case "bounding_demigon_blue":
    case "piercing_shadow_vise_red":
    case "piercing_shadow_vise_yellow":
    case "piercing_shadow_vise_blue":
    case "rift_bind_red":
    case "rift_bind_yellow":
    case "rift_bind_blue":
    case "rifted_torment_red":
    case "rifted_torment_yellow":
    case "rifted_torment_blue":
    case "rip_through_reality_red":
    case "rip_through_reality_yellow":
    case "rip_through_reality_blue":
    case "seeds_of_agony_red":
    case "seeds_of_agony_yellow":
    case "seeds_of_agony_blue":
    case "carrion_husk":
    case "mutated_mass_blue":
    case "guardian_of_the_shadowrealm_red":
    case "tome_of_torment_red":
    case "howl_from_beyond_red":
    case "howl_from_beyond_yellow":
    case "howl_from_beyond_blue":
    case "ghostly_visit_red":
    case "ghostly_visit_yellow":
    case "ghostly_visit_blue":
    case "void_wraith_red":
    case "void_wraith_yellow":
    case "void_wraith_blue":
    case "hell_hammer":
    case "spoiled_skull":
    case "diabolic_offering_blue":
    case "shaden_death_hydra_yellow":
    case "slithering_shadowpede_red":
    case "ram_raider_red":
    case "ram_raider_yellow":
    case "ram_raider_blue":
    case "wall_breaker_red":
    case "wall_breaker_yellow":
    case "wall_breaker_blue":
    case "battlefield_breaker_red":
    case "battlefield_breaker_yellow":
    case "battlefield_breaker_blue":
    case "shaden_swing_red":
    case "shaden_swing_yellow":
    case "shaden_swing_blue":
    case "tribute_to_demolition_red":
    case "tribute_to_demolition_yellow":
    case "tribute_to_demolition_blue":
    case "tribute_to_the_legions_of_doom_red":
    case "tribute_to_the_legions_of_doom_yellow":
    case "tribute_to_the_legions_of_doom_blue":
    case "grimoire_of_the_haunt":
    case "widespread_annihilation_blue":
    case "widespread_destruction_yellow":
    case "widespread_ruin_red":
    case "funeral_moon_red":
    case "requiem_for_the_damned_red":
    case "deathly_delight_red":
    case "deathly_delight_yellow":
    case "deathly_delight_blue":
    case "deathly_wail_red":
    case "deathly_wail_yellow":
    case "deathly_wail_blue":
    case "rift_skitter_red":
    case "rift_skitter_yellow":
    case "rift_skitter_blue":
    case "vantom_banshee_red":
    case "vantom_banshee_yellow":
    case "vantom_banshee_blue":
    case "vantom_wraith_red":
    case "vantom_wraith_yellow":
    case "vantom_wraith_blue":
    case "putrid_stirrings_red":
    case "putrid_stirrings_yellow":
    case "putrid_stirrings_blue":
    case "shroud_of_darkness":
    case "cloak_of_darkness":
    case "grasp_of_darkness":
    case "dance_of_darkness":
    case "dabble_in_darkness_red":
    case "chains_of_mephetis_blue":
    case "dimenxxional_vortex":
    case "hungering_demigon_red":
    case "hungering_demigon_yellow":
    case "hungering_demigon_blue":
    case "grim_feast_red":
    case "grim_feast_yellow":
    case "grim_feast_blue":
    case "vile_inquisition_red":
    case "vile_inquisition_yellow":
    case "vile_inquisition_blue":
    case "soul_butcher_red":
    case "soul_butcher_yellow":
    case "soul_butcher_blue":
    case "soul_cleaver_red":
    case "soul_cleaver_yellow":
    case "soul_cleaver_blue":
    case "shadowrealm_horror_red":
    case "eloquent_eulogy_red":
    case "cull_red":
          return true;
    default:
      return false;
  }
}

function HasRunegate($cardID)
{
  switch ($cardID) {
    case "widespread_annihilation_blue":
    case "widespread_destruction_yellow":
    case "widespread_ruin_red":
    case "deathly_delight_red":
    case "deathly_delight_yellow":
    case "deathly_delight_blue":
    case "deathly_wail_red":
    case "deathly_wail_yellow":
    case "deathly_wail_blue":
    case "rift_skitter_red":
    case "rift_skitter_yellow":
    case "rift_skitter_blue":
    case "vantom_banshee_red":
    case "vantom_banshee_yellow":
    case "vantom_banshee_blue":
    case "vantom_wraith_red":
    case "vantom_wraith_yellow":
    case "vantom_wraith_blue":
    case "eloquent_eulogy_red":
      return true;
    default:
      return false;
  }
}

function PlayableFromBanish($cardID, $mod = "", $nonLimitedOnly = false, $player = "")
{
  global $currentPlayer, $CS_NumNonAttackCards, $CS_Num6PowBan;
  if ($player == "") $player = $currentPlayer;
  $mod = explode("-", $mod)[0];
  if ($mod == "TRAPDOOR") return SubtypeContains($cardID, "Trap", $currentPlayer);
  if (isFaceDownMod($mod)) return false;
  if ($mod == "TCL" || $mod == "TT" || $mod == "TCC" || $mod == "NT" || $mod == "INST" || $mod == "spew_shadow_red" || $mod == "sonic_boom_yellow" || $mod == "blossoming_spellblade_red") return true;
  if ($mod == "shadowrealm_horror_red" && SearchCurrentTurnEffects("shadowrealm_horror_red-3", $player) && CardType($cardID) != "E") return true;
  if (HasRunegate($cardID) && SearchCount(SearchAurasForCard("runechant", $player, false)) >= CardCost($cardID, "BANISH")) return true;
  $char = &GetPlayerCharacter($player);
  if (SubtypeContains($cardID, "Evo") && ($char[0] == "professor_teklovossen" || $char[0] == "teklovossen_esteemed_magnate" || $char[0] == "teklovossen") && $char[1] < 3) return true;
  if (!$nonLimitedOnly && $char[0] == "levia_redeemed" && SearchCurrentTurnEffects("levia_redeemed", $player) && HasBloodDebt($cardID) && $char[1] < 3 && !TypeContains($cardID, "E") && !TypeContains($cardID, "W")) return true;
  switch ($cardID) {
    case "deep_rooted_evil_yellow":
      return GetClassState($player, $CS_Num6PowBan) > 0;
    case "shadow_of_ursur_blue":
    case "invert_existence_blue":
      return true;
    case "unhallowed_rites_red":
    case "unhallowed_rites_yellow":
    case "unhallowed_rites_blue":
      return GetClassState($player, $CS_NumNonAttackCards) > 0;
    case "seeping_shadows_red":
    case "seeping_shadows_yellow":
    case "seeping_shadows_blue":
      return true;
    case "bounding_demigon_red":
    case "bounding_demigon_yellow":
    case "bounding_demigon_blue":
      return GetClassState($player, $CS_NumNonAttackCards) > 0;
    case "piercing_shadow_vise_red":
    case "piercing_shadow_vise_yellow":
    case "piercing_shadow_vise_blue":
      return true;
    case "rift_bind_red":
    case "rift_bind_yellow":
    case "rift_bind_blue":
      return true;
    case "rifted_torment_red":
    case "rifted_torment_yellow":
    case "rifted_torment_blue":
      return true;
    case "rip_through_reality_red":
    case "rip_through_reality_yellow":
    case "rip_through_reality_blue":
      return true;
    case "seeds_of_agony_red":
    case "seeds_of_agony_yellow":
    case "seeds_of_agony_blue":
      return true;
    case "eclipse_blue":
    case "mutated_mass_blue":
    case "tome_of_torment_red":
    case "howl_from_beyond_red":
    case "howl_from_beyond_yellow":
    case "howl_from_beyond_blue":
    case "ghostly_visit_red":
    case "ghostly_visit_yellow":
    case "ghostly_visit_blue":
    case "void_wraith_red":
    case "void_wraith_yellow":
    case "void_wraith_blue":
      return true;
    case "requiem_for_the_damned_red":
    case "putrid_stirrings_red":
    case "putrid_stirrings_yellow":
    case "putrid_stirrings_blue":
    case "grim_feast_red":
    case "grim_feast_yellow":
    case "grim_feast_blue":
    case "funeral_moon_red":
    case "chains_of_mephetis_blue":
    case "dimenxxional_vortex":
      return true;
    case "hungering_demigon_red":
    case "hungering_demigon_yellow":
    case "hungering_demigon_blue":
      $soul = &GetSoul($player == 1 ? 2 : 1);
      return count($soul) > 0;
    case "vile_inquisition_red":
    case "vile_inquisition_yellow":
    case "vile_inquisition_blue":
      return true;
    case "cull_red":
      return true;
    default:
      break;
  }
  return false;
}

function AbilityPlayableFromBanish($cardID, $mod = "")
{
  global $currentPlayer, $mainPlayer;
  $mod = explode("-", $mod)[0];
  if (isFaceDownMod($mod)) return false;
  switch ($cardID) {
    case "guardian_of_the_shadowrealm_red":
      return $currentPlayer == $mainPlayer;
    default:
      return false;
  }
}

function PlayableFromOtherPlayerBanish($cardID, $mod = "", $player = "")
{
  global $currentPlayer;
  $mod = explode("-", $mod)[0];
  if ($player == "") $player = $currentPlayer;
  $otherPlayer = $player == 1 ? 2 : 1;
  if (isFaceDownMod($mod)) return false;
  if (ColorContains($cardID, 3, $otherPlayer) && (SearchCurrentTurnEffects("nuu_alluring_desire", $player) || SearchCurrentTurnEffects("nuu", $player))) return true;
  if ($mod == "NTFromOtherPlayer" || $mod == "TTFromOtherPlayer" || $mod == "TCCGorgonsGaze") return true;
  else return false;
}

function PlayableFromGraveyard($cardID)
{
  switch ($cardID) {
    case "graven_call":
      return true;
    default:
      return false;
  }
}

function RequiresDieRoll($cardID, $from, $player): bool
{
  global $turn;
  if (GetDieRoll($player) > 0) return false;
  if ($turn[0] == "B") return false;
  $type = CardType($cardID);
  if ($type == "AA" && (GetResolvedAbilityType($cardID) == "" || GetResolvedAbilityType($cardID) == "AA") && AttackValue($cardID) >= 6 && (SearchCharacterActive($player, "kayo_berserker_runt") || SearchCurrentTurnEffects("kayo_berserker_runt-SHIYANA", $player))) return true;
  return match ($cardID) {
    "crazy_brew_blue" => $from == "PLAY",
    "scabskin_leathers", "barkbone_strapping", "bone_head_barrier_yellow", "argh_smash_yellow", "rolling_thunder_red", "bad_beats_red", "bad_beats_yellow", "bad_beats_blue", "knucklehead" => true,
    default => false
  };
}

function SpellVoidAmount($cardID, $player): int
{
  if ($cardID == "runechant" && SearchCurrentTurnEffects("amethyst_tiara", $player)) return 1;
  return match ($cardID) {
    "arcanite_fortress" => SearchCount(SearchMultiZone($player, "MYCHAR:type=E;nameIncludes=Arcanite")),
    "shock_charmers", "ebon_fold", "halo_of_illumination" => 2,
    "dream_weavers", "talisman_of_dousing_yellow", "MON400", "MON401", "MON402", "spellbane_aegis", "spell_fray_tiara", "spell_fray_cloak", "spell_fray_gloves", "spell_fray_leggings" => 1,
    "widow_veil_respirator", "widow_back_abdomen", "widow_claw_tarsus", "widow_web_crawler" => 1,
    default => 0
  };
}

function IsSpecialization($cardID): bool
{
  return GeneratedIsSpecialization($cardID) == "true";
}

function Is1H($cardID): bool|int
{
  if($cardID == "gavel_of_natural_order") return true;
  if (SubtypeContains($cardID, "Off-Hand")) return true;
  return GeneratedIs1H($cardID);
}

function AbilityPlayableFromCombatChain($cardID): bool
{
  return match ($cardID) {
    "exude_confidence_red", "rally_the_rearguard_red", "rally_the_rearguard_yellow", "rally_the_rearguard_blue", "shock_striker_red", "shock_striker_yellow", "shock_striker_blue", "firebreathing_red" => true,
    default => false
  };
}

function CardCaresAboutPitch($cardID): bool
{
  $cardID = ShiyanaCharacter($cardID);
  return match ($cardID) {
    "oldhim_grandfather_of_eternity", "oldhim", "winters_wail", "annals_of_sutcliffe", "cryptic_crossing_yellow", "diabolic_ultimatum_red", "deathly_duet_red", "deathly_duet_yellow", "deathly_duet_blue", "aether_slash_red", "aether_slash_yellow",
    "aether_slash_blue", "runic_reaping_red", "runic_reaping_yellow", "runic_reaping_blue", "gorgons_gaze_yellow", "manifestation_of_miragai_blue", "shifting_winds_of_the_mystic_beast_blue", "cosmic_awakening_blue", "unravel_aggression_blue", "dense_blue_mist_blue", "orihon_of_mystic_tenets_blue",
    "redwood_hammer", "bracken_rap_red", "bracken_rap_yellow", "strong_wood_red", "strong_wood_yellow", "seeds_of_strength_yellow", "seeds_of_strength_blue", "log_fall_red", "log_fall_yellow", "staff_of_verdant_shoots", "gauntlets_of_the_boreal_domain" => true,
    default => false
  };
}

function IsIyslander($character)
{
  switch ($character) {
    case 'iyslander':
    case 'iyslander_stormbind':
    case 'iyslander':
      return true;
    default:
      return false;
  }
}

function WardAmount($cardID, $player, $index = -1)
{
  global $mainPlayer;
  $auras = &GetAuras($player);
  switch ($cardID) {
    case "tome_of_aeo_blue":
    case "wave_of_reality":
    case "celestial_kimono":
    case "blessing_of_spirits_blue":
    case "blessing_of_spirits_yellow":
    case "blessing_of_spirits_red":
    case "tranquil_passing_blue":
    case "tranquil_passing_yellow":
    case "tranquil_passing_red":
    case "phantom_tidemaw_blue":
    case "uphold_tradition":
    case "truths_retold":
    case "waning_vengeance_blue":
    case "waxing_specter_blue":
    case "solitary_companion_blue":
    case "vengeful_apparition_blue":
    case "vengeful_apparition_yellow":
    case "vengeful_apparition_red":
    case "spectral_shield":
    case "fluttersteps":
      return 1;
    case "diadem_of_dreamstate":
    case "waning_vengeance_yellow":
    case "waxing_specter_yellow":
    case "restless_coalescence_yellow":
    case "essence_of_ancestry_mind_blue":
    case "essence_of_ancestry_soul_yellow":
    case "essence_of_ancestry_body_red":
    case "haunting_specter_blue":
    case "sigil_of_solitude_blue":
    case "single_minded_determination_blue":
    case "single_minded_determination_yellow":
    case "single_minded_determination_red":
    case "solitary_companion_yellow":
    case "sigil_of_protection_blue":
      return 2;
    case "waning_vengeance_red":
    case "waxing_specter_red":
    case "haunting_specter_yellow":
    case "sigil_of_solitude_yellow":
    case "solitary_companion_red":
    case "sigil_of_protection_yellow":
      return 3;
    case "suraya_archangel_of_knowledge":
    case "bellona_archangel_of_war":
    case "victoria_archangel_of_triumph":
    case "metis_archangel_of_tenacity":
    case "avalon_archangel_of_rebirth":
    case "sekem_archangel_of_ravages":
    case "aegis_archangel_of_protection":
    case "themis_archangel_of_judgment":
    case "suraya_archangel_of_erudition":
    case "heirloom_of_rabbit_hide":
    case "haunting_specter_red":
    case "sigil_of_solitude_red":
    case "sigil_of_protection_red":
      return 4;
    case "empyrean_rapture":
      if (SearchCurrentTurnEffects("empyrean_rapture-1", $player)) return 1;
      else return 0;
    case "meridian_pathway":
      return SearchCurrentTurnEffects("MERIDIANWARD", $player) ? 3 : 0;
    case "manifestation_of_miragai_blue":
      return isset($auras[$index + 3]) ? $auras[$index + 3] : 0;
    case "three_visits_red":
      return SearchPitchForColor($player, 3) * 3;
    case "haze_shelter_red":
      if (SearchPitchForColor($player, 3) > 0) return 4;
      else return 1;
    case "haze_shelter_yellow":
      if (SearchPitchForColor($player, 3) > 0) return 3;
      else return 1;
    case "haze_shelter_blue":
      if (SearchPitchForColor($player, 3) > 0) return 2;
      else return 1;
    case "10000_year_reunion_red":
      return 10;
    case "rage_specter_blue":
      return $player == $mainPlayer ? 6 : 1;
    default:
      return 0;
  }
}

function HasWard($cardID, $player)
{
  switch ($cardID) {
    case "spectral_shield":
    case "sand_cover_red":
    case "sand_cover_yellow":
    case "sand_cover_blue":
    case "sigil_of_protection_red":
    case "sigil_of_protection_yellow":
    case "sigil_of_protection_blue":
    case "celestial_kimono":
    case "wave_of_reality":
    case "tome_of_aeo_blue":
    case "blessing_of_spirits_red":
    case "blessing_of_spirits_yellow":
    case "blessing_of_spirits_blue":
    case "tranquil_passing_red":
    case "tranquil_passing_yellow":
    case "tranquil_passing_blue":
    case "suraya_archangel_of_knowledge":
      return true;
    case "empyrean_rapture":
      return SearchCurrentTurnEffects("empyrean_rapture-1", $player);
    case "diadem_of_dreamstate":
    case "suraya_archangel_of_erudition":
    case "themis_archangel_of_judgment":
    case "aegis_archangel_of_protection":
    case "sekem_archangel_of_ravages"://Angels
    case "avalon_archangel_of_rebirth":
    case "metis_archangel_of_tenacity":
    case "victoria_archangel_of_triumph":
    case "bellona_archangel_of_war":
    case "fluttersteps":
      return true;
    case "mini_forcefield_red":
    case "mini_forcefield_yellow":
    case "mini_forcefield_blue":
    case "phantom_tidemaw_blue":
      return true;
    case "meridian_pathway":
      return SearchCurrentTurnEffects("MERIDIANWARD", $player);
    case "heirloom_of_rabbit_hide":
    case "truths_retold":
    case "uphold_tradition":
      $char = &GetPlayerCharacter($player);
      $index = FindCharacterIndex($player, $cardID);
      return $char[$index + 12] != "DOWN";
    case "manifestation_of_miragai_blue":
    case "three_visits_red":
      return true;
    case "haze_shelter_red":
    case "haze_shelter_yellow":
    case "haze_shelter_blue":
    case "waning_vengeance_red":
    case "waning_vengeance_yellow":
    case "waning_vengeance_blue":
    case "waxing_specter_red":
    case "waxing_specter_yellow":
    case "waxing_specter_blue":
      return true;
    case "10000_year_reunion_red":
    case "rage_specter_blue":
    case "restless_coalescence_yellow":
      return true;
    case "essence_of_ancestry_body_red":
    case "essence_of_ancestry_soul_yellow":
    case "essence_of_ancestry_mind_blue":
      return true;
    case "haunting_specter_red":
    case "haunting_specter_yellow":
    case "haunting_specter_blue":
    case "sigil_of_solitude_red":
    case "sigil_of_solitude_yellow":
    case "sigil_of_solitude_blue":
    case "single_minded_determination_red":
    case "single_minded_determination_yellow":
    case "single_minded_determination_blue":
    case "solitary_companion_red":
    case "solitary_companion_yellow":
    case "solitary_companion_blue":
    case "vengeful_apparition_red":
    case "vengeful_apparition_yellow":
    case "vengeful_apparition_blue":
      return true;
    default:
      return false;
  }
}

function ArcaneShelterAmount($cardID)
{
  return match ($cardID) {
    "sigil_of_sanctuary_blue", "sigil_of_conductivity_blue" => 1,
    default => 0
  };
}

function HasArcaneShelter($cardID): bool
{
  return match ($cardID) {
    "sigil_of_sanctuary_blue", "sigil_of_conductivity_blue" => true,
    default => false
  };
}

function HasDominate($cardID)
{
  global $mainPlayer, $combatChainState;
  global $CS_NumAuras, $CCS_NumBoosted;
  switch ($cardID) {
    case "open_the_center_red":
    case "open_the_center_yellow":
    case "open_the_center_blue":
      return (ComboActive() ? true : false);
    case "demolition_crew_red":
    case "demolition_crew_yellow":
    case "demolition_crew_blue":
      return true;
    case "arknight_ascendancy_red":
      return true;
    case "herald_of_erudition_yellow":
      return true;
    case "herald_of_tenacity_red":
    case "herald_of_tenacity_yellow":
    case "herald_of_tenacity_blue":
      return true;
    case "nourishing_emptiness_red":
      return SearchDiscard($mainPlayer, "AA") == "";
    case "overload_red":
    case "overload_yellow":
    case "overload_blue":
      return true;
    case "thump_red":
    case "thump_yellow":
    case "thump_blue":
      return HasIncreasedAttack();
    case "macho_grande_red":
    case "macho_grande_yellow":
    case "macho_grande_blue":
      return true;
    case "break_tide_yellow":
      return (ComboActive() ? true : false);
    case "payload_red":
    case "payload_yellow":
    case "payload_blue":
      return $combatChainState[$CCS_NumBoosted] > 0;
    case "drowning_dire_red":
    case "drowning_dire_yellow":
    case "drowning_dire_blue":
      return GetClassState($mainPlayer, $CS_NumAuras) > 0;
    case "isolate_red":
    case "isolate_yellow":
    case "isolate_blue":
      return true;
    default:
      break;
  }
  return false;
}

function HasAmbush($cardID)
{
  switch ($cardID) {
    case "tiger_eye_reflex_yellow":
    case "tiger_eye_reflex_blue":
      return true;
    default:
      return false;
  }
}

function HasScrap($cardID)
{
  switch ($cardID) {
    case "scrap_trader_red":
    case "hydraulic_press_red":
    case "hydraulic_press_yellow":
    case "hydraulic_press_blue":
    case "scrap_hopper_red":
    case "scrap_hopper_yellow":
    case "scrap_hopper_blue":
    case "junkyard_dogg_red":
    case "junkyard_dogg_yellow":
    case "junkyard_dogg_blue":
    case "scrap_compactor_red":
    case "scrap_compactor_yellow":
    case "scrap_compactor_blue":
    case "scrap_harvester_red":
    case "scrap_harvester_yellow":
    case "scrap_harvester_blue":
    case "scrap_prospector_red":
    case "scrap_prospector_yellow":
    case "scrap_prospector_blue":
      return true;
    default:
      return false;
  }
}

function HasGalvanize($cardID)
{
  switch ($cardID) {
    case "adaptive_plating":
    case "ratchet_up_red":
    case "ratchet_up_yellow":
    case "ratchet_up_blue":
    case "soup_up_red":
    case "soup_up_yellow":
    case "soup_up_blue":
    case "torque_tuned_red":
    case "torque_tuned_yellow":
    case "torque_tuned_blue":
    case "cognition_field_red":
    case "cognition_field_yellow":
    case "cognition_field_blue":
    case "infuse_alloy_red":
    case "infuse_alloy_yellow":
    case "infuse_alloy_blue":
    case "infuse_titanium_red":
    case "infuse_titanium_yellow":
    case "infuse_titanium_blue":
    case "steel_street_hoons_blue":
      return true;
    default:
      return false;
  }
}

function PowerCantBeModified($cardID)
{
  switch ($cardID) {
    case "numbskull_red":
      return true;
    default:
      return false;
  }
}

function CostCantBeModified($cardID)
{
  switch ($cardID) {
    case "numbskull_red":
      return true;
    default:
      return false;
  }
}

function BlockCantBeModified($cardID)
{
  switch ($cardID) {
    case "numbskull_red":
      return true;
    default:
      return false;
  }
}

function Rarity($cardID)
{
  $set = CardSet($cardID);
  switch ($cardID) {
    case "qi_unleashed_red":
    case "qi_unleashed_yellow":
    case "qi_unleashed_blue": // Commoner workaround. Can be deleted later when the database is updated.
      return "C";
  }
  if ($set != "ROG" && $set != "DUM") {
    return GeneratedRarity($cardID);
  }
  if ($set == "ROG") {
    return ROGUERarity($cardID);
  }
}

function IsEquipment($cardID, $player = "")
{
  return TypeContains($cardID, "E", $player) || SubtypeContains($cardID, "Evo", $player);
}

function CardCareAboutChiPitch($cardID)
{
  $cardID = ShiyanaCharacter($cardID);
  switch ($cardID) {
    case "nuu_alluring_desire":
    case "nuu":
    case "mask_of_recurring_nightmares":
    case "enigma_ledger_of_ancestry":
    case "enigma":
    case "meridian_pathway":
    case "zen_tamer_of_purpose":
    case "zen":
    case "twelve_petal_kasaya":
    case "enigma_new_moon":
      return true;
    default:
      return false;
  }
}

function IsModular($cardID)
{
  switch ($cardID) {
    case "adaptive_plating":
    case "adaptive_dissolver":
      return true;
    default:
      return false;
  }
}

function HasCloaked($cardID, $player = "", $hero = "")
{
  $char = GetPlayerCharacter($player);
  if (TypeContains($cardID, "E", $player) && $hero == "enigma_new_moon") return "DOWN";
  switch ($cardID) {
    case "heirloom_of_snake_hide":
    case "heirloom_of_rabbit_hide":
    case "truths_retold":
    case "uphold_tradition":
    case "heirloom_of_tiger_hide":
    case "aqua_seeing_shell":
    case "koi_blessed_kimono":
    case "waves_of_aqua_marine":
    case "aqua_laps":
    case "skycrest_keikoi":
    case "skybody_keikoi":
    case "skyhold_keikoi":
    case "skywalker_keikoi":
      return "DOWN";
    default:
      return "UP";
  }
}

function HasEphemeral($cardID)
{
  switch ($cardID) {
    case "crouching_tiger":
    case "fang_strike":
    case "slither":
      return true;
    default:
      return false;
  }
}

function HasAttackLayer()
{
  global $layers;
  if (count($layers) == 0) return false;//If there's no attack, and no layers, nothing to do
  $layerIndex = count($layers) - LayerPieces();//Only the earliest layer can be an attack
  $layerID = $layers[$layerIndex];
  $parameters = explode("|", $layers[$layerIndex+2]);
  if (strlen($layerID) != 6) return false;//Game phase, not a card - sorta hacky
  if (GetResolvedAbilityType($layerID, $parameters[0]) == "AA") return true;
  $layerType = CardType($layerID);
  if ($layerType == "AA") return true; //It's an attack
  if ($parameters[0] == "PLAY" && DelimStringContains(CardSubType($layerID), "Aura")) return true;
  return false;
}

function HasMeld($cardID){
  switch ($cardID) {
    case "thistle_bloom__life_yellow":
    case "arcane_seeds__life_red":
    case "vaporize__shock_yellow":
    case "burn_up__shock_red":
    case "rampant_growth__life_yellow":
    case "pulsing_aether__life_red":
    case "null__shock_yellow":
    case "comet_storm__shock_red":
    case "regrowth__shock_blue":
      return true;
    default:
      return false;
  }  
}

Function IsMeldInstantName($term){
  switch ($term) {
      case "Shock":
      case "Life":
      case "Rampant_Growth":
      case "Null":
      case "Vaporize":
      return true;
    default:
      return false;
  }  
}

Function IsMeldActionName($term){
  switch ($term) {
      case "Pulsing_Aether":
      case "Arcane_Seeds":
      case "Comet_Storm":
      case "Thistle_Bloom":
      case "Burn_Up":
      case "Regrowth":
      return true;
    default:
      return false;
  }  
}


Function IsMeldRightSideName($term){
  switch ($term) {
      case "Shock":
      case "Life":
      return true;
    default:
      return false;
  }  
}

Function IsMeldLeftSideName($term){
  switch ($term) {
      case "Pulsing_Aether":
      case "Arcane_Seeds":
      case "Comet_Storm":
      case "Thistle_Bloom":
      case "Burn_Up":
      case "Regrowth":
      case "Null":
      case "Rampant_Growth":
      case "Vaporize":
        return true;
      default:
        return false;
  }  
}

function HasEssenceOfEarth($cardID){
  switch ($cardID) {
    case "oldhim_grandfather_of_eternity":
    case "oldhim":
    case "briar_warden_of_thorns":
    case "briar":
    case "bravo_star_of_the_show":
    case "florian_rotwood_harbinger":
    case "florian":
    case "verdance_thorn_of_the_rose":
    case "verdance":
    case "terra":
    case "jarl_vetreidi": 
      return true;
    default:
      return false;
  }
}

function canBeAddedToChainDuringDR($cardID){
  switch ($cardID) {
    case "leap_frog_vocal_sac":
    case "leap_frog_slime_skin":
    case "leap_frog_gloves":
    case "leap_frog_leggings":
      return true;
    default:
      return false;
  }
}

//utility function for stripping "," and "-" from a card id
//replaces substr($cardID, 0, 6)
function ExtractCardID($cardID) {
  $cardID = explode(",", $cardID)[0];
  $cardID = explode("-", $cardID)[0];
  return $cardID;
}