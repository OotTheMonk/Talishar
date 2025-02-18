<?php
function AAZAbilityType($cardID, $index = -1, $from = "-"): string
{
  return match ($cardID) {
    "target_totalizer", "sharp_shooters" => "A",
    "hidden_agenda" => "I",
    "flight_path" => "AR",
    default => ""
  };
}

function AAZAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    "target_totalizer", "sharp_shooters" => true,
    default => false
  };
}

function AAZCombatEffectActive($cardID, $attackID): bool
{
  return match ($cardID) {
    "line_it_up_yellow", "flight_path" => CardSubType($attackID) == "Arrow",
    "target_totalizer" => CardSubType($attackID) == "Arrow" && HasAimCounter(),
    "stone_rain_red" => true,
    default => false
  };
}

function AAZEffectAttackModifier($cardID): int
{
  return match ($cardID) {
    "line_it_up_yellow" => 3,
    default => 0
  };
}

function AAZPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = ""): string
{
  global $currentPlayer;
  switch ($cardID) {
    case "target_totalizer":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "sharp_shooters":
      LoadArrow($currentPlayer, "UP", 1);
      return "";
    case "flight_path":
      GiveAttackGoAgain();
      return "";
    case "hidden_agenda":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      GainResources($currentPlayer, 1);
      return "";
    case "stone_rain_red":
      if (HasAimCounter()) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "line_it_up_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      $arsenal = &GetArsenal($currentPlayer);
      for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
        if (CardSubType($arsenal[$i]) == "Arrow" && $arsenal[$i + 1] == "DOWN"){
          AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_turn_your_arsenal_face_up");
          AddDecisionQueue("NOPASS", $currentPlayer, "-");
          AddDecisionQueue("TURNARSENALFACEUP", $currentPlayer, $i, 1);
          AddDecisionQueue("ADDAIMCOUNTER", $currentPlayer, $i, 1);
        }
      }
      return "";
    default:
      return "";
  }
}

function AAZHitEffect($cardID): void
{
  global $defPlayer;
  switch ($cardID) {
    case "stone_rain_red":
      AddDecisionQueue("FINDINDICES", $defPlayer, "HAND");
      AddDecisionQueue("SETDQCONTEXT", $defPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("CHOOSEHAND", $defPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $defPlayer, "-", 1);
      AddDecisionQueue("BANISHCARD", $defPlayer, "HAND,NTSTONERAIN," . $cardID, 1);
      break;
  }
}