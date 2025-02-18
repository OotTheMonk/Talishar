<?php
function AUREffectAttackModifier($cardID): int
{
  return match ($cardID) {
    "sizzle_red" => 3,
    "sizzle_yellow" => 2,
    "spark_spray_yellow", "spark_spray_blue" => 1,
    default => 0,
  };
}

function AURCombatEffectActive($cardID, $attackID): bool|string
{
  global $mainPlayer;
  return match ($cardID) {
    "sizzle_red", "sizzle_yellow" => TalentContainsAny($attackID, "LIGHTNING,ELEMENTAL", $mainPlayer),
    "spark_spray_yellow", "spark_spray_blue" => true,
    default => "",
  };
}

function AURPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = ""): string
{
  global $currentPlayer, $mainPlayer, $CS_NumLightningPlayed;
  switch ($cardID) {
    case "harness_lightning_red":
      if (GetClassState($mainPlayer, $CS_NumLightningPlayed) > 0) {
        DealArcane(3, 0, "PLAYCARD", $cardID);
      }
      return "";
    case "harness_lightning_yellow":
      if (GetClassState($mainPlayer, $CS_NumLightningPlayed) > 0) {
        DealArcane(2, 0, "PLAYCARD", $cardID);
      }
      return "";
    case "sizzle_red":
    case "cloud_cover_yellow":
    case "sizzle_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    default:
      return "";
  }
}

function AURHitEffect($cardID): void
{
  global $mainPlayer, $CS_NumLightningPlayed;
  switch ($cardID) {
    case "static_shock_red": case "static_shock_yellow":
    if (GetClassState($mainPlayer, $CS_NumLightningPlayed) > 0) {
      DealArcane(1, 0, "PLAYCARD", $cardID, false, $mainPlayer);
    }
    break;
    default:
      break;
  }
}