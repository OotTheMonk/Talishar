<?php
function TERAbilityType($cardID): string
{
  return match ($cardID) {
    "redwood_hammer" => "AA",
    "blossom_of_spring" => "A",
    default => ""
  };
}

function TERAbilityCost($cardID): int
{
  return match ($cardID) {
    "redwood_hammer" => 3,
    "blossom_of_spring" => 0,
    default => 0
  };
}

function TERCombatEffectActive($cardID): bool
{
  return match ($cardID) {
    "redwood_hammer", "log_fall_red", "log_fall_yellow", "strong_wood_red", "strong_wood_yellow", "thrive_yellow", "hard_knuckle", "flourish_yellow", "flourish_blue" => true,
    default => false
  };
}

function TEREffectAttackModifier($cardID): int
{
  global $currentPlayer;

  return match ($cardID) {
    "flourish_yellow" => SearchCurrentTurnEffects("thrive_yellow", $currentPlayer) ? 2 : 3,
    "flourish_blue" => SearchCurrentTurnEffects("thrive_yellow", $currentPlayer) ? 1 : 2,
    "redwood_hammer", "strong_wood_red", "strong_wood_yellow", "hard_knuckle" => 1,
    default => 0
  };
}

function TERPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = ""): string
{
  global $currentPlayer;
  switch ($cardID) {
    case "redwood_hammer":
      if (SearchCardList($additionalCosts, $currentPlayer, talent: "EARTH") != "") AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "blossom_of_spring":
      GainResources($currentPlayer, 1);
      return "";
    case "bracken_rap_red": case "bracken_rap_yellow":
      if (SearchCardList($additionalCosts, $currentPlayer, talent: "EARTH") != "") PlayAura("might", $currentPlayer); //Might
      return "";
    case "log_fall_red":
    case "log_fall_yellow":
    case "strong_wood_red":
    case "strong_wood_yellow":
      if (SearchCardList($additionalCosts, $currentPlayer, talent: "EARTH") != "") AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "sigil_of_shelter_yellow":
    case "sigil_of_shelter_blue":
    case "thrive_yellow":
      AddCurrentTurnEffect($cardID, 1);
      AddCurrentTurnEffect($cardID, 2); // I think because of the way this effect is evaluated, both players need to "know" about it in order for it to work properly. See rain_razors_yellow.
      return "";
    case "flourish_yellow":
      AddCurrentTurnEffect("flourish_yellow-INACTIVE", $currentPlayer);
      return "";
    case "flourish_blue":
      AddCurrentTurnEffect("flourish_blue-INACTIVE", $currentPlayer);
      return "";
    case "seeds_of_strength_yellow":
      PlayAura("might", $currentPlayer);
      PlayAura("might", $currentPlayer);
      if (SearchCardList($additionalCosts, $currentPlayer, talent: "EARTH") != "") {
        PlayAura("might", $currentPlayer);
      }
      return "";
    case "seeds_of_strength_blue":
      PlayAura("might", $currentPlayer);
      if (SearchCardList($additionalCosts, $currentPlayer, talent: "EARTH") != "") {
        PlayAura("might", $currentPlayer);
      }
      return "";
    default:
      return "";
  }
}

function TerraEndPhaseAbility($characterID, $player): void
{
  $resources = &GetResources($player);
  $hand = &GetHand($player);
  $earthTalent = SearchCount(SearchPitch($player, talent: "EARTH"));
  if (($earthTalent >= 1) && (Count($hand) > 0 || $resources[0] > 0)) {
    AddDecisionQueue("YESNO", $player, "if you want to pay 1 to create a " . CardLink("might", "might"), 0, 1);
    AddDecisionQueue("NOPASS", $player, "-", 1);
    AddDecisionQueue("PASSPARAMETER", $player, "1", 1);
    AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
    AddDecisionQueue("WRITELOG", $player, CardLink($characterID, $characterID) . " created a " . CardLink("might", "might") . " token ", 1);
    AddDecisionQueue("PASSPARAMETER", $player, "might", 1);
    AddDecisionQueue("PUTPLAY", $player, "-", 1);
  }
}