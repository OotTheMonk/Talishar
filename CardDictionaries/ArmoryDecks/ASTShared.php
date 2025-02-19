<?php

function ASTAbilityType($cardID, $index = -1, $from = "-"): string
{
  return match ($cardID) {
    default => ""
  };
}

function ASTAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    default => false
  };
}

function ASTEffectAttackModifier($cardID): int
{
  global $currentPlayer, $defPlayer;
  return match (CardIdentifier($cardID)) {
    "skyward-serenade-2" => 1,
    default => 0
  };
}

function ASTCombatEffectActive($cardID, $attackID): bool
{
  return match(CardIdentifier($cardID)) {
    "skyward-serenade-2" => true,
    default => false
  };
}

function ASTAbilityCost($cardID): int
{
  return match($cardID) {
    default => 0
  };
}

function ASTPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = ""): string
{
  global $currentPlayer, $CS_PlayIndex;
  $ID = CardIdentifier($cardID);
  switch ($ID) {
    case "skyward-serenade-2":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "SKYWARDSERENADE", 1);
      return "";
    default:
      return "";
  }
}
