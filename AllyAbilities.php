<?php

function PlayAlly($cardID, $player, $subCards = "-", $number = 1, $isToken = false, $firstTransform = true)
{
  global $EffectContext;
  $otherPlayer = ($player == 1 ? 2 : 1);
  if (TypeContains($cardID, "T", $player)) $isToken = true;
  $numMinusTokens = 0;
  $numMinusTokens = CountCurrentTurnEffects("ripple_away_blue", $player) + CountCurrentTurnEffects("ripple_away_blue", $otherPlayer);
  if ($numMinusTokens > 0 && $isToken && (TypeContains($EffectContext, "AA", $player) || TypeContains($EffectContext, "A", $player)) && $firstTransform) $number -= $numMinusTokens;
  $allies = &GetAllies($player);
  for ($i = 0; $i < $number; ++$i) {
    array_push($allies, $cardID);
    array_push($allies, 2);
    array_push($allies, AllyHealth($cardID));
    array_push($allies, 0); //Frozen
    array_push($allies, $subCards); //Subcards
    array_push($allies, GetUniqueId($cardID, $player)); //Unique ID
    array_push($allies, AllyEnduranceCounters($cardID)); //Endurance Counters
    array_push($allies, 0); //Life Counters
    array_push($allies, 1); //Ability/effect uses
    array_push($allies, 0); //Attack Counters
    array_push($allies, 0); //Damage dealt to the opponent
    if ($cardID == "ouvia") {
      WriteLog(CardLink($cardID, $cardID) . " lets you transform up to 1 ash into an Ashwing.");
      Transform($player, "Ash", "aether_ashwing", true);
    }
  }
  return count($allies) - AllyPieces();
}

function DestroyAlly($player, $index, $skipDestroy = false, $fromCombat = false, $uniqueID = "")
{
  $allies = &GetAllies($player);
  if (!$skipDestroy) AllyDestroyedAbility($player, $index);
  if (IsSpecificAllyAttacking($player, $index) || (IsSpecificAllyAttackTarget($player, $index, $uniqueID) && !$fromCombat)) {
    CloseCombatChain();
  }
  $cardID = $allies[$index];
  AllyAddGraveyard($player, $cardID);
  AllyAddGraveyard($player, $allies[$index + 4]);
  for ($j = $index + AllyPieces() - 1; $j >= $index; --$j) unset($allies[$j]);
  $allies = array_values($allies);
  return $cardID;
}

function AllyAddGraveyard($player, $cardID)
{
  if (CardType($cardID) != "T") {
    if (SubtypeContains($cardID, "Ash", $player)) AddGraveyard($cardID, $player, "PLAY", $player);
    $setID = SetID($cardID);
    $set = substr($setID, 0, 3);
    $number = intval(substr($setID, 3, 3));
    $number -= 400;
    if ($number < 0) return;
    $id = $number;
    if ($number < 100) $id = "0" . $id;
    if ($number < 10) $id = "0" . $id;
    $id = $set . $id;
    if (!SubtypeContains($id, "Invocation", $player) && !SubtypeContains($id, "Figment", $player)) return;
    AddGraveyard($id, $player, "PLAY", $player);
  }
}

function AllyHealth($cardID)
{
  switch ($cardID) {
    case "blasmophet_the_soul_harvester":
      return 6;
    case "ursur_the_soul_reaper":
      return 6;
    case "dracona_optimai":
      return 6;
    case "tomeltai":
      return 5;
    case "dominia":
      return 4;
    case "azvolai":
      return 3;
    case "cromai":
      return 2;
    case "kyloria":
      return 2;
    case "miragai":
      return 4;
    case "nekria":
      return 7;
    case "ouvia":
      return 6;
    case "themai":
      return 4;
    case "vynserakai":
      return 1;
    case "yendurai":
      return 3;
    case "suraya_archangel_of_knowledge":
      return 4;
    case "nasreth_the_soul_harrower":
      return 6;
    case "suraya_archangel_of_erudition":
      return 4;
    case "themis_archangel_of_judgment":
      return 4;
    case "aegis_archangel_of_protection":
      return 4;
    case "sekem_archangel_of_ravages":
      return 4;
    case "avalon_archangel_of_rebirth":
      return 4;
    case "metis_archangel_of_tenacity":
      return 4;
    case "victoria_archangel_of_triumph":
      return 4;
    case "bellona_archangel_of_war":
      return 4;
    case "cintari_sellsword":
      return 2;
    default:
      return 1;
  }
}

function AllyDestroyedAbility($player, $index)
{
  global $mainPlayer;
  $allies = &GetAllies($player);
  $cardID = $allies[$index];
  switch ($cardID) {
    case "cromai":
      if ($player == $mainPlayer && $allies[$index + 8] > 0) {
        GainActionPoints(1, $player);
        --$allies[$index + 8];
      }
      break;
    case "UPR551":
      $charIndex = FindCharacterIndex($player, "ghostly_touch");
      if ($charIndex > -1) DestroyCharacter($player, $charIndex);
      break;
    default:
      break;
  }
  if (HasWard($cardID, $player)) WardPoppedAbility($player, $cardID);
}

function AllyStartTurnAbilities($player)
{
  $allies = &GetAllies($player);
  for ($i = 0; $i < count($allies); $i += AllyPieces()) {
    switch ($allies[$i]) {
      case "ouvia":
        WriteLog(CardLink($allies[$i], $allies[$i]) . " lets you transform up to 1 ash into an Ashwing.");
        Transform($player, "Ash", "aether_ashwing", true);
        break;
      default:
        break;
    }
  }
}

function AllyEnduranceCounters($cardID)
{
  switch ($cardID) {
    case "yendurai":
      return 1;
    default:
      return 0;
  }
}

function AllyDamagePrevention($player, $index, $damage, $type = "")
{
  $allies = &GetAllies($player);
  $cardID = $allies[$index];
  $preventedDamage = 0;
  $canBePrevented = CanDamageBePrevented($player, $damage, $type);
  switch ($cardID) {
    case "yendurai":
      if ($allies[$index + 6] > 0) {
        if ($damage > 0) --$allies[$index + 6];
        if ($canBePrevented) $preventedDamage += 3;
        if ($preventedDamage > 0 && SearchCurrentTurnEffects("vambrace_of_determination", $player) != "") {
          $preventedDamage -= 1;
          SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
        }
        $damage -= $preventedDamage;
        if ($damage < 0) $damage = 0;
      }
      return $damage;
    default:
      return $damage;
  }
}

//NOTE: This is for ally abilities that trigger when any ally attacks (for example miragai GRANTS an ability)
function AllyAttackAbilities($attackID)
{
  global $mainPlayer, $CS_NumDragonAttacks;
  $allies = &GetAllies($mainPlayer);
  for ($i = 0; $i < count($allies); $i += AllyPieces()) {
    switch ($allies[$i]) {
      case "miragai":
        if ($allies[$i + 8] > 0 && DelimStringContains(CardSubType($attackID), "Dragon") && GetClassState($mainPlayer, $CS_NumDragonAttacks) <= 1) {
          AddCurrentTurnEffect("miragai", $mainPlayer);
          --$allies[$i + 8];
        }
        break;
      default:
        break;
    }
  }
}

//NOTE: This is for the actual attack abilities that allies have
function SpecificAllyAttackAbilities($attackID)
{
  global $mainPlayer, $combatChainState, $CCS_WeaponIndex, $defPlayer;
  $allies = &GetAllies($mainPlayer);
  $i = $combatChainState[$CCS_WeaponIndex];
  switch ($allies[$i]) {
    case "dracona_optimai":
    case "tomeltai":
    case "dominia":
      if (IsHeroAttackTarget()) {
        AddLayer("TRIGGER", $mainPlayer, $allies[$i], "-", "-", $allies[$i + 5]);
      }
      return "";
    case "azvolai":
      AddLayer("TRIGGER", $mainPlayer, $allies[$i], "-", "-", $allies[$i + 5]);
      return "";
    case "cromai":
      if ($attackID == $allies[$i] && $allies[$i + 8] > 0) {
        GainActionPoints(1);
        --$allies[$i + 8];
        WriteLog("Gained 1 action point from " . CardLink($allies[$i], $allies[$i]));
      }
      break;
    case "suraya_archangel_of_erudition":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
      AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
      break;
    case "themis_archangel_of_judgment":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRBANISH&MYBANISH", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $mainPlayer, "TURNBANISHFACEDOWN", 1);
      break;
    case "aegis_archangel_of_protection":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      AddDecisionQueue("PLAYAURA", $mainPlayer, "spectral_shield-2", 1);
      break;
    case "sekem_archangel_of_ravages":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      DealArcane(2, 2, "PLAYCARD", $allies[$i], false, $mainPlayer, isPassable: 1);
      break;
    case "avalon_archangel_of_rebirth":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      MZMoveCard($mainPlayer, "MYDISCARD:pitch=2", "MYTOPDECK", isSubsequent: true);
      break;
    case "metis_archangel_of_tenacity":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, "metis_archangel_of_tenacity", 1);
      break;
    case "victoria_archangel_of_triumph":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      AddDecisionQueue("ADDCURRENTANDNEXTTURNEFFECT", $defPlayer, "victoria_archangel_of_triumph", 1);
      break;
    case "bellona_archangel_of_war":
      $soul = &GetSoul($mainPlayer);
      if (count($soul) == 0) break;
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish a card from soul");
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      MZMoveCard($mainPlayer, "MYSOUL", "MYBANISH,SOUL,-", isSubsequent: true);
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYALLY:subtype=Angel", 1);
      AddDecisionQueue("ADDALLATTACKCOUNTERS", $mainPlayer, "1", 1);
      break;
    default:
      break;
  }
}

function AllyDamageTakenAbilities($player, $i)
{
  $allies = &GetAllies($player);
  switch ($allies[$i]) {
    case "nekria":
      $allies[$i + 2] -= 1;
      $allies[$i + 7] -= 1;
      PutPermanentIntoPlay($player, "ash");
      WriteLog(CardLink($allies[$i], $allies[$i]) . " got a -1 life counter and created an ash token");
      break;
    default:
      break;
  }
}

function AllyTakeDamageAbilities($player, $index, $damage, $preventable)
{
  $allies = &GetAllies($player);
  //CR 2.1 6.4.10f If an effect states that a prevention effect can not prevent the damage of an event, the prevention effect still applies to the event but its prevention amount is not reduced. Any additional modifications to the event by the prevention effect still occur.
  $remove = false;
  $preventedDamage = 0;
  if ($damage > 0 && HasWard($allies[$index], $player)) {
    if ($preventable) $preventedDamage += WardAmount($allies[$index], $player);
    $remove = true;
    WardPoppedAbility($player, $allies[$index]);
  }
  switch ($allies[$index]) {
    default:
      break;
  }
  if ($remove) DestroyAlly($player, $index, uniqueID: $allies[$index + 5]);
  if ($preventedDamage > 0 && SearchCurrentTurnEffects("vambrace_of_determination", $player) != "") {
    $preventedDamage -= 1;
    SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
  }
  $damage -= $preventedDamage;
  if ($damage <= 0) $damage = 0;
  return $damage;
}

function AllyBeginEndTurnEffects()
{
  global $mainPlayer, $defPlayer;
  //CR 2.0 4.4.3a Reset life for all allies
  $mainAllies = &GetAllies($mainPlayer);
  updateAllyHealth($mainAllies);

  $defAllies = &GetAllies($defPlayer);
  updateAllyHealth($defAllies);
}

function updateAllyHealth(&$allies)
{
  $pieces = AllyPieces();
  $count = count($allies);
  for ($i = 0; $i < $count; $i += $pieces) {
    if ($allies[$i + 1] != 0) {
      $allies[$i + 1] = 2;
      $allies[$i + 2] = AllyHealth($allies[$i]) + $allies[$i + 7];
      $allies[$i + 8] = 1;
      $allies[$i + 10] = 0;
    }
  }
}

function AllyEndTurnAbilities()
{
  global $mainPlayer;
  $allies = &GetAllies($mainPlayer);
  for ($i = count($allies) - AllyPieces(); $i >= 0; $i -= AllyPieces()) {
    switch ($allies[$i]) {
      case "UPR551":
        DestroyAlly($mainPlayer, $i, true);
        break;
      default:
        break;
    }
  }
}
