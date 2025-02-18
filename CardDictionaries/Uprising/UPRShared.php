<?php

  include 'UPRIllusionist.php';
  include 'UPRNinja.php';
  include 'UPRWizard.php';
  include 'UPRTalent.php';

  function UPRAbilityCost($cardID)
  {
    global $mainPlayer, $currentPlayer;
    switch($cardID)
    {
      case "fai_rising_rebellion": case "fai": return 3 - ($mainPlayer == $currentPlayer ? NumDraconicChainLinks() : 0);
      case "searing_emberblade": return 2;
      case "flamescale_furnace": return 1;
      case "coronet_peak": return 3;
      case "waning_moon": return 2;
      case "helios_mitre": return 2;
      case "UPR551": return 3;
      default: return 0;
    }
  }

  function UPRAbilityType($cardID, $index=-1)
  {
    switch($cardID)
    {
      case "silken_form": return "I";
      case "fai_rising_rebellion": case "fai": return "I";
      case "searing_emberblade": return "AA";
      case "heat_wave": return "I";
      case "flamescale_furnace": return "I";
      case "sash_of_sandikai": return "I";
      case "conduit_of_frostburn": return "I";
      case "coronet_peak": return "A";
      case "glacial_horns": return "A";
      case "ghostly_touch": return "A";
      case "tide_flippers": return "AR";
      case "waning_moon": return "I";
      case "alluvion_constellas": return "I";
      case "spellfire_cloak": return "I";
      case "helios_mitre": return "I";
      case "UPR551": return "AA";
      default: return "";
    }
  }

  function UPRAbilityHasGoAgain($cardID)
  {
    switch($cardID)
    {
      case "glacial_horns": return true;
      case "ghostly_touch": return true;
      default: return false;
    }
  }

  function UPREffectAttackModifier($cardID)
  {
    $params = explode("-", $cardID);
    $cardID = $params[0];
    if(count($params) > 1) $subparam = $params[1];
    switch($cardID)
    {
      case "skittering_sands_red": return 3;
      case "skittering_sands_yellow": return 2;
      case "skittering_sands_blue": return 1;
      case "heat_wave": return 1;
      case "spreading_flames_red": return 1;
      case "mounting_anger_red": case "mounting_anger_yellow": case "mounting_anger_blue": return 1;
      case "rise_from_the_ashes_red": return 3;
      case "rise_from_the_ashes_yellow": return 2;
      case "rise_from_the_ashes_blue": return 1;
      case "uprising_red": return 1;
      case "rise_up_red": return NumChainLinksWithName("Phoenix Flame")*2;
      case "burn_away_red": return 2;
      case "tiger_stripe_shuko": return 1;
      case "flex_red": case "flex_yellow": case "flex_blue": return 2;
      default: return 0;
    }
  }

  function UPRCombatEffectActive($cardID, $attackID)
  {
    global $mainPlayer;
    $params = explode("-", $cardID);
    $cardID = $params[0];
    switch($cardID)
    {
      case "skittering_sands_red": case "skittering_sands_yellow": case "skittering_sands_blue": return true;
      case "heat_wave": return IsCardNamed($mainPlayer, $attackID, "Phoenix Flame");
      case "spreading_flames_red": return TalentContains($attackID, "DRACONIC", $mainPlayer) && AttackValue($attackID) < NumDraconicChainLinks();
      case "mounting_anger_red": case "mounting_anger_yellow": case "mounting_anger_blue": return true;
      case "rise_from_the_ashes_red": case "rise_from_the_ashes_yellow": case "rise_from_the_ashes_blue": return CardType($attackID) == "AA" && (TalentContains($attackID, "DRACONIC", $mainPlayer) || ClassContains($attackID, "NINJA", $mainPlayer));
      case "brand_with_cinderclaw_red": case "brand_with_cinderclaw_yellow": case "brand_with_cinderclaw_blue": return true;
      case "soaring_strike_red": case "soaring_strike_yellow": case "soaring_strike_blue": return true;
      case "uprising_red": return TalentContains($attackID, "DRACONIC", $mainPlayer);
      case "rise_up_red": return true;
      case "burn_away_red": return true;
      case "ghostly_touch": return $attackID == "UPR551";
      case "semblance_blue": return ClassContains($attackID, "ILLUSIONIST", $mainPlayer);
      case "transmogrify_red": case "transmogrify_yellow": case "transmogrify_blue": return CardType($attackID) == "AA";
      case "tiger_stripe_shuko": return true;
      case "flex_red": case "flex_yellow": case "flex_blue": return true;
      case "miragai": return true;
      default: return false;
    }
  }

  function UPRPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    $class = CardClass($cardID);
    switch($class)
    {
      case "ILLUSIONIST": return UPRIllusionistPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "NINJA": return UPRNinjaPlayAbility($cardID);
      case "WIZARD": return UPRWizardPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      default: return UPRTalentPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
    }
  }

  function UPRHitEffect($cardID)
  {
    $class = CardClass($cardID);
    switch($class)
    {
      case "ILLUSIONIST": return UPRIllusionistHitEffect($cardID);
      case "NINJA": return UPRNinjaHitEffect($cardID);
      case "WIZARD": return UPRWizardHitEffect($cardID);
      default: return UPRTalentHitEffect($cardID);
    }
  }

function UPRDealDamageEffect($cardID)
{
  $class = CardClass($cardID);
  switch ($class) {
    case "ILLUSIONIST":
      return UPRIllusionistDealDamageEffect($cardID);
    default:
      return UPRTalentHitEffect($cardID);
  }
}

  function QuellAmount($cardID)
  {
    switch($cardID)
    {
      case "silken_form": return 1;
      case "heat_wave": return 1;
      case "conduit_of_frostburn": return 1;
      case "quelling_robe": return 1;
      case "quelling_sleeves": return 1;
      case "quelling_slippers": return 1;
      default: return 0;
    }
  }

  function QuellChoices($player, $damage)
  {
    $character = &GetPlayerCharacter($player);
    $quellAmount = 0;
    for($i=0; $i<count($character); $i+=CharacterPieces()) {
      if($character[$i+1] == "0" || $character[$i+9] == "0") continue;
      $quellAmount += QuellAmount($character[$i]);
    }
    if($quellAmount > $damage) $quellAmount = $damage;
    $rv = "0";
    for($i=1; $i<=$quellAmount; ++$i) $rv .= "," . $i;
    return $rv;
  }

  function QuellEndPhase($player)
  {
    global $CS_MaxQuellUsed;
    $maxUsed = GetClassState($player, $CS_MaxQuellUsed);
    for($i=0; $i<$maxUsed; ++$i)
    {
      AddDecisionQueue("FINDINDICES", $player, "QUELL");
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose " . ($maxUsed - $i) . " quell cards to destroy");
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZDESTROY", $player, "-", 1);
    }
  }

  function QuellIndices($player)
  {
    $character = &GetPlayerCharacter($player);
    $indices = "";
    for($i=0; $i<count($character); $i+=CharacterPieces())
    {
      if($character[$i+1] == "0") continue;
      if(QuellAmount($character[$i]) > 0)
      {
        if($indices != "") $indices .= ",";
        $indices .= SearchMultizoneFormat($i, "MYCHAR");
      }
    }
    return $indices;
  }

?>
