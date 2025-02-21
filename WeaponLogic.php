<?php

function IsWeapon($cardID, $from)
{
  global $currentPlayer, $mainPlayer;
  if (DelimStringContains(CardSubType($cardID), "Aura") && ClassContains($cardID, "ILLUSIONIST", $mainPlayer) && $from == "PLAY" && (
      SearchCharacterForCard($mainPlayer, "luminaris") || SearchCharacterForCard($mainPlayer, "iris_of_reality") || SearchCharacterForCard($mainPlayer, "reality_refractor") || SearchCharacterForCard($mainPlayer, "cosmo_scroll_of_ancestral_tapestry"))) 
    {
    return true;
  }
  return TypeContains($cardID, "W", $currentPlayer);
}

function IsWeaponAttack()
{
  global $combatChain, $mainPlayer;
  if (count($combatChain) == 0) return false;
  if (TypeContains($combatChain[0], "W", $mainPlayer) || DelimStringContains(CardSubType($combatChain[0]), "Aura")) return true;
  return false;
}

function WeaponWithNonAttack($cardID,  $from) 
{
  if (!IsWeapon($cardID, $from)) return false;
  if (GetAbilityTypes($cardID) != "") return true;
  if (GetAbilityType($cardID) != "AA" && GetAbilityType($cardID) != "") return true;
  return false;
}

function WeaponIndices($chooser, $player, $subtype = "")
{
  global $mainPlayer;
  $whoPrefix = ($player == $chooser ? "MY" : "THEIR");
  $character = GetPlayerCharacter($player);
  $weapons = "";
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] != 0 && CardType($character[$i]) == "W" && ($subtype == "" || CardSubType($character[$i]) == $subtype)) {
      if ($weapons != "") $weapons .= ",";
      $weapons .= $whoPrefix . "CHAR-" . $i;
    }
  }
  $auraWeapons = (SearchCharacterForCard($player, "luminaris") || SearchCharacterForCard($player, "iris_of_reality") || SearchCharacterForCard($player, "reality_refractor") || SearchCharacterForCard($player, "cosmo_scroll_of_ancestral_tapestry")) && ($player == $mainPlayer);
  if ($auraWeapons) {
    $auras = GetAuras($player);
    for ($i = 0; $i < count($auras); $i += AuraPieces()) {
      if (SearchCharacterForCard($player, "cosmo_scroll_of_ancestral_tapestry")) {
        if (HasWard($auras[$i], $player)) {
          if ($weapons != "") $weapons .= ",";
          $weapons .= $whoPrefix . "AURAS-" . $i;
        }
      } else if (ClassContains($auras[$i], "ILLUSIONIST", $player)) {
        if ($weapons != "") $weapons .= ",";
        $weapons .= $whoPrefix . "AURAS-" . $i;
      }
    }
  }
  return $weapons;
}

function ApplyEffectToEachWeapon($effectID)
{
  global $currentPlayer;
  $character = &GetPlayerCharacter($currentPlayer);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if (CardType($character[$i]) == "W") AddCharacterEffect($currentPlayer, $i, $effectID);
  }
}
