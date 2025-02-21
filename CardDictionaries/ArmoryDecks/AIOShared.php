<?php

function AIOAbilityType($cardID, $index = -1, $from = "-"): string
{
  return match ($cardID) {
    "heavy_industry_gear_shift" => "A",
    "heavy_industry_power_plant" => "A",
    "cerebellum_processor_blue" => "A",
    default => ""
  };
}

function AIOAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    "heavy_industry_power_plant"  => true,
    default => false
  };
}

function AIOCombatEffectActive($cardID, $attackID): bool
{
  return match ($cardID) {
    default => false
  };
}

function AIOAbilityCost($cardID): int
{
  return match ($cardID) {
    "heavy_industry_power_plant"  => 1,
    default => 0
  };
}

function AIOPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = ""): string
{
  global $currentPlayer, $CS_PlayIndex;
  switch ($cardID) {
    case "heavy_industry_gear_shift":
      $deck = new Deck($currentPlayer);
      for($i = 0; $i < 2 && !$deck->Empty(); ++$i) {
        $banished = $deck->BanishTop();
        if(ClassContains($banished, "MECHANOLOGIST", $currentPlayer)) GainActionPoints(1, $currentPlayer);
      }
      return "";
    case "heavy_industry_power_plant":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "cerebellum_processor_blue":
      if ($from == "PLAY") {
        Draw($currentPlayer);
      }
      return "";
    default:
      return "";
  }
}