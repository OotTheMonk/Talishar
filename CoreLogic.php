<?php

include "CardSetters.php";
include "CardGetters.php";

function EvaluateCombatChain(&$totalAttack, &$totalDefense, &$attackModifiers = [], $secondNeedleCheck = false)
{
  global $CombatChain, $mainPlayer, $currentTurnEffects, $combatChainState, $CCS_LinkBaseAttack, $CCS_WeaponIndex;
  global $CCS_WeaponIndex, $combatChain, $defPlayer;
  BuildMainPlayerGameState();
  $attackType = CardType($CombatChain->AttackCard()->ID());
  $canGainAttack = CanGainAttack($CombatChain->AttackCard()->ID());
  $snagActive = SearchCurrentTurnEffects("snag_blue", $mainPlayer) && $attackType == "AA";

  for ($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, true);
    if ($chainCard->ID() == "manifestation_of_miragai_blue" && SearchCharacterActive($mainPlayer, "cosmo_scroll_of_ancestral_tapestry")) {
      $combatChainState[$CCS_LinkBaseAttack] = WardAmount($chainCard->ID(), $mainPlayer, $combatChainState[$CCS_WeaponIndex]);
    }
    if ($chainCard->PlayerID() == $mainPlayer) {
      if ($i == 0 && $attackType != "W") $attack = $combatChainState[$CCS_LinkBaseAttack];
      else $attack = AttackValue($chainCard->ID());
      if ($canGainAttack || $i == 0 || $attack < 0) {
        array_push($attackModifiers, $chainCard->ID());
        array_push($attackModifiers, $attack);
        if ($i == 0) $totalAttack += $attack;
        else AddAttack($totalAttack, $attack);
      }
      $attack = AttackModifier($chainCard->ID(), $chainCard->From(), $chainCard->ResourcesPaid(), $chainCard->RepriseActive()) + $chainCard->AttackValue();
      if (($canGainAttack && !$snagActive) || $attack < 0) {
        array_push($attackModifiers, $chainCard->ID());
        array_push($attackModifiers, $attack);
        AddAttack($totalAttack, $attack);
      }
    } else {
      $totalDefense += BlockingCardDefense($i * CombatChainPieces());
    }
  }
  // //Now check current turn effects
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    if (IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i)) {
      if ($currentTurnEffects[$i + 1] == $mainPlayer) {
        $attack = EffectAttackModifier($currentTurnEffects[$i]);
        if (($canGainAttack || $attack < 0) && !($snagActive && ($currentTurnEffects[$i] == $CombatChain->AttackCard()->ID() || CardType(EffectCardID($currentTurnEffects[$i])) == "AR"))) {
          array_push($attackModifiers, $currentTurnEffects[$i]);
          array_push($attackModifiers, $attack);
          AddAttack($totalAttack, $attack);
        }
      }
    }
  }
  // check layer continuous buffs
  if(isset($combatChain[10])) {
    $layerContBuffs = explode(",", $combatChain[10]);
    foreach ($layerContBuffs as $buff) {
      $attack = EffectAttackModifier($buff);
      if (($canGainAttack || $attack < 0) && !($snagActive && ($buff == $CombatChain->AttackCard()->ID() || CardType(EffectCardID($buff)) == "AR"))) {
        array_push($attackModifiers, $buff);
        array_push($attackModifiers, $attack);
        AddAttack($totalAttack, $attack);
      }
    }
  }
  if ($combatChainState[$CCS_WeaponIndex] != -1) {
    $attack = 0;
    if ($attackType == "W") {
      $char = &GetPlayerCharacter($mainPlayer);
      $attack = $char[$combatChainState[$CCS_WeaponIndex] + 3];
      if (filter_var($attack, FILTER_VALIDATE_INT) === false) $attack = 0;
    } else if (DelimStringContains(CardSubtype($CombatChain->AttackCard()->ID()), "Aura")) {
      $auras = &GetAuras($mainPlayer);
      if (isset($auras[$combatChainState[$CCS_WeaponIndex]])) $attack = $auras[$combatChainState[$CCS_WeaponIndex] + 3];
    } else if (DelimStringContains(CardSubtype($CombatChain->AttackCard()->ID()), "Ally")) {
      $allies = &GetAllies($mainPlayer);
      if (isset($allies[$combatChainState[$CCS_WeaponIndex]])) $attack = $allies[$combatChainState[$CCS_WeaponIndex] + 9];
    }
    if ($canGainAttack || $attack < 0) {
      array_push($attackModifiers, "ATKCOU"); //Attack Counter image ID
      array_push($attackModifiers, $attack);
      AddAttack($totalAttack, $attack);
    }
  }
  $attack = MainCharacterAttackModifiers($attackModifiers);
  if ($canGainAttack || $attack < 0) {
    AddAttack($totalAttack, $attack);
  }
  $attack = AuraAttackModifiers(0, $attackModifiers);
  if ($canGainAttack || $attack < 0) {
    AddAttack($totalAttack, $attack);
  }
  $attack = ArsenalAttackModifier($attackModifiers);
  if ($canGainAttack || $attack < 0) {
    AddAttack($totalAttack, $attack);
  }
  $attack = ItemAttackModifiers($attackModifiers);
  if ($canGainAttack || $attack < 0) {
    AddAttack($totalAttack, $attack);
  }
  CurrentEffectAfterPlayOrActivateAbility(false); //checking gauntlets of iron will
  if (!$secondNeedleCheck) {
    switch ($combatChain[0]) {
      case "zephyr_needle":
      case "zephyr_needle":
        for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
          $blockVal = (intval(BlockValue($combatChain[$i])) + BlockModifier($combatChain[$i], "CC", 0) + $combatChain[$i + 6]);
          if ($totalDefense > 0 && $blockVal > $totalAttack && $combatChain[$i + 1] == $defPlayer) {
            $char = GetPlayerCharacter($mainPlayer);
            $charID = -1;
            for ($i = 0; $i < count($char); $i += CharacterPieces()) {
              if ($char[$i + 11] == $combatChain[8]) $charID = $i;
            }
            if ($charID == -1) WriteLog("something went wrong, please submit a bug report");
            if (SearchLayersForCardID($combatChain[0]) == -1 && $char[$charID + 7] != "1") {
              AddLayer("TRIGGER", $mainPlayer, $combatChain[0]);
            }
            break;
          }
        }
        break;
      default:
        break;
    }
  }
}

function AddAttack(&$totalAttack, $amount): void
{
  global $CombatChain, $mainPlayer;
  $attackID = $CombatChain->AttackCard()->ID();
  if (PowerCantBeModified($attackID)) return;
  if ($amount > 0 && $attackID == "amplifying_arrow_yellow") $amount += 1;
  if ($amount > 0 && SearchCurrentTurnEffects("thrive_yellow", $mainPlayer)) {
    $num_thrives_active = CountCurrentTurnEffects("thrive_yellow", $mainPlayer); //thrives stack so get all the active effects before applying bonus
    $amount += $num_thrives_active;
  }
  if ($amount > 0) {
    SearchCurrentTurnEffects("flourish_yellow-INACTIVE", $mainPlayer, false, false, true);
    SearchCurrentTurnEffects("flourish_blue-INACTIVE", $mainPlayer, false, false, true);
  }
  if ($amount > 0 && ($attackID == "back_heel_kick_red" || $attackID == "back_heel_kick_yellow" || $attackID == "back_heel_kick_blue") && ComboActive()) $amount += 1;
  if ($amount > 0) $amount += PermanentAddAttackAbilities();
  $totalAttack += $amount;
}

function BlockingCardDefense($index)
{
  global $combatChain, $defPlayer, $currentTurnEffects;
  $from = isset($combatChain[$index + 2]) ? $combatChain[$index + 2] : "-";
  $cardID = isset($combatChain[$index]) ? $combatChain[$index] : "-";
  $baseCost = ($from == "PLAY" || $from == "EQUIP" ? AbilityCost($cardID) : (CardCost($cardID) + SelfCostModifier($cardID, $from)));
  $resourcesPaid = (isset($combatChain[$index + 3]) ? intval($combatChain[$index + 3]) : 0) + intval($baseCost);
  $defense = intval(BlockValue($cardID)) + (BlockCantBeModified($cardID) ? 0 : (isset($combatChain[$index + 6]) ? intval(BlockModifier($cardID, $from, $resourcesPaid)) + intval($combatChain[$index + 6]) : 0));
  if (TypeContains($cardID, "E", $defPlayer)) {
    $defCharacter = &GetPlayerCharacter($defPlayer);
    $charIndex = isset($combatChain[$index + 8]) ? SearchCharacterForUniqueID($combatChain[$index + 8], $defPlayer) : null;
    $defense += $defCharacter[$charIndex + 4];
  }
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    if (IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i)) {
      if ($currentTurnEffects[$i + 1] == $defPlayer) {
        $defense += EffectBlockModifier($currentTurnEffects[$i], $index, $from);
      }
    }
  }
  if ($defense < 0) $defense = 0;
  return $defense;
}

function AddCombatChain($cardID, $player, $from, $resourcesPaid, $OriginUniqueID)
{
  global $combatChain, $turn;
  $index = count($combatChain);
  array_push($combatChain, $cardID);
  array_push($combatChain, $player);
  array_push($combatChain, $from);
  array_push($combatChain, $resourcesPaid);
  array_push($combatChain, RepriseActive());
  array_push($combatChain, 0);//Attack modifier
  array_push($combatChain, 0);//Defense modifier
  array_push($combatChain, GetUniqueId($cardID, $player));
  array_push($combatChain, $OriginUniqueID);
  array_push($combatChain, $cardID); //original cardID in case it becomes a copy
  array_push($combatChain, "-"); //Added static buffs, "," separated list
  if ($turn[0] == "B" || CardType($cardID) == "DR" || DefendingTerm($turn[0])) OnBlockEffects($index, $from);
  CurrentEffectAttackAbility();
  return $index;
}

function DefendingTerm($term)
{
  switch ($term) {
    case "ADDCARDTOCHAINASDEFENDINGCARD": // Pulsewave Harpoon, Pulsewave Protocol, etc.
    case "PROVOKE":
      return true;
    default:
      return false;
  }
}

function CombatChainPowerModifier($index, $amount)
{
  global $combatChain, $mainPlayer;
  $combatChain[$index + 5] += $amount;
  ProcessPhantasmOnBlock($index);
  ProcessAllMirage();
}

function StartTurnAbilities()
{
  global $mainPlayer, $defPlayer;
  $mainCharacter = &GetPlayerCharacter($mainPlayer);
  $defCharacter = &GetPlayerCharacter($defPlayer);
  if($mainCharacter[13]) AddCurrentTurnEffect("marked", $mainPlayer);  //Marked stays between turns
  if($defCharacter[13]) AddCurrentTurnEffect("marked", $defPlayer); //Marked stays between turns
  for ($i = count($mainCharacter) - CharacterPieces(); $i >= 0; $i -= CharacterPieces()) {
    CharacterStartTurnAbility($i);
  }
  ArsenalStartTurnAbilities();
  DefCharacterStartTurnAbilities();
  ArsenalStartTurnAbilities();
  AuraStartTurnAbilities();
  PermanentStartTurnAbilities();
  AllyStartTurnAbilities($mainPlayer); 
  LandmarkStartTurnAbilities();

  AuraBeginningActionPhaseAbilities();

  $mainItems = &GetItems($mainPlayer);
  for ($i = count($mainItems) - ItemPieces(); $i >= 0; $i -= ItemPieces()) {
    $mainItems[$i + 2] = "2";
    $mainItems[$i + 3] = ItemUses($mainItems[$i]);
    ItemStartTurnAbility($i);
  }
  $defItems = &GetItems($defPlayer);
  for ($i = 0; $i < count($defItems); $i += ItemPieces()) {
    $defItems[$i + 2] = "2";
    $defItems[$i + 3] = ItemUses($defItems[$i]);
    $defItems[$i + 7] = "0";//Reset Frozen
  }
  $defCharacter = &GetPlayerCharacter($defPlayer);
  for ($i = 0; $i < count($defCharacter); $i += CharacterPieces()) {
    $defCharacter[$i + 8] = "0";//Reset Frozen
  }
  $defAllies = &GetAllies($defPlayer);
  for ($i = 0; $i < count($defAllies); $i += AllyPieces()) {
    $defAllies[$i + 3] = "0";//Reset Frozen
  }
  $defArsenal = &GetArsenal($defPlayer);
  for ($i = 0; $i < count($defArsenal); $i += ArsenalPieces()) {
    $defArsenal[$i + 4] = "0";//Reset Frozen
  }
  MZStartTurnMayAbilities();
}

function MZStartTurnMayAbilities()
{
  global $mainPlayer;
  AddDecisionQueue("FINDINDICES", $mainPlayer, "MZSTARTTURN");
  AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a start of turn ability to activate (or pass)", 1);
  AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
  AddDecisionQueue("MZSTARTTURNABILITY", $mainPlayer, "-", 1);
}

function MZStartTurnIndices()
{
  global $mainPlayer;
  $graveyard = &GetDiscard($mainPlayer);
  $cards = "";
  for ($i = 0; $i < count($graveyard); $i += DiscardPieces()) {
    switch ($graveyard[$i]) {
      case "thaw_red":
        if (ThawIndices($mainPlayer) != "") $cards = CombineSearches($cards, SearchMultiZoneFormat($i, "MYDISCARD"));
        break;
      case "blacktek_whisperers":
      case "mask_of_perdition":
      case "redback_shroud":
      case "shriek_razors":
        $emptyEquipmentSlots = explode(",", FindEmptyEquipmentSlots($mainPlayer));
        $discardIndex = SearchDiscardForCard($mainPlayer, $graveyard[$i]);
        $foundSlot = in_array(CardSubType($graveyard[$i]), $emptyEquipmentSlots);
        if (CountItem("silver", $mainPlayer) >= 2 && $discardIndex != "" && $foundSlot) {
          AddDecisionQueue("COUNTITEM", $mainPlayer, "silver");
          AddDecisionQueue("LESSTHANPASS", $mainPlayer, "2");
          AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_pay_2_".Cardlink("silver", "silver")."_and_equip_" . CardLink($graveyard[$i], $graveyard[$i]), 1);
          AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, "silver-2", 1);
          AddDecisionQueue("FINDANDDESTROYITEM", $mainPlayer, "<-", 1);
          AddDecisionQueue("EQUIPCARD", $mainPlayer, $graveyard[$i]."-".CardSubType($graveyard[$i]), 1);
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, "MYDISCARD-" . $discardIndex, 1);
          AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
        }
        break;
      case "loyalty_beyond_the_grave_red":
        $foundLoyalties = SearchDiscard($mainPlayer, nameIncludes:"Loyalty,Beyond,the,Grave");
        if (count(explode(",", $foundLoyalties)) >= 2) $cards = CombineSearches($cards, SearchMultiZoneFormat($i, "MYDISCARD"));
        break;
      default:
        break;
    }
  }
  return $cards;
}

function FindEmptyEquipmentSlots($player)
{
  $character = &GetPlayerCharacter($player);
  $available = array_filter(["Head", "Chest", "Arms", "Legs"], function ($slot) use ($character) {
    for ($i = 0; $i < count($character); $i += CharacterPieces()) {
      $subtype = CardSubType($character[$i], $character[$i + 11]);
      $status = $character[$i + 1];
      if (DelimStringContains($subtype, $slot) && $status != 0) return false;
    }
    return true;
  });
  return empty($available) ? "" : implode(",", $available);
}

function ArsenalStartTurnAbilities()
{
  global $mainPlayer;
  $arsenal = &GetArsenal($mainPlayer);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    switch ($arsenal[$i]) {
      case "the_librarian":
      case "minerva_themis":
      case "lady_barthimont":
      case "lord_sutcliffe":
      case "hala_goldenhelm":
      case "chief_rukutan":
        if ($arsenal[$i + 1] == "DOWN") {
          AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_turn_".CardLink($arsenal[$i], $arsenal[$i])."_face_up");
          AddDecisionQueue("NOPASS", $mainPlayer, "-");
          AddDecisionQueue("TURNARSENALFACEUP", $mainPlayer, $i, 1);
        }
        break;
      case "the_hand_that_pulls_the_strings":
        if ($arsenal[$i + 1] == "UP") {
          AddCurrentTurnEffect("the_hand_that_pulls_the_strings", $mainPlayer);
        }
      default:
        break;
    }
  }
}

function ArsenalBeginEndPhaseAbilities()
{
  global $mainPlayer;
  $arsenal = &GetArsenal($mainPlayer);
  for ($i = count($arsenal) - ArsenalPieces(); $i >= 0; $i -= ArsenalPieces()) {
    switch ($arsenal[$i]) {
      case "the_hand_that_pulls_the_strings":
        if ($arsenal[$i + 1] == "UP") {
          if(CountItem("silver", $mainPlayer) >= 1) {
            AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_destroy_a_".Cardlink("silver", "silver")."_or_put_".CardLink($arsenal[$i], $arsenal[$i])."_at_the_bottom_of_your_deck", 1);
            AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
            AddDecisionQueue("PASSPARAMETER", $mainPlayer, "silver-2", 1);
            AddDecisionQueue("FINDANDDESTROYITEM", $mainPlayer, "<-", 1);
            AddDecisionQueue("ELSE", $mainPlayer, "-");
          }
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, "MYARS-" . $i, 1);
          AddDecisionQueue("MZADDZONE", $mainPlayer, "MYBOTDECK", 1);
          AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
          AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
        }
        break;
      default:
        break;
    }
  }
}


function ArsenalAttackAbilities()
{
  global $CombatChain, $mainPlayer;
  $attackID = $CombatChain->AttackCard()->ID();
  $arsenal = GetArsenal($mainPlayer);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    switch ($arsenal[$i]) {
      case "lady_barthimont":
        if (CardType($attackID) == "AA" && $arsenal[$i + 1] == "UP") LadyBarthimontAbility($mainPlayer, $i);
        break;
      case "chief_rukutan":
        if (CardType($attackID) == "AA" && ModifiedAttackValue($attackID, $mainPlayer, "CC", source: $arsenal[$i]) >= 6 && $arsenal[$i + 1] == "UP") ChiefRukutanAbility($mainPlayer, $i);
        break;
      default:
        break;
    }
  }
}

function ArsenalAttackModifier(&$attackModifiers)
{
  global $CombatChain, $mainPlayer;
  $attackID = $CombatChain->AttackCard()->ID();
  $attackType = CardType($attackID);
  $arsenal = GetArsenal($mainPlayer);
  $modifier = 0;
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    switch ($arsenal[$i]) {
      case "minerva_themis":
        $modifier += ($arsenal[$i + 1] == "UP" && TypeContains($attackID, "W", $mainPlayer) && Is1H($attackID) ? 1 : 0);
        array_push($attackModifiers, $arsenal[$i]);
        array_push($attackModifiers, $modifier);
        break;
      default:
        break;
    }
  }
  return $modifier;
}

function ArsenalHitEffects()
{
  global $CombatChain, $mainPlayer;
  $attackID = $CombatChain->AttackCard()->ID();
  $arsenal = GetArsenal($mainPlayer);
  $modifier = 0;
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    switch ($arsenal[$i]) {
      case "minerva_themis":
        if ($arsenal[$i + 1] == "UP" && TypeContains($attackID, "W", $mainPlayer)) {
          MinervaThemisAbility($mainPlayer, $i);
          break;
        }
      case "hala_goldenhelm":
        if ($arsenal[$i + 1] == "UP" && TypeContains($attackID, "W", $mainPlayer) && CardSubType($attackID) == "Sword") {
          HalaGoldenhelmAbility($mainPlayer, $i);
          break;
        }
      default:
        break;
    }
  }
  return $modifier;
}

function ArsenalPlayCardAbilities($cardID)
{
  global $currentPlayer;
  $cardType = CardType($cardID);
  $arsenal = GetArsenal($currentPlayer);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    switch ($arsenal[$i]) {
      case "lord_sutcliffe":
        if ($arsenal[$i + 1] == "UP" && DelimStringContains($cardType, "A")) LordSutcliffeAbility($currentPlayer, $i);
        break;
      default:
        break;
    }
  }
}

function HasIncreasedAttack()
{
  global $CombatChain, $combatChainState, $CCS_LinkBaseAttack, $mainPlayer, $combatChain;
  if ($CombatChain->HasCurrentLink()) {
    $attack = CachedTotalAttack();
    if (SearchCharacterActive($mainPlayer, "cosmo_scroll_of_ancestral_tapestry") && HasWard($combatChain[0], $mainPlayer) && SubtypeContains($combatChain[0], "Aura", $mainPlayer)) {
      if ($attack > WardAmount($combatChain[0], $mainPlayer)) return true;
      else return false;
    }
    if ($attack > $combatChainState[$CCS_LinkBaseAttack]) return true;
  }
  return false;
}

function DamageTrigger($player, $damage, $type, $source = "NA")
{
  PrependDecisionQueue("DEALDAMAGE", $player, $damage . "-" . $source . "-" . $type);
  return $damage;
}

function CanDamageBePrevented($player, $damage, $type, $source = "-")
{
  global $mainPlayer;
  $otherPlayer = ($player == 1 ? 2 : 1);
  if ($type == "ARCANE" && SearchCurrentTurnEffects("swarming_gloomveil_red", $player)) return false;
  if ($source == "runechant" && (SearchCurrentTurnEffects("vynnset", $otherPlayer) || SearchCurrentTurnEffects("vynnset_iron_maiden", $otherPlayer))) return false;
  if (SearchCurrentTurnEffects("tiger_stripe_shuko", $otherPlayer)) return false;
  if ($type == "COMBAT" && SearchCurrentTurnEffects("chorus_of_ironsong_yellow", $mainPlayer)) return false;
  if ($type == "COMBAT" && SearchCurrentTurnEffects("jagged_edge_red", $mainPlayer)) return false;
  if ($source == "rok" || $source == "malign_red" || $source == "malign_yellow" || $source == "malign_blue" || $source == "murkmire_grapnel_red" || $source == "murkmire_grapnel_yellow" || $source == "murkmire_grapnel_blue") return false;
  if (($source == "pick_to_pieces_red" || $source == "pick_to_pieces_yellow" || $source == "pick_to_pieces_blue") && NumAttackReactionsPlayed() > 0) return false;
  if ($source == "war_cry_of_bellona_yellow") return false;
  return true;
}

function DealDamageAsync($player, $damage, $type = "DAMAGE", $source = "NA")
{
  global $CS_DamagePrevention, $combatChain, $CS_ArcaneDamagePrevention, $dqVars, $dqState;
  $classState = &GetPlayerClassState($player);
  if ($type == "COMBAT" || $type == "ATTACKHIT") $source = $combatChain[0];
  $otherPlayer = $player == 1 ? 2 : 1;
  $damage = $damage > 0 ? $damage : 0;
  $origDamage = $damage;
  $preventable = CanDamageBePrevented($player, $damage, $type, $source);
  if ($damage > 0) $damage += CurrentEffectDamageModifiers($player, $source, $type);
  if ($preventable) {
    if ($damage > 0) $damage = CurrentEffectPreventDamagePrevention($player, $type, $damage, $source);
    if (ConsumeDamagePrevention($player)) return 0;//I damage can be prevented outright, don't use up your limited damage prevention
    if ($type == "ARCANE") {
      if ($damage <= $classState[$CS_ArcaneDamagePrevention]) {
        $classState[$CS_ArcaneDamagePrevention] -= $damage;
        $damage = 0;
      } else {
        $damage -= $classState[$CS_ArcaneDamagePrevention];
        $classState[$CS_ArcaneDamagePrevention] = 0;
      }
    }
    if ($damage > 0) {
      CheckIfPreventionEffectIsActive($player, $damage);
      if ($classState[$CS_DamagePrevention] > 0) {
        if($damage <= $classState[$CS_DamagePrevention]) {
          $classState[$CS_DamagePrevention] -= $damage;
          $damage = 0;
        } else {
          $damage -= $classState[$CS_DamagePrevention];
          $classState[$CS_DamagePrevention] = 0;
        }
        if (SearchCurrentTurnEffects("vambrace_of_determination", $player) != "") {//vambrace
          $damage += 1;
          SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
          // if there's still floating prevention after vambrace we do it again
          // in the future this may need to be re-implemented as a while loop
          if ($classState[$CS_DamagePrevention] > 0) {
            if($damage <= $classState[$CS_DamagePrevention]) {
              $classState[$CS_DamagePrevention] -= $damage;
              $damage = 0;
            } else {
              $damage -= $classState[$CS_DamagePrevention];
              $classState[$CS_DamagePrevention] = 0;
            }
          }
        }
      }
    }
  }
  //else: CR 2.0 6.4.10h If damage is not prevented, damage prevention effects are not consumed
  $damage = $damage > 0 ? $damage : 0;
  $damage = CurrentEffectDamagePrevention($player, $type, $damage, $source, $preventable);
  $damage = AuraTakeDamageAbilities($player, $damage, $type, $source);
  $damage = PermanentTakeDamageAbilities($player, $damage, $type, $source);
  $damage = ItemTakeDamageAbilities($player, $damage, $type, $source);
  $damage = CharacterTakeDamageAbilities($player, $damage, $type, $preventable);
  if ($damage == 1 && $preventable && SearchItemsForCard("dissolution_sphere_yellow", $player) != "") $damage = 0;//Must be last
  $dqVars[0] = $damage;
  if ($type == "COMBAT") $dqState[6] = $damage;
  PrependDecisionQueue("FINALIZEDAMAGE", $player, $damage . "," . $type . "," . $source);
  if ($damage > 0) AddDamagePreventionSelection($player, $damage, $type, $preventable);
  if ($source == "runechant") {
    SearchCurrentTurnEffects("vynnset", $otherPlayer, true);
    SearchCurrentTurnEffects("vynnset_iron_maiden", $otherPlayer, true);
  }
  ResetAuraStatus($player);
  if($damage < $origDamage) LogDamagePreventedStats($player, $origDamage - $damage);
  return $damage;
}

function CheckIfPreventionEffectIsActive($player, $damage): void
{
  global $currentTurnEffects;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = 0;
    if($currentTurnEffects[$i+1] != $player) continue;
    switch ($currentTurnEffects[$i]) {
      case "haunting_rendition_red":
        if($damage > 0) PlayAura("runechant", $player); // Runechant
        $damage -= 2;
        $remove = 1;
        break;
      case "mental_block_blue":
        if($damage > 0) PlayAura("ponder", $player); // Ponder
        $damage -= 2;
        $remove = 1;
        break;
      default:
        break;
    }
    if ($remove == 1) RemoveCurrentTurnEffect($i);
  }
}


function ResetAuraStatus($player)
{
  $auras = &GetAuras($player);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    switch ($auras[$i]) {
      case "runeblood_barrier_yellow":
        $auras[$i + 1] = 2;
        break;
      default:
        break;
    }
  }
}

function AddDamagePreventionSelection($player, $damage, $type, $preventable)
{
  PrependDecisionQueue("PROCESSDAMAGEPREVENTION", $player, $damage . "-" . $preventable . "-" . $type, 1);
  PrependDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
  PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a card to prevent damage: " . $damage . " damage left", 1);
  PrependDecisionQueue("FINDINDICES", $player, "DAMAGEPREVENTION," . $type);
}

function FinalizeDamage($player, $damage, $damageThreatened, $type, $source)
{
  global $otherPlayer, $CS_DamageTaken, $combatChainState, $CCS_AttackTotalDamage, $CS_ArcaneDamageTaken, $defPlayer, $mainPlayer;
  global $CS_DamageDealt, $CS_PowDamageDealt, $CS_DamageDealtToOpponent, $combatChain;
  $classState = &GetPlayerClassState($player);
  $otherPlayer = $player == 1 ? 2 : 1;
  if ($damage > 0) {
    if ($source != "NA") {
      $otherCharacter = &GetPlayerCharacter($otherPlayer);
      $characterID = ShiyanaCharacter($otherCharacter[0]);
      DamageDealtAbilities($player, $damage, $type, $source);
      if ($source == "dread_scythe" && !SearchNextTurnEffects("dread_scythe", $player)) AddNextTurnEffect("dread_scythe", $player);
      if (($characterID == "briar_warden_of_thorns" || $characterID == "briar") && $type == "ARCANE" && $otherCharacter[1] == "2" && CardType($source) == "AA" && !SearchAuras("embodiment_of_earth", $otherPlayer)) {
        PlayAura("embodiment_of_earth", $otherPlayer);
      }
      if ($source == "cryptic_crossing_yellow" && SearchCurrentTurnEffects("cryptic_crossing_yellow", $mainPlayer, true)) {
        WriteLog("Player " . $mainPlayer . " drew a card and Player " . $otherPlayer . " must discard a card");
        Draw($mainPlayer);
        PummelHit();
      }
    }
    AuraDamageTakenAbilities($player, $damage, $source);
    ItemDamageTakenAbilities($player, $damage);
    CharacterDamageTakenAbilities($player, $damage);
    AuraDamageDealtAbilities($otherPlayer, $damage);
    CharacterDealDamageAbilities($otherPlayer, $damage);
    if (SearchAuras("ode_to_wrath_yellow", $otherPlayer)) {
      LoseHealth(CountAura("ode_to_wrath_yellow", $otherPlayer), $player);
      WriteLog("Lost life from Ode to Wrath");
    }
    $classState[$CS_DamageTaken] += $damage;
    if (!IsAllyAttacking()) IncrementClassState($otherPlayer, $CS_DamageDealtToOpponent, $damage);
    else {
      $allyInd = SearchAlliesForUniqueID($combatChain[8], $otherPlayer);
      $allies = &GetAllies($otherPlayer);
      $allies[$allyInd + 10] += $damage;
    }
    // add ally tracking  here
    if ($type !== "COMBAT") SetClassState($otherPlayer, $CS_DamageDealt, GetClassState($otherPlayer, $CS_DamageDealt) + $damage);
    else SetClassState($otherPlayer, $CS_PowDamageDealt, GetClassState($otherPlayer, $CS_PowDamageDealt) + $damage);
    if ($player == $defPlayer && $type == "COMBAT" || $type == "ATTACKHIT") $combatChainState[$CCS_AttackTotalDamage] += $damage;
    if ($type == "ARCANE") $classState[$CS_ArcaneDamageTaken] += $damage;
    CurrentEffectDamageEffects($player, $source, $type, $damage);
  }
  if ($damage > 0 && ($type == "COMBAT" || $type == "ATTACKHIT") && SearchCurrentTurnEffects("ice_storm_red-2", $otherPlayer) && IsHeroAttackTarget()) {
    for ($i = 0; $i < $damage; ++$i) PlayAura("frostbite", $player, effectController:$otherPlayer);
  }
  LogDamageStats($player, $damageThreatened, $damage);
  PlayerLoseHealth($player, $damage);
  return $damage;
}

function DamageDealtAbilities($player, $damage, $type, $source)
{
  global $mainPlayer, $combatChainState, $CCS_AttackFused;
  if (($source == "explosive_growth_red" || $source == "explosive_growth_yellow" || $source == "explosive_growth_blue") && $combatChainState[$CCS_AttackFused]) AddCurrentTurnEffect($source, $mainPlayer);
  if ($source == "suraya_archangel_of_knowledge") GainHealth($damage, $mainPlayer);
}

function DoQuell($targetPlayer, $damage)
{
  $quellChoices = QuellChoices($targetPlayer, $damage);
  if ($quellChoices != "0") {
    PrependDecisionQueue("PAYRESOURCES", $targetPlayer, "<-", 1);
    PrependDecisionQueue("AFTERQUELL", $targetPlayer, "-", 1);
    PrependDecisionQueue("BUTTONINPUT", $targetPlayer, $quellChoices);
    PrependDecisionQueue("SETDQCONTEXT", $targetPlayer, "Choose an amount to pay for Quell");
  } else {
    PrependDecisionQueue("PASSPARAMETER", $targetPlayer, "0"); //If no quell, we need to discard the previous last result
  }
}

function PreventLethal($targetPlayer, $damage)
{
  global $CS_NextDamagePrevented;
  if (SearchHandForCard($targetPlayer, "minerva_themis") != "") {
    MZMoveCard($targetPlayer, "MYHAND", "MYBANISH,HAND'-");
    SetClassState($targetPlayer, $CS_NextDamagePrevented, $damage);
  }
  if (SearchArsenalForCard($targetPlayer, "minerva_themis") != "") {
    MZMoveCard($targetPlayer, "MYARS", "MYBANISH,ARS,-");
    SetClassState($targetPlayer, $CS_NextDamagePrevented, $damage);
  }
}

function ProcessDealDamageEffect($cardID)
{
  $set = CardSet($cardID);
  if ($set == "UPR") {
    return UPRDealDamageEffect($cardID);
  }
}

function ArcaneDamagePrevented($player, $cardMZIndex)
{
  $prevented = 0;
  $params = explode("-", $cardMZIndex);
  $zone = $params[0];
  $index = $params[1];
  switch ($zone) {
    case "MYCHAR":
      $source = &GetPlayerCharacter($player);
      break;
    case "MYITEMS":
      $source = &GetItems($player);
      break;
    case "MYAURAS":
      $source = &GetAuras($player);
      break;
  }
  if ($zone == "MYCHAR" && $source[$index + 1] == 0) return;
  $cardID = $source[$index];
  $spellVoidAmount = SpellVoidAmount($cardID, $player);
  if ($spellVoidAmount > 0) {
    if ($zone == "MYCHAR") DestroyCharacter($player, $index);
    else if ($zone == "MYITEMS") DestroyItemForPlayer($player, $index);
    else if ($zone == "MYAURAS") DestroyAura($player, $index);
    $prevented += $spellVoidAmount;
    WriteLog(CardLink($cardID, $cardID) . " was destroyed and prevented " . $spellVoidAmount . " arcane damage.");
  }
  return $prevented;
}

function CurrentEffectDamageModifiers($player, $source, $type)
{
  global $currentTurnEffects;
  $modifier = 0;
  if($type == "ARCANE") return $modifier; //It's already checked upfront for Arcane
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = 0;
    switch ($currentTurnEffects[$i]) {
      case "frazzle_red":
      case "frazzle_yellow":
      case "frazzle_blue":
        if ($type == "COMBAT" || $type == "ATTACKHIT") ++$modifier;
        break;
      case "ball_lightning_red":
      case "ball_lightning_yellow":
      case "ball_lightning_blue":
        if (TalentContainsAny($source, "LIGHTNING,ELEMENTAL", $player) && (TypeContains($source, "A") || TypeContains($source, "AA"))) ++$modifier;
        break;
      default:
        break;
    }
    if ($remove == 1) RemoveCurrentTurnEffect($i);
  }
  return $modifier;
}

function CurrentEffectDamageEffects($target, $source, $type, $damage)
{
  global $currentTurnEffects, $EffectContext;
  $otherPlayer = ($target == 1 ? 2 : 1);
  if (CardType($source) == "AA" && (SearchAuras("stamp_authority_blue", 1) || SearchAuras("stamp_authority_blue", 2))) return;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $target) {
      continue;
    }
    if ($type == "COMBAT" && HitEffectsArePrevented($source)) continue;
    $remove = 0;
    $EffectContext = $currentTurnEffects[$i];
    switch ($currentTurnEffects[$i]) {
      case "blizzard_bolt_red":
      case "blizzard_bolt_yellow":
      case "blizzard_bolt_blue":
        if (IsHeroAttackTarget() && CardType($source) == "AA")
          PlayAura("frostbite", $target, effectController:$otherPlayer);
        break;
      case "chilling_icevein_red":
      case "chilling_icevein_yellow":
      case "chilling_icevein_blue":
        if (IsHeroAttackTarget() && CardType($source) == "AA")
          PayOrDiscard($target, 1);
        break;
      case "blossoming_spellblade_red":
        if ($source == "blossoming_spellblade_red" && (IsHeroAttackTarget() || $type != "COMBAT"))
          MZMoveCard(($target == 1 ? 2 : 1), "MYDISCARD:type=A", "MYBANISH,GY,blossoming_spellblade_red", may: true);
        break;
      case "sigil_of_permafrost_red":
      case "sigil_of_permafrost_yellow":
      case "sigil_of_permafrost_blue":
        if ((IsHeroAttackTarget() || (IsHeroAttackTarget() == "" && $source != "frostbite")) && $type == "ARCANE") {
          PlayAura("frostbite", $target, $damage, effectController:$otherPlayer);
          $remove = 1;
        }
        break;
      case "shift_the_tide_of_battle_yellow":
        if (IsHeroAttackTarget()) {
          PlayAura("agility", $otherPlayer); //Agility
          $remove = 1;
        }
        break;
      case "staff_of_verdant_shoots": // So technically this procks if you deal damage to yourself but this would need to be refactored in order to make that work. Until someone has this happen, lets just leave it as so.
        if ($source != "frostbite" && $type == "ARCANE") {
          PlayAura("embodiment_of_earth", $currentTurnEffects[$i + 1], 1);
          $remove = 1;
        }
        break;
      default:
        break;
    }
    if ($remove == 1) RemoveCurrentTurnEffect($i);
  }
}

function AttackDamageAbilities($damageDone)
{
  global $combatChain, $defPlayer;
  $attackID = $combatChain[0];
  switch ($attackID) {
    case "light_it_up_yellow":
      if (IsHeroAttackTarget() && $damageDone >= NumEquipment($defPlayer)) {
        AddCurrentTurnEffect("light_it_up_yellow", $defPlayer);
        AddNextTurnEffect("light_it_up_yellow", $defPlayer);
      }
      break;
    default:
      break;
  }
}

function LoseHealth($amount, $player)
{
  PlayerLoseHealth($player, $amount);
}

function GainHealth($amount, $player, $silent = false, $preventable = true)
{
  global $mainPlayer, $CS_HealthGained;
  $otherPlayer = ($player == 1 ? 2 : 1);
  $health = &GetHealth($player);
  $otherHealth = &GetHealth($otherPlayer);
  if ((SearchCurrentTurnEffects("poison_the_well_blue", 1, remove: true) || SearchCurrentTurnEffects("poison_the_well_blue", 2, remove: true)) && $preventable) {
    WriteLog("<span style='color:green;'>🧪 Somebody poisoned the water hole.</span>");
    LoseHealth($amount, $player);
    return false;
  }
  if (SearchCurrentTurnEffects("dread_scythe", $player) && $preventable) {
    WriteLog(CardLink("dread_scythe", "dread_scythe") . " prevented you from gaining life");
    return;
  }
  if ((SearchCharacterForCard($player, "reaping_blade") || SearchCharacterForCard($otherPlayer, "reaping_blade") && $preventable) && $health > $otherHealth) {
    WriteLog(CardLink("reaping_blade", "reaping_blade") . " prevented Player " . $player . " from gaining " . $amount . " life");
    return false;
  }
  $p2Char = &GetPlayerCharacter(2);//Use only for single player for the dummy to be "invincible"
  if (!$silent && $p2Char[0] != "DUMMY") WriteLog("Player " . $player . " gained " . $amount . " life");
  IncrementClassState($player, $CS_HealthGained, $amount);
  if($p2Char[0] != "DUMMY" || $player == 1) $health += $amount;
  LogHealthGainedStats($player, $amount);

  if ($player == $mainPlayer) {
    $char = &GetPlayerCharacter($player);
    for ($i = 0; $i < count($char); $i += CharacterPieces()) {
      if (intval($char[$i + 1]) != 2) continue;
      switch ($char[$i]) {
        case "verdance_thorn_of_the_rose":
          // Now we need to check that we banished 8 earth cards.
          $results = SearchCount(SearchMultiZone($player, "MYBANISH:talent=EARTH"));
          if ($results >= 8) {
            AddLayer("TRIGGER", $mainPlayer, $char[$i], 3);
          }
          break;
        case "verdance":
          // Now we need to check that we banished 4 earth cards.
          $results = SearchCount(SearchMultiZone($player, "MYBANISH:talent=EARTH"));
          if ($results >= 4) {
            AddLayer("TRIGGER", $mainPlayer, $char[$i], 3);
          }
          break;
        default:
          break;
      }
    }
  }
  return true;
}

function PlayerLoseHealth($player, $amount)
{
  global $CS_HealthLost;
  $p2Char = &GetPlayerCharacter(2);//Use only for single player for the dummy to be "invincible"
  $health = &GetHealth($player);
  $amount = AuraLoseHealthAbilities($player, $amount);
  if($p2Char[0] != "DUMMY" || $player == 1) $health -= $amount;
  IncrementClassState($player, $CS_HealthLost, $amount);
  if ($health <= 0 && !IsGameOver()) {
    PlayerWon(($player == 1 ? 2 : 1));
  }
}

function IsGameOver()
{
  global $inGameStatus, $GameStatus_Over;
  return $inGameStatus == $GameStatus_Over;
}

function PlayerWon($playerID)
{
  //NOTE: These globals might appear to be unused. It's because they're written by ParseGamefile.
  global $winner, $turn, $gameName, $p1id, $p2id, $p1uid, $p2uid, $p1IsChallengeActive, $p2IsChallengeActive, $conceded, $currentTurn;
  global $p1DeckLink, $p2DeckLink, $inGameStatus, $GameStatus_Over, $firstPlayer, $p1deckbuilderID, $p2deckbuilderID, $CS_SkipAllRunechants;
  if ($turn[0] == "OVER") return;
  include_once "./MenuFiles/ParseGamefile.php";
  $winner = $playerID;
  if ($playerID == 1 && $p1uid != "") WriteLog("Player 1 (" . $p1uid . ") won! 🎉", $playerID);
  else if ($playerID == 2 && $p2uid != "") WriteLog("Player 2 (" . $p2uid . ") won! 🎉", $playerID);
  else WriteLog("Player " . $winner . " won! 🎉");
  SetClassState(1, $CS_SkipAllRunechants, 0);
  SetClassState(2, $CS_SkipAllRunechants, 0);
  $inGameStatus = $GameStatus_Over;
  $turn[0] = "OVER";
  SetCachePiece($gameName, 14, 99);//$MGS_GameOver
  try {
    logCompletedGameStats();
  } catch (Exception $e) {
  }
}

function UnsetChainLinkBanish()
{
  $p1Banish = new Banish(1);
  $p1Banish->UnsetModifier("TCL");
  $p2Banish = new Banish(2);
  $p2Banish->UnsetModifier("TCL");
}

function UnsetCombatChainBanish()
{
  $p1Banish = new Banish(1);
  $p1Banish->UnsetModifier("TCL");
  $p1Banish->UnsetModifier("TCC");
  $p1Banish->UnsetModifier("TCCGorgonsGaze");
  $p2Banish = new Banish(2);
  $p2Banish->UnsetModifier("TCL");
  $p2Banish->UnsetModifier("TCC");
  $p2Banish->UnsetModifier("TCCGorgonsGaze");
}

function ReplaceBanishModifier($player, $oldMod, $newMod)
{
  $banish = new Banish($player);
  $banish->UnsetModifier($oldMod, $newMod);
}

//TT = This Turn
//NT = Next Turn
//INT = Intimidated
function UnsetTurnBanish()
{
  global $defPlayer, $mainPlayer;
  $p1Banish = new Banish(1);
  $p1Banish->UnsetModifier("TT");
  $p1Banish->UnsetModifier("INST");
  $p1Banish->UnsetModifier("sonic_boom_yellow");
  $p1Banish->UnsetModifier("blossoming_spellblade_red");
  $p1Banish->UnsetModifier("TTFromOtherPlayer");
  $p1Banish->UnsetModifier("shadowrealm_horror_red");
  $p1Banish->UnsetModifier("REMOVEGRAVEYARD");
  $p2Banish = new Banish(2);
  $p2Banish->UnsetModifier("TT");
  $p2Banish->UnsetModifier("INST");
  $p2Banish->UnsetModifier("sonic_boom_yellow");
  $p2Banish->UnsetModifier("blossoming_spellblade_red");
  $p2Banish->UnsetModifier("TTFromOtherPlayer");
  $p2Banish->UnsetModifier("shadowrealm_horror_red");
  $p2Banish->UnsetModifier("REMOVEGRAVEYARD");
  UnsetCombatChainBanish();
  ReplaceBanishModifier($defPlayer, "NT", "TT");
  ReplaceBanishModifier($defPlayer, "NTSTONERAIN", "STONERAIN");
  ReplaceBanishModifier($defPlayer, "TRAPDOOR", "FACEDOWN");
  ReplaceBanishModifier($mainPlayer, "NTFromOtherPlayer", "TTFromOtherPlayer");
}

function GetChainLinkCards($playerID = "", $cardType = "", $exclCardTypes = "", $nameContains = "", $subType = "", $exclCardSubTypes = "")
{
  global $combatChain;
  $pieces = "";
  $exclCardTypeArray = explode(",", $exclCardTypes);
  $exclCardSubTypeArray = explode(",", $exclCardSubTypes);

  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    $thisType = CardType($combatChain[$i]);
    $thisSubType = CardSubType($combatChain[$i]);
    if (($playerID == "" || $combatChain[$i + 1] == $playerID) && ($cardType == "" || $thisType == $cardType) && ($subType == "" || $thisSubType == $subType) && ($nameContains == "" || CardNameContains($combatChain[$i], $nameContains, $playerID, partial: true))) {
      $excluded = false;
      for ($j = 0; $j < count($exclCardTypeArray); ++$j) {
        if ($thisType == $exclCardTypeArray[$j]) $excluded = true;
      }
      for ($k = 0; $k < count($exclCardSubTypeArray); ++$k) {
        if ($thisSubType != "" && DelimStringContains($thisSubType, $exclCardSubTypeArray[$k])) $excluded = true;
      }
      if ($excluded) continue;
      if ($pieces != "") $pieces .= ",";
      $pieces .= $i;
    }
  }
  return $pieces;
}

function GetChainLinkCardIDs($playerID = "", $cardType = "", $exclCardTypes = "", $nameContains = "", $subType = "", $exclCardSubTypes = "")
{
  global $combatChain;
  $cardIDs = "";
  $exclCardTypeArray = explode(",", $exclCardTypes);
  $exclCardSubTypeArray = explode(",", $exclCardSubTypes);
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    $thisType = CardType($combatChain[$i]);
    $thisSubType = CardSubType($combatChain[$i]);
    if (($playerID == "" || $combatChain[$i + 1] == $playerID) && ($cardType == "" || $thisType == $cardType) && ($subType == "" || $thisSubType == $subType) && ($nameContains == "" || CardNameContains($combatChain[$i], $nameContains, $playerID, partial: true))) {
      $excluded = false;
      for ($j = 0; $j < count($exclCardTypeArray); ++$j) {
        if ($thisType == $exclCardTypeArray[$j]) $excluded = true;
      }
      for ($k = 0; $k < count($exclCardSubTypeArray); ++$k) {
        if ($thisSubType != "" && DelimStringContains($thisSubType, $exclCardSubTypeArray[$k])) $excluded = true;
      }
      if ($excluded) continue;
      if ($cardIDs != "") $cardIDs .= ",";
      $cardIDs .= $combatChain[$i];
    }
  }
  return $cardIDs;
}

function ChainLinkResolvedEffects()
{
  global $combatChain, $mainPlayer, $currentTurnEffects, $combatChainState, $CCS_WeaponIndex, $CombatChain, $CCS_GoesWhereAfterLinkResolves, $defPlayer;
  $allies = GetAllies($mainPlayer);
  if ($CombatChain->HasCurrentLink()) {
    if ($combatChain[0] == "exude_confidence_red" && !ExudeConfidenceReactionsPlayable()) AddCurrentTurnEffect($combatChain[0], $mainPlayer, "CC");
  }
  if (IsAllyAttacking() && isset($allies[$combatChainState[$CCS_WeaponIndex] + 2]) && $allies[$combatChainState[$CCS_WeaponIndex] + 2] <= 0) {
    DestroyAlly($mainPlayer, $combatChainState[$CCS_WeaponIndex]);
  }
}

function ResolutionStepEffectTriggers()
{
  global $currentTurnEffects;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $currentEffect = explode("-", $currentTurnEffects[$i]);
    switch ($currentEffect[0]) {
      case "electromagnetic_somersault_red":
      case "electromagnetic_somersault_yellow":
      case "electromagnetic_somersault_blue":
        $player = $currentTurnEffects[$i + 1];
        AddLayer("TRIGGER", $player, $currentEffect[0], $currentEffect[1]);
        RemoveCurrentTurnEffect($i);
        break;
      default:
        break;
    }
  }
}

function ResolutionStepCharacterTriggers()
{
  global $mainPlayer, $combatChain;
  $character = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    $charID = $character[$i];
    switch ($charID) {
      case "nuu_alluring_desire":
      case "nuu":
        if (HasStealth($combatChain[0]) && $character[$i + 1] < 3) {
          AddLayer("TRIGGER", $mainPlayer, $charID, $combatChain[0]);
        }
        break;
      default:
        break;
    }
  }
}

function ResolutionStepAttackTriggers()
{
  global $mainPlayer, $defPlayer, $combatChain, $CID_BloodRotPox, $CS_Transcended;
  switch ($combatChain[0]) {
    case "virulent_touch_red":
    case "virulent_touch_yellow":
    case "virulent_touch_blue":
      for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
        if ($combatChain[$i + 1] != $defPlayer || $combatChain[$i + 2] != "HAND") continue;
        AddLayer("TRIGGER", $mainPlayer, $combatChain[0]);
        break;
      }
      break;
    case "second_tenet_of_chi_moon_blue":
      if (GetClassState($mainPlayer, $CS_Transcended) > 0) {
        AddLayer("TRIGGER", $mainPlayer, $combatChain[0]);
      }
      break;
    default:
      break;
  }
}

function CombatChainClosedMainCharacterEffects()
{
  global $chainLinks, $mainPlayer;
  $character = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($chainLinks); ++$i) {
    for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
      if ($chainLinks[$i][$j + 1] != $mainPlayer) continue;
      $charIndex = FindCharacterIndex($mainPlayer, $chainLinks[$i][$j]);
      if ($charIndex == -1) continue;
      switch ($chainLinks[$i][$j]) {
        case "zephyr_needle":
        case "zephyr_needle":
          if ($character[$charIndex + 7] == "1") DestroyCharacter($mainPlayer, $charIndex);
          break;
        default:
          break;
      }
    }
  }
}

function CombatChainClosedCharacterEffects()
{
  global $chainLinks, $defPlayer, $chainLinkSummary;
  $character = &GetPlayerCharacter($defPlayer);
  for ($i = 0; $i < count($chainLinks); ++$i) {
    $nervesOfSteelActive = $chainLinkSummary[$i * ChainLinkSummaryPieces() + 1] <= 2 && SearchAuras("nerves_of_steel_blue", $defPlayer);
    for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
      if ($chainLinks[$i][$j + 1] != $defPlayer) continue;
      $charIndex = FindCharacterIndex($defPlayer, $chainLinks[$i][$j]);
      if ($charIndex == -1) continue;
      if ($chainLinks[$i][$j] == "carrion_husk") {
        $character[$charIndex + 1] = 0;
        BanishCardForPlayer($chainLinks[$i][$j], $defPlayer, "EQUIP", "NA");
      }
      if (!$nervesOfSteelActive) {

        if (HasTemper($chainLinks[$i][$j])) {
          if ($character[$charIndex + 1] != 0 && $character[$charIndex + 6] != 0) {
            $character[$charIndex + 4] -= 1; //Add -1 block counter
            $character[$charIndex + 6] = 0;
          }
          if ((BlockValue($character[$charIndex]) + $character[$charIndex + 4] + BlockModifier($character[$charIndex], "CC", 0) + $chainLinks[$i][$j + 5]) <= 0) {
            DestroyCharacter($defPlayer, $charIndex);
          }
        } 
        elseif (HasBattleworn($chainLinks[$i][$j]) && $character[$charIndex + 1] != 0) {
          $character[$charIndex + 4] -= 1;//Add -1 block counter
        }
      }
      if (HasGuardwell($chainLinks[$i][$j]) && $character[$charIndex + 1] != 0) {
        $character[$charIndex + 4] -= (BlockValue($character[$charIndex]) + $character[$charIndex + 4] + BlockModifier($character[$charIndex], "CC", 0) + $chainLinks[$i][$j + 5]);//Add -block value counter
        if (IsWeapon($chainLinks[$i][0], "PLAY") && ($chainLinks[$i][$j] == "blade_beckoner_helm" || $chainLinks[$i][$j] == "blade_beckoner_plating" || $chainLinks[$i][$j] == "blade_beckoner_gauntlets" || $chainLinks[$i][$j] == "blade_beckoner_boots")) {
          $character[$charIndex + 4] -= 1;
        }
      } 
      elseif (HasBladeBreak($chainLinks[$i][$j]) && $character[$charIndex + 1] != 0) {
        DestroyCharacter($defPlayer, $charIndex);
      }
      switch ($chainLinks[$i][$j]) {
        case "phantasmal_footsteps":
          if (!DelimStringContains($chainLinkSummary[$i * ChainLinkSummaryPieces() + 3], "ILLUSIONIST") && $chainLinkSummary[$i * ChainLinkSummaryPieces() + 1] >= 6) {
            DestroyCharacter($defPlayer, FindCharacterIndex($defPlayer, "phantasmal_footsteps"));
          }
          break;
        case "ironhide_helm":
        case "ironhide_plate":
        case "ironhide_gauntlet":
        case "ironhide_legs":
          $charIndex = FindCharacterIndex($defPlayer, $chainLinks[$i][$j]);
          if (SearchCurrentTurnEffects($chainLinks[$i][$j], $defPlayer, true)) DestroyCharacter($defPlayer, $charIndex);; //Ironhide
          break;
        case "bone_vizier":
          $deck = new Deck($defPlayer);
          if ($deck->Reveal() && ModifiedAttackValue($deck->Top(), $defPlayer, "DECK", source: "bone_vizier") < 6) {
            $card = $deck->AddBottom($deck->Top(remove: true), "DECK");
            WriteLog(CardLink("bone_vizier", "bone_vizier") . " put " . CardLink($card, $card) . " on the bottom of your deck");
          }
          break;
        default:
          break;
      }
    }
  }
}

function NumDefendedFromHand()
{
  global $CombatChain, $defPlayer;
  $num = 0;
  for ($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, cardNumber: true);
    if ($chainCard->PlayerID() == $defPlayer) {
      if (CardType($chainCard->ID()) != "I" && $chainCard->From() == "HAND") ++$num;
    }
  }
  return $num;
}

function NumCardsBlocking()
{
  global $CombatChain, $defPlayer;
  $num = 0;
  for ($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, cardNumber: true);
    if ($chainCard->PlayerID() == $defPlayer) {
      $type = CardType($chainCard->ID());
      if ($type != "I" && $type != "C") ++$num;
    }
  }
  return $num;
}

function NumCardsNonEquipBlocking()
{
  global $CombatChain, $defPlayer;
  $num = 0;
  for ($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, cardNumber: true);
    if ($chainCard->PlayerID() == $defPlayer) {
      $type = CardType($chainCard->ID());
      if ($type != "E" && $type != "I" && $type != "C") ++$num;
      if (DelimStringContains(CardSubType($chainCard->ID()), "Evo")) --$num;
    }
  }
  return $num;
}

function NumAttacksBlocking()
{
  global $CombatChain, $defPlayer;
  $num = 0;
  for ($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, cardNumber: true);
    if ($chainCard->PlayerID() == $defPlayer && CardType($chainCard->ID()) == "AA") ++$num;
  }
  return $num;
}

function NumActionsBlocking()
{
  global $CombatChain, $defPlayer;
  $num = 0;
  for ($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, cardNumber: true);
    if ($chainCard->PlayerID() == $defPlayer) {
      $type = CardType($chainCard->ID());
      if (DelimStringContains($type, "A") || $type == "AA") ++$num;
      if (DelimStringContains($type, "E")) {
        if (SubtypeContains($chainCard->ID(), "Evo" && $chainCard->ID() != "teklovossen_the_mechropotentb" && $chainCard->ID() != "nitro_mechanoidb")) {
          if (CardType(GetCardIDBeforeTransform($chainCard->ID())) == "A") ++$num;
        }
      }
    }
  }
  return $num;
}

function GetCardIDBeforeTransform($cardID)
{
  $splitCard = explode("_", $cardID);
  return implode("_", array_slice($splitCard, 0, count($splitCard) - 1));;
}

function PlayerHasLessHealth($player)
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  return GetHealth($player) < GetHealth($otherPlayer);
}

function GetIndices($count, $add = 0, $pieces = 1)
{
  $indices = "";
  for ($i = 0; $i < $count; $i += $pieces) {
    if ($indices != "") $indices .= ",";
    $indices .= ($i + $add);
  }
  return $indices;
}

function RollDie($player, $fromDQ = false, $subsequent = false, $reroll = false)
{
  global $CS_DieRoll;
  $otherPlayer = ($player == 1 ? 2 : 1);
  $numRolls = 1 + CountCurrentTurnEffects("ready_to_roll_blue", $player);
  $highRoll = 0;
  for ($i = 0; $i < $numRolls; ++$i) {
    $roll = GetRandom(1, 6);
    WriteLog("🎲<b>" . $roll . "</b> was rolled.");
    if ($roll > $highRoll) $highRoll = $roll;
  }
  AddEvent("ROLL", $highRoll);
  SetClassState($player, $CS_DieRoll, $highRoll);
  $playerHasGamblersGloves = HasGamblersGloves($player);
  $otherPlayerHasGamblersGloves = HasGamblersGloves($otherPlayer);
  if (($playerHasGamblersGloves || $otherPlayerHasGamblersGloves) && !$reroll) {
    if ($fromDQ && !$subsequent) PrependDecisionQueue("AFTERDIEROLL", $player, "-");
    if ($playerHasGamblersGloves) GamblersGlovesReroll($player, $player); // reroll your own
    if ($otherPlayerHasGamblersGloves) GamblersGlovesReroll($otherPlayer, $player); //reroll ops
    if (!$fromDQ && !$subsequent) AddDecisionQueue("AFTERDIEROLL", $player, "-");
  } else {
    AfterDieRoll($player);
  }
}

function AfterDieRoll($player)
{
  global $CS_DieRoll, $CS_HighestRoll;
  $roll = GetClassState($player, $CS_DieRoll);
  $skullCrusherIndex = FindCharacterIndex($player, "skull_crushers");
  if ($skullCrusherIndex > -1 && IsCharacterAbilityActive($player, $skullCrusherIndex)) {
    if ($roll == 1) {
      WriteLog("Skull Crushers was destroyed");
      DestroyCharacter($player, $skullCrusherIndex);
    }
    if ($roll == 5 || $roll == 6) {
      WriteLog("Skull Crushers gives +1 this turn");
      AddCurrentTurnEffect("skull_crushers", $player);
    }
  }
  if ($roll > GetClassState($player, $CS_HighestRoll)) SetClassState($player, $CS_HighestRoll, $roll);
}

function HasGamblersGloves($player)
{
  $gamblersGlovesIndex = FindCharacterIndex($player, "gamblers_gloves");
  return $gamblersGlovesIndex != -1 && IsCharacterAbilityActive($player, $gamblersGlovesIndex);
}

function IsCharacterAbilityActive($player, $index, $checkGem = false)
{
  $character = &GetPlayerCharacter($player);
  if ($checkGem && $character[$index + 9] == 0) return false;
  if ($character[$index + 12] == "DOWN") return false;
  return $character[$index + 1] == 2;
}

function GetDieRoll($player)
{
  global $CS_DieRoll;
  return GetClassState($player, $CS_DieRoll);
}

function ClearDieRoll($player)
{
  global $CS_DieRoll;
  return SetClassState($player, $CS_DieRoll, 0);
}


function GetBanishModifier($index)
{
  global $currentPlayer;
    $banish = GetBanish($currentPlayer);
    if ($index < count($banish)) {
      return explode("-", $banish[$index + 1])[0];
    }
  return "";
}

function CanPlayAsInstant($cardID, $index = -1, $from = "")
{
  global $currentPlayer, $CS_NextWizardNAAInstant, $CS_NextNAAInstant, $CS_CharacterIndex, $CS_ArcaneDamageTaken, $CS_NumWizardNonAttack;
  global $mainPlayer, $CS_PlayedAsInstant, $CS_HealthLost, $CS_NumAddedToSoul, $layers;
  global $combatChainState, $CCS_EclecticMag;
  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  $cardType = CardType($cardID);
  $subtype = CardSubType($cardID);
  $otherCharacter = &GetPlayerCharacter($otherPlayer);
  if (CardNameContains($cardID, "Lumina Ascension", $currentPlayer) && SearchItemsForCard("spirit_of_eirina_yellow", $currentPlayer) != "") return true;
  if (DelimStringContains($cardType, "A") && GetClassState($currentPlayer, $CS_NextWizardNAAInstant) && ClassContains($cardID, "WIZARD", $currentPlayer)) return true;
  if (GetClassState($currentPlayer, $CS_NumWizardNonAttack) && ($cardID == "snapback_red" || $cardID == "snapback_yellow" || $cardID == "snapback_blue")) return true;
  if ($currentPlayer != $mainPlayer && ($cardID == "cindering_foresight_red" || $cardID == "cindering_foresight_yellow" || $cardID == "cindering_foresight_blue")) return true;
  if (DelimStringContains($cardType, "A") && GetClassState($currentPlayer, $CS_NextNAAInstant)) return true;
  if (DelimStringContains($cardType, "A") && $currentPlayer == $mainPlayer && $combatChainState[$CCS_EclecticMag]) return true;
  if ($cardType == "C" || $cardType == "E" || $cardType == "W") {
    if ($index == -1) $index = GetClassState($currentPlayer, $CS_CharacterIndex);
    if (SearchCharacterEffects($currentPlayer, $index, "INSTANT")) return true;
  }
  if ($from == "BANISH") {
    $banish = GetBanish($currentPlayer);
    if ($index > -1 && $index < count($banish)) {
      if ($banish[$index + 1] !== null) {
        $mod = explode("-", $banish[$index + 1])[0];
        if ((DelimStringContains($cardType, "I") && ($mod == "TCL" || $mod == "TT" || $mod == "TCC" || $mod == "NT" || $mod == "spew_shadow_red" || $mod == "shadowrealm_horror_red")) || $mod == "INST" || $mod == "sonic_boom_yellow" || $mod == "blossoming_spellblade_red") return true;
      }
    }
  }
  if (GetClassState($currentPlayer, $CS_PlayedAsInstant) == "1") return true;
  if ($cardID == "rejuvenate_red" || $cardID == "rejuvenate_yellow" || $cardID == "rejuvenate_blue") {
    return PlayerHasFused($currentPlayer);
  } else if ($cardID == "blessing_of_salvation_red" || $cardID == "blessing_of_salvation_yellow" || $cardID == "blessing_of_salvation_blue") {
    return GetClassState($currentPlayer, $CS_NumAddedToSoul);
  } else if ($cardID == "cleansing_light_red" || $cardID == "cleansing_light_yellow" || $cardID == "cleansing_light_blue") {
    return GetClassState($currentPlayer, $CS_NumAddedToSoul);
  } else if ($cardID == "rattle_bones_red") {
    return GetClassState($otherPlayer, $CS_ArcaneDamageTaken) > 0;
  } else if ($cardID == "funeral_moon_red") return GetClassState($currentPlayer, $CS_HealthLost) > 0 || GetClassState($otherPlayer, $CS_HealthLost) > 0;
  else if ($cardID == "requiem_for_the_damned_red") return GetClassState($currentPlayer, $CS_HealthLost) > 0 || GetClassState($otherPlayer, $CS_HealthLost) > 0;
  else if ($cardID == "cull_red") return GetClassState($currentPlayer, $CS_HealthLost) > 0 || GetClassState($otherPlayer, $CS_HealthLost) > 0;
  if (SearchCurrentTurnEffects("meridian_pathway", $currentPlayer) && SubtypeContains($cardID, "Aura", $currentPlayer) && $from != "PLAY") return true;
  if (SubtypeContains($cardID, "Evo")) {
    if (SearchCurrentTurnEffects("teklovossen_esteemed_magnate", $currentPlayer) || SearchCurrentTurnEffects("teklovossen", $currentPlayer)) return true;
    if (SearchCurrentTurnEffects("scrap_compactor_red", $currentPlayer) || SearchCurrentTurnEffects("scrap_compactor_yellow", $currentPlayer) || SearchCurrentTurnEffects("scrap_compactor_blue", $currentPlayer)) return true;
  }
  if ($from == "ARS" && DelimStringContains($cardType, "A") && $currentPlayer != $mainPlayer && ColorContains($cardID, 3, $currentPlayer) && (SearchCharacterActive($currentPlayer, "iyslander") || SearchCharacterActive($currentPlayer, "iyslander_stormbind") || SearchCharacterActive($currentPlayer, "iyslander") || (SearchCharacterActive($currentPlayer, "shiyana_diamond_gemini") && SearchCurrentTurnEffects($otherCharacter[0] . "-SHIYANA", $currentPlayer) && IsIyslander($otherCharacter[0])))) return true;
  if ($from == "ARS" && DelimStringContains($cardType, "A") && $currentPlayer != $mainPlayer ) {
    $crArsenal = &GetArsenal($currentPlayer);
    for ($i = 0; $i < count($crArsenal); $i += ArsenalPieces()) {
      if (SearchCurrentTurnEffects("chain_reaction_yellow" . "-" . $crArsenal[$i + 5], $currentPlayer)) return true;
    }
    for ($i = 0; $i < count($layers); $i += LayerPieces()) {
      if (SearchCurrentTurnEffects("chain_reaction_yellow" . "-" . $layers[$i + 5], $currentPlayer)) return true;
    }
  }
  if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && DelimStringContains($subtype, "Aura") && SearchCurrentTurnEffects("vengeful_apparition_red-INST", $currentPlayer) && CardCost($cardID, $from) <= 2) return true;
  if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && DelimStringContains($subtype, "Aura") && SearchCurrentTurnEffects("vengeful_apparition_yellow-INST", $currentPlayer) && CardCost($cardID, $from) <= 1) return true;
  if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && DelimStringContains($subtype, "Aura") && SearchCurrentTurnEffects("vengeful_apparition_blue-INST", $currentPlayer) && CardCost($cardID, $from) <= 0) return true;
  if (DelimStringContains($subtype, "Aura") && SearchCurrentTurnEffects("fluttersteps", $currentPlayer)) return true;
  $isStaticType = IsStaticType($cardType, $from, $cardID);
  $abilityType = "-";
  if ($isStaticType) $abilityType = GetAbilityType($cardID, $index, $from);
  if (($cardType == "AR" || ($abilityType == "AR" && $isStaticType)) && IsReactionPhase() && $currentPlayer == $mainPlayer) return true;
  if (($cardType == "DR" || ($abilityType == "DR" && $isStaticType)) && IsReactionPhase() && $currentPlayer != $mainPlayer && IsDefenseReactionPlayable($cardID, $from)) return true;
  if ($from == "DECK" && (SearchCharacterActive($currentPlayer, "dash_io") || SearchCharacterActive($currentPlayer, "dash_database"))) return true;
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
    case "ripple_away_blue":
    case "fruits_of_the_forest_red":
    case "fruits_of_the_forest_yellow":
    case "fruits_of_the_forest_blue":
    case "trip_the_light_fantastic_red":
    case "trip_the_light_fantastic_yellow":
    case "trip_the_light_fantastic_blue":
    case "haunting_rendition_red":
    case "mental_block_blue":
    case "chorus_of_the_amphitheater_red":
    case "chorus_of_the_amphitheater_yellow":
    case "chorus_of_the_amphitheater_blue":
    case "arcane_twining_red":
    case "arcane_twining_yellow":
    case "arcane_twining_blue":
    case "photon_splicing_red":
    case "photon_splicing_yellow":
    case "photon_splicing_blue":
    case "under_the_trapdoor_blue":
    case "reapers_call_red":
    case "reapers_call_yellow":
    case "reapers_call_blue":
    case "shelter_from_the_storm_red":
    case "tipoff_red":
    case "tipoff_yellow":
    case "tipoff_blue":
    case "war_cry_of_themis_yellow":
    case "war_cry_of_bellona_yellow":
      return $from == "HAND";
    case "astral_etchings_red":
    case "astral_etchings_yellow":
    case "astral_etchings_blue":
      return SearchAuras("spectral_shield", $currentPlayer);
    case "succumb_to_temptation_yellow":
      return GetClassState($otherPlayer, $CS_ArcaneDamageTaken) > 0;
    default:
      break;
  }
  return false;
}

function ClassOverride($cardID, $player)
{
  global $currentTurnEffects;
  $cardClass = "";
  $otherPlayer = ($player == 1 ? 2 : 1);
  $otherCharacter = &GetPlayerCharacter($otherPlayer);
  $mainCharacter = &GetPlayerCharacter($player);

  // With the rules as of today it's correct. HVY Release Notes Disclaimer. CR2.6 - 6.3.6. Continuous effects that remove a property, or part of a property, from an object do not removeproperties, or parts of properties, that were added by another effect.
  if (HasUniversal($cardID)) { //Universal
    $cardClass = CardClass($mainCharacter[0]);
  }
  if (SearchCurrentTurnEffects("phantasmal_symbiosis_yellow-" . GamestateSanitize(CardName($cardID)), $player)) { //Phantasmal Symbiosis
    if ($cardClass != "") $cardClass .= ",";
    $cardClass .= "ILLUSIONIST";
  }
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if (!isset($currentTurnEffects[$i + 1])) continue;
    if ($currentTurnEffects[$i + 1] != $player) continue;
    $classToAdd = "";
    switch ($currentTurnEffects[$i]) {
      case "phantasmify_red":
      case "phantasmify_yellow":
      case "phantasmify_blue":
        $classToAdd = "ILLUSIONIST";
        break; //Phantasmify
      case "veiled_intentions_red":
      case "veiled_intentions_yellow":
      case "veiled_intentions_blue":
        $classToAdd = "ILLUSIONIST";
        break; //Veiled Intentions
      case "transmogrify_red":
      case "transmogrify_yellow":
      case "transmogrify_blue":
        $classToAdd = "ILLUSIONIST";
        break; //Transmogrify
      default:
        break;
    }
    if ($classToAdd != "") {
      if ($cardClass != "") $cardClass .= ",";
      $cardClass .= $classToAdd;
    }
  }
  if (SearchCurrentTurnEffects($otherCharacter[0] . "-SHIYANA", $player)) { //Shiyana
    if ($cardClass != "") $cardClass .= ",";
    $cardClass .= CardClass($otherCharacter[0]) . ",SHAPESHIFTER";
  }
  if (!SearchCurrentTurnEffects("erase_face_red", $player)) { //Erase Face
    if ($cardClass != "") $cardClass .= ",";
    $cardClass .= CardClass($cardID);
  }
  if ($cardClass == "") return "NONE";
  return $cardClass;
}

function NameOverride($cardID, $player = "")
{
  $name = CardName($cardID);
  if (SearchCurrentTurnEffects("amnesia_red", $player)) $name = "";
  return $name;
}

function ColorOverride($cardID, $player = "")
{
  $pitch = PitchValue($cardID);
  if (SearchCurrentTurnEffects("blanch_red", $player)) $pitch = 0;
  if (SearchCurrentTurnEffects("blanch_yellow", $player)) $pitch = 0;
  if (SearchCurrentTurnEffects("blanch_blue", $player)) $pitch = 0;
  return $pitch;
}

function ClassContains($cardID, $class, $player)
{
  $cardClass = ClassOverride($cardID, $player);
  return DelimStringContains($cardClass, $class);
}

function ColorContains($cardID, $color, $player)
{
  $cardColor = ColorOverride($cardID, $player);
  return DelimStringContains($cardColor, $color);
}

function TypeContains($cardID, $type, $player = "", $partial = false)
{
  $cardType = CardType($cardID);
  return DelimStringContains($cardType, $type, $partial);
}

function SubtypeContains($cardID, $subtype, $player = "", $uniqueID = "")
{
  global $currentTurnEffects;
  $cardSubtype = CardSubtype($cardID);
  if ($cardID == "adaptive_plating") {
    for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
      $effect = explode(",", $currentTurnEffects[$i]);
      if ($effect[0] == "adaptive_plating-" . $uniqueID) return $effect[1];
    }
  }
  if ($cardID == "adaptive_dissolver") {
    if($subtype == "Base") return true;
    for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
      $effect = explode(",", $currentTurnEffects[$i]);
      if ($effect[0] == "adaptive_dissolver-" . $uniqueID) return DelimStringContains($currentTurnEffects[$i], $subtype, true);
    }
  }
  return DelimStringContains($cardSubtype, $subtype);
}

function CardNameContains($cardID, $name, $player = "", $partial = false) // This isn't actually a contains operation. It's an equals unless you turn partial to true.
{
  $cardName = NameOverride($cardID, $player);
  if ($partial) {
    $cardName = explode(" ", $cardName);
    $cardName = implode(",", $cardName);
  }
  if ($cardName == $name) return true; //Card is breaking due to comma
  return DelimStringContains($cardName, $name, $partial);
}

function TalentOverride($cardID, $player = "", $zone="-")
{
  global $currentTurnEffects;
  $cardTalent = "";
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    $talentToAdd = "";
    if (!isset($currentTurnEffects[$i + 1])) continue;
    if ($currentTurnEffects[$i + 1] != $player) continue;
    switch ($currentTurnEffects[$i]) {
      case "brand_with_cinderclaw_red":
      case "brand_with_cinderclaw_yellow":
      case "brand_with_cinderclaw_blue":
        if (TypeContains($cardID, "AA") || TypeContains($cardID, "W") || SubtypeContains($cardID, "Ally")) {
          $talentToAdd = "DRACONIC"; //Brand of Cinderclaw
        }
        break;
      case "blessing_of_vynserakai_red":
        $talentToAdd = "DRACONIC";
        break;
      case "fealty": //Fealty
        if (!TypeContains($cardID, "W") && !TypeContains($cardID, "AA")) { // We'll need to add cases for Allies and Emperor Attacking
          $talentToAdd = "DRACONIC";
        }
        break;
      case "fealty-ATTACK":
        if (!TypeContains($cardID, "W") && TypeContains($cardID, "AA")) {
          $talentToAdd = "DRACONIC";
        }
        break;
      default:
        break;
    }
    if ($talentToAdd != "") {
      if ($cardTalent == "NONE") $cardTalent = "";
      if ($cardTalent != "") $cardTalent .= ",";
      $cardTalent .= $talentToAdd;
    }
  }
  if (!SearchCurrentTurnEffects("erase_face_red", $player)) { //Erase Face
    if ($cardTalent != "") $cardTalent .= ",";
    $cardTalent .= CardTalent($cardID, $zone);
  }
  if ($cardTalent == "") return "NONE";
  return $cardTalent;
}

function TalentContains($cardID, $talent, $player = "")
{
  $cardTalent = TalentOverride($cardID, $player);
  return DelimStringContains($cardTalent, $talent);
}

//talents = comma delimited list of talents to check
function TalentContainsAny($cardID, $talents, $player = "", $zone="-")
{
  $cardTalent = TalentOverride($cardID, $player, $zone);
  //Loop over current turn effects to find modifiers
  $talentArr = explode(",", $talents);
  for ($i = 0; $i < count($talentArr); ++$i) {
    if (DelimStringContains($cardTalent, $talentArr[$i])) return true;
  }
  return false;
}

function RevealCards($cards, $player = "")
{
  global $currentPlayer;
  if ($player == "") $player = $currentPlayer;
  if (!CanRevealCards($player)) return false;
  if ($cards == "") return true;
  $cardArray = explode(",", $cards);
  $string = "";
  for ($i = 0; $i < count($cardArray); ++$i) {
    if ($string != "") $string .= ", ";
    $string .= CardLink($cardArray[$i], $cardArray[$i]);
    AddEvent("REVEAL", $cardArray[$i]);
  }
  $string .= (count($cardArray) == 1 ? " is" : " are");
  $string .= " revealed";
  WriteLog($string);
  if ($player != "" && SearchLandmark("korshem_crossroad_of_elements")) {
    KorshemRevealAbility($player);
  }
  return true;
}

function DoesAttackHaveGoAgain()
{
  global $CombatChain, $combatChainState, $CCS_CurrentAttackGainedGoAgain, $mainPlayer, $defPlayer, $CS_Num6PowDisc;
  global $CS_NumAuras, $CS_ArcaneDamageTaken, $CS_AnotherWeaponGainedGoAgain, $CS_NumRedPlayed, $CS_NumNonAttackCards;
  global $CS_NumItemsDestroyed, $CCS_WeaponIndex, $CS_NumCharged, $CS_NumCardsDrawn, $CS_Transcended;
  global $CS_NumLightningPlayed, $CCS_NumInstantsPlayedByAttackingPlayer, $CS_ActionsPlayed, $CS_FealtyCreated;
  global $chainLinks, $chainLinkSummary, $CCS_FlickedDamage, $defPlayer, $CS_NumStealthAttacks;
  $attackID = $CombatChain->AttackCard()->ID();
  $attackType = CardType($attackID);
  $attackSubtype = CardSubType($attackID);
  $isAura = DelimStringContains(CardSubtype($attackID), "Aura");

  //Prevention Natural Go Again
  if (CurrentEffectPreventsGoAgain($attackID, "CC")) return false;
  if (SearchCurrentTurnEffects("blizzard_blue", $mainPlayer)) return false;

  //Natural Go Again
  if (!$isAura && HasGoAgain($attackID)) return true;

  //Prevention Grant Go Again
  if (SearchAuras("hypothermia_blue", $mainPlayer)) return false;

  //Grant go Again
  $auras = &GetAuras($mainPlayer);
  $actionsPlayed = explode(",", GetClassState($mainPlayer, $CS_ActionsPlayed));
  $numActions = count($actionsPlayed);
  if (ClassContains($attackID, "ILLUSIONIST", $mainPlayer)) {
    if (SearchCharacterForCard($mainPlayer, "luminaris") && SearchPitchForColor($mainPlayer, 2) > 0) return true;
    if ($isAura && SearchCharacterForCard($mainPlayer, "iris_of_reality")) return true;
  }
  if ($isAura && SearchCharacterForCard($mainPlayer, "cosmo_scroll_of_ancestral_tapestry") && isset($auras[$combatChainState[$CCS_WeaponIndex] + 3]) ? $auras[$combatChainState[$CCS_WeaponIndex] + 3] > 0 : false) return true;
  if ($combatChainState[$CCS_CurrentAttackGainedGoAgain] == 1 || CurrentEffectGrantsGoAgain() || MainCharacterGrantsGoAgain()) {
    $combatChainState[$CCS_CurrentAttackGainedGoAgain] = 1;
    return true;
  }
  if ($attackType == "AA" && ClassContains($attackID, "ILLUSIONIST", $mainPlayer) && SearchAuras("ode_to_wrath_yellow", $mainPlayer)) return true;
  if (DelimStringContains($attackSubtype, "Dragon") && GetClassState($mainPlayer, $CS_NumRedPlayed) > 0 && (SearchCharacterActive($mainPlayer, "dromai_ash_artist") || SearchCharacterActive($mainPlayer, "dromai") || SearchCurrentTurnEffects("dromai_ash_artist-SHIYANA", $mainPlayer) || SearchCurrentTurnEffects("dromai-SHIYANA", $mainPlayer))) return true;
  if (SearchItemsForCard("mhz_script_yellow", $mainPlayer) != "" && $attackType == "AA" && ClassContains($CombatChain->AttackCard()->ID(), "MECHANOLOGIST", $mainPlayer)) return true;
  if (SearchCurrentTurnEffectsForCycle("engaged_swiftblade_red", "engaged_swiftblade_yellow", "engaged_swiftblade_blue", $mainPlayer) && ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer) && NumAttacksBlocking() > 0) return true;
  if (SearchCurrentTurnEffects("first_tenet_of_chi_wind_blue", $mainPlayer) && PitchValue($CombatChain->AttackCard()->ID()) == 3 && $CombatChain->AttackCard()->From() != "PLAY") return true;
  if ((SearchCurrentTurnEffects("arc_lightning_yellow-GOAGAIN", $mainPlayer) || SearchCurrentTurnEffects("current_funnel_blue", $mainPlayer)) && $CombatChain->AttackCard()->From() != "PLAY" && $attackType == "AA") return true;
  if (IsWeaponGreaterThanTwiceBasePower() && SearchAuras("sharpened_senses_yellow", $mainPlayer) && IsWeaponAttack()) return true;
  //the last action in numActions is going to be the current chain link
  //so we want the second to last to be current funnel, and 3rd to last to be lightning
  $character = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] != 2) continue;
    $characterID = ShiyanaCharacter($character[$i]);
    switch ($characterID) {
      case "arakni_solitary_confinement": case "arakni_5lp3d_7hru_7h3_cr4x": case "arakni_5lp3d_7hru_7h3_cr4x":
        if (HasStealth($attackID) && GetClassState($mainPlayer, piece: $CS_NumStealthAttacks) == 1) {
          return true;
        }
      default:
        break;
    }
  }
  $otherPlayerCharacter = &GetPlayerCharacter($defPlayer);
  for ($j = 0; $j < count($otherPlayerCharacter); $j += CharacterPieces()) {
    if ($otherPlayerCharacter[$j + 1] != 2) continue;
    switch ($otherPlayerCharacter[$j]) {
      case "arakni_5lp3d_7hru_7h3_cr4x": case "arakni_5lp3d_7hru_7h3_cr4x":
        if (HasStealth($attackID) && GetClassState($mainPlayer, $CS_NumStealthAttacks) == 1) {
          return true;
        }
      default:
        break;
    }
  }
  $mainPitch = &GetPitch($mainPlayer);
  switch ($attackID) {
    case "harmonized_kodachi":
      return SearchCount(SearchPitch($mainPlayer, minCost: 0, maxCost: 0)) > 0;
    case "mugenshi_release_yellow":
    case "hurricane_technique_yellow":
      return ComboActive($attackID);
    case "open_the_center_red":
    case "open_the_center_yellow":
    case "open_the_center_blue":
      return ComboActive($attackID);
    case "rising_knee_thrust_red":
    case "rising_knee_thrust_yellow":
    case "rising_knee_thrust_blue":
      return ComboActive($attackID);
    case "whelming_gustwave_red":
    case "whelming_gustwave_yellow":
    case "whelming_gustwave_blue":
      return ComboActive($attackID);
    case "last_ditch_effort_blue":
      $deck = new Deck($mainPlayer);
      return $deck->Empty();
    case "vigor_rush_red":
    case "vigor_rush_yellow":
    case "vigor_rush_blue":
      return GetClassState($mainPlayer, $CS_NumNonAttackCards) > 0;
    case "mandible_claw":
    case "mandible_claw":
      return GetClassState($mainPlayer, $CS_Num6PowDisc) > 0;
    case "barraging_big_horn_red":
    case "barraging_big_horn_yellow":
    case "barraging_big_horn_blue":
      if (NumCardsNonEquipBlocking() < 2) return true;
    case "crane_dance_red":
    case "crane_dance_yellow":
    case "crane_dance_blue":
      return ComboActive($attackID);
    case "rushing_river_red":
    case "rushing_river_yellow":
    case "rushing_river_blue":
      return ComboActive($attackID);
    case "meat_and_greet_red":
    case "meat_and_greet_yellow":
    case "meat_and_greet_blue":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) > 0;
    case "battlefield_blitz_red":
    case "battlefield_blitz_yellow":
    case "battlefield_blitz_blue":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0;
    case "take_flight_red":
    case "take_flight_yellow":
    case "take_flight_blue":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0;
    case "rip_through_reality_red":
    case "rip_through_reality_yellow":
    case "rip_through_reality_blue":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) > 0;
    case "soul_reaping_red":
    case "ursur_the_soul_reaper":
      return (count(GetSoul($defPlayer)) > 0 && !IsAllyAttackTarget());
    case "pulping_red":
    case "pulping_yellow":
    case "pulping_blue":
      return NumCardsNonEquipBlocking() < 2;
    case "out_muscle_red":
    case "out_muscle_yellow":
    case "out_muscle_blue":
      return SearchHighestAttackDefended() < CachedTotalAttack();
    case "zealous_belting_red":
    case "zealous_belting_yellow":
    case "zealous_belting_blue":
      return SearchPitchHighestAttack($mainPitch) > AttackValue($attackID);
    case "boltn_shot_red":
    case "boltn_shot_yellow":
    case "boltn_shot_blue":
      return CachedTotalAttack() > AttackValue($attackID);
    case "swarming_gloomveil_red":
      return GetClassState($mainPlayer, $CS_NumAuras) > 0;
    case "fractal_replication_red":
      return FractalReplicationStats("GoAgain");
    case "searing_emberblade":
      return NumDraconicChainLinks() >= 2;
    case "cinderskin_devotion_red":
    case "cinderskin_devotion_yellow":
    case "cinderskin_devotion_blue":
      return NumDraconicChainLinks() >= 2;
    case "lava_vein_loyalty_red":
    case "lava_vein_loyalty_yellow":
    case "lava_vein_loyalty_blue":
      return NumDraconicChainLinks() >= 2;
    case "phoenix_form_red":
      return NumChainLinksWithName("Phoenix Flame") >= 1;
    case "blaze_headlong_red":
      return GetClassState($mainPlayer, $CS_NumRedPlayed) > 1;
    case "tiger_swipe_red":
      return ComboActive($attackID);
    case "pouncing_qi_red":
    case "pouncing_qi_yellow":
    case "pouncing_qi_blue":
      return (ComboActive($attackID));
    case "quicksilver_dagger":
    case "quicksilver_dagger":
      return GetClassState($mainPlayer, $CS_AnotherWeaponGainedGoAgain) != "-";
    case "teklo_leveler":
      return EvoUpgradeAmount($mainPlayer) >= 3;
    case "soup_up_red":
    case "soup_up_yellow":
    case "soup_up_blue":
      return GetClassState($mainPlayer, $CS_NumItemsDestroyed) > 0;
    case "hot_streak":
      $character = &GetPlayerCharacter($mainPlayer);
      return SearchCurrentTurnEffectsForUniqueID($character[$combatChainState[$CCS_WeaponIndex] + 11]) != -1 && SearchCurrentTurnEffects($attackID, $mainPlayer);
    case "cintari_sellsword":
      return true;
    case "second_tenet_of_chi_wind_blue":
      return GetClassState($mainPlayer, $CS_Transcended) > 0;
    case "chase_the_tail_red":
      return ComboActive($attackID);
    case "aspect_of_tiger_body_red":
    case "aspect_of_tiger_soul_yellow":
    case "aspect_of_tiger_mind_blue":
      return ComboActive($attackID);
    case "rising_speed_red":
    case "rising_speed_yellow":
    case "rising_speed_blue":
      return GetClassState($mainPlayer, $CS_NumCardsDrawn) > 0;
    case "breed_anger_red":
    case "breed_anger_yellow":
    case "breed_anger_blue":
      return ComboActive($attackID);
    case "photon_rush_red":
    case "photon_rush_blue":
    case "star_fall":
      return GetClassState($mainPlayer, $CS_NumLightningPlayed) > 0;
    case "current_funnel_blue":
      //the last action in numActions is going to be the current chain link
      //so we want the second to last
      return count($actionsPlayed) > 1 && TalentContains($actionsPlayed[$numActions-2], "LIGHTNING", $mainPlayer);
    case "flittering_charge_red":
    case "flittering_charge_yellow":
    case "flittering_charge_blue":
      if (isset($combatChainState[$CCS_NumInstantsPlayedByAttackingPlayer])) { // the first time this is checked in a chain it isn't set but the rest of the time it can be checked.
        return $combatChainState[$CCS_NumInstantsPlayedByAttackingPlayer] > 0;
      } else return false;
    case "runerager_swarm_red":
    case "runerager_swarm_yellow":
    case "runerager_swarm_blue":
      return GetClassState($mainPlayer, $CS_NumAuras) > 0;
    case "gustwave_of_the_second_wind_red":
      return ComboActive($attackID);
    case "burning_blade_dance_red":
      return NumDraconicChainLinks() > 1;
    case "hot_on_their_heels_red":
    case "mark_with_magma_red":
      return NumDraconicChainLinks() >= 2;
    case "art_of_the_dragon_blood_red":
      $attackUniqueID = $CombatChain->AttackCard()->UniqueID();
      return SearchCurrentTurnEffects("art_of_the_dragon_blood_red-$attackUniqueID", $mainPlayer);
    case "grow_wings_red":
    case "grow_wings_yellow":
    case "grow_wings_blue":
      return isPreviousLinkDraconic();
    case "march_of_loyalty_red":
      return GetClassState($mainPlayer, $CS_FealtyCreated) > 0;
    case "cut_through_red":
    case "cut_through_yellow":
    case "cut_through_blue":
      $numDaggerHits = 0;
        for($i=0; $i<count($chainLinks); ++$i)
        {
          if(CardSubType($chainLinks[$i][0]) == "Dagger" && $chainLinkSummary[$i*ChainLinkSummaryPieces()] > 0) ++$numDaggerHits;
        }
        $numDaggerHits += $combatChainState[$CCS_FlickedDamage];
      return $numDaggerHits > 0;
    case "retrace_the_past_blue":
      return SearchCurrentTurnEffectsForIndex("retrace_the_past_blue", $mainPlayer) != -1;
    default:
      return false;
  }
}

function DestroyCurrentWeapon()
{
  global $combatChainState, $CCS_WeaponIndex, $mainPlayer;
  $index = $combatChainState[$CCS_WeaponIndex];
  $char = &GetPlayerCharacter($mainPlayer);
  $char[$index + 7] = "1";
}

function AttackDestroyed($attackID)
{
  global $mainPlayer, $combatChainState, $CCS_GoesWhereAfterLinkResolves;
  switch ($attackID) {
    case "miraging_metamorph_red":
      MirragingMetamorphDestroyed();
      break;
    case "coalescence_mirage_red":
    case "coalescence_mirage_yellow":
    case "coalescence_mirage_blue":
      CoalescentMirageDestroyed();
      break;
    case "phantasmal_haze_red":
    case "phantasmal_haze_yellow":
    case "phantasmal_haze_blue":
      PlayAura("spectral_shield", $mainPlayer);
      break;
    case "dunebreaker_cenipai_red":
    case "dunebreaker_cenipai_yellow":
    case "dunebreaker_cenipai_blue":
      PutPermanentIntoPlay($mainPlayer, "ash");
      break;
    case "embermaw_cenipai_red":
    case "embermaw_cenipai_yellow":
    case "embermaw_cenipai_blue":
      PutPermanentIntoPlay($mainPlayer, "ash");
      break;
    default:
      break;
  }
  AttackDestroyedEffects($attackID);
  CharacterAttackDestroyedAbilities($attackID);
  $numMercifulRetribution = SearchCount(SearchAurasForCard("merciful_retribution_yellow", $mainPlayer));
  if ($numMercifulRetribution > 0 && TalentContains($attackID, "LIGHT", $mainPlayer)) {
    AddDecisionQueue("PASSPARAMETER", $mainPlayer, $attackID);
    AddDecisionQueue("ADDSOUL", $mainPlayer, "CC");
    $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-";
  }
  for ($i = 0; $i < $numMercifulRetribution; ++$i) {
    AddDecisionQueue("ADDTRIGGER", $mainPlayer, "merciful_retribution_yellow," . $attackID);
  }
}

function AttackDestroyedEffects($attackID)
{
  global $currentTurnEffects, $mainPlayer;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    switch ($currentTurnEffects[$i]) {
      case "veiled_intentions_red":
      case "veiled_intentions_yellow":
      case "veiled_intentions_blue":
        Draw($mainPlayer);
        break;
      default:
        break;
    }
  }
}

function CloseCombatChain($chainClosed = "true")
{
  global $turn, $currentPlayer, $mainPlayer, $combatChainState, $CCS_AttackTarget, $layers;


  if (count($layers) <= LayerPieces() && isset($layers[0]) && isPriorityStep($layers[0])) $layers = [];//In case there's another combat chain related layer like defense step
  elseif (in_array("DEFENDSTEP", $layers)) PopLayer();
  if(!$chainClosed) FinalizeChainLink(!$chainClosed);
  elseif (!in_array("FINALIZECHAINLINK", $layers)) PrependLayer("FINALIZECHAINLINK", $mainPlayer, $chainClosed);
  $turn[0] = "M";
  $currentPlayer = $mainPlayer;
  $combatChainState[$CCS_AttackTarget] = "NA";
}

function UndestroyCharacter($player, $index)
{
  $char = &GetPlayerCharacter($player);
  $char[$index + 1] = 2;
  $char[$index + 4] = 0;
}

function DestroyCharacter($player, $index, $skipDestroy = false, $wasBanished = false)
{
  if ($index == -1) return "";
  global $CombatChain;
  $char = &GetPlayerCharacter($player);
  $linkedEffect = $char[$index] . "-" . $char[$index + 11];
  SearchCurrentTurnEffects($linkedEffect, $player, true);
  $char[$index + 1] = 0;
  $char[$index + 2] = 0;
  $char[$index + 3] = 0;
  $char[$index + 4] = 0;
  $char[$index + 7] = "0"; //avoid it getting double destroyed
  $cardID = $char[$index];
  if ($char[$index + 6] == 1) $CombatChain->Remove(GetCombatChainIndex($cardID, $player));
  $char[$index + 6] = 0;
  if (!isSubcardEmpty($char, $index)) {
    $subcards = explode(',', $char[$index + 10]);
    $subcardsCount = count($subcards);
    for ($i = 0; $i < $subcardsCount; $i++) AddGraveyard($subcards[$i], $player, "CHAR");
  }
  $char[$index + 10] = "-";
  if (!$skipDestroy) {
    if (HasWard($cardID, $player)) WardPoppedAbility($player, $char[$index]);
    if (HasWard($cardID, $player) && ClassContains($cardID, "ILLUSIONIST", $player)) PhantomTidemawDestroy($player);
    if (!$wasBanished) AddGraveyard($cardID, $player, "CHAR");
    CharacterDestroyEffect($cardID, $player);
  }
  return $cardID;
}

function RemoveCharacter($player, $index)
{
  if ($index == -1) return "";
  $char = &GetPlayerCharacter($player);
  for ($i = 0; $i < CharacterPieces(); ++$i) {
    unset($char[$index + $i]);
  }
}

function RemoveCharacterAndAddAsSubcardToCharacter($player, $index, &$newCharactersSubcardIndex)
{
  global $CombatChain;
  $char = &GetPlayerCharacter($player);
  $cardID = $char[$index];
  if ($char[0] == "teklovossen_the_mechropotent") AddSoul($cardID, $player, "-");
  if ($char[$index + 6] == 1) $CombatChain->Remove(GetCombatChainIndex($cardID, $player));
  if (!isSubcardEmpty($char, $index)) {
    $subcards = explode(',', $char[$index + 10]);
    $subcardsCount = count($subcards);
    for ($i = 0; $i < $subcardsCount; $i++) {
      if ($char[0] == "teklovossen_the_mechropotent") AddSoul($subcards[$i], $player, "-");
      if (isSubcardEmpty($char, $newCharactersSubcardIndex)) $char[$newCharactersSubcardIndex + 10] = $subcards[$i];
      else $char[$newCharactersSubcardIndex + 10] = $char[$newCharactersSubcardIndex + 10] . "," . $subcards[$i];
    }
  }
  CharacterDestroyEffect($cardID, $player);
  if (isSubcardEmpty($char, $newCharactersSubcardIndex)) $char[$newCharactersSubcardIndex + 10] = $cardID;
  else $char[$newCharactersSubcardIndex + 10] = $char[$newCharactersSubcardIndex + 10] . "," . $cardID;
  $characterPieces = CharacterPieces();
  if ($newCharactersSubcardIndex > $index) $newCharactersSubcardIndex -= $characterPieces;
  for ($i = 0; $i < $characterPieces; $i++) array_splice($char, $index, 1);
  UpdateSubcardCounterCount($player, $newCharactersSubcardIndex);
}

function RemoveItemAndAddAsSubcardToCharacter($player, $itemIndex, $newCharactersSubcardIndex)
{
  $items = &GetItems($player);
  $char = &GetPlayerCharacter($player);
  $itemPieces = ItemPieces();
  $cardID = $items[$itemIndex];
  if (isSubcardEmpty($char, $newCharactersSubcardIndex)) $char[$newCharactersSubcardIndex + 10] = $cardID;
  else $char[$newCharactersSubcardIndex + 10] = $char[$newCharactersSubcardIndex + 10] . "," . $cardID;
  for ($i = 0; $i < $itemPieces; $i++) array_splice($items, $itemIndex, 1);
  if ($char[$newCharactersSubcardIndex] == "nitro_mechanoida") UpdateSubcardCounterCount($player, $newCharactersSubcardIndex);
}

function UpdateSubcardCounterCount($player, $index)
{
  $char = &GetPlayerCharacter($player);

  if (empty($char[$index + 10])) $char[$index + 2] = 0;
  else $char[$index + 2] = count(explode(",", $char[$index + 10]));
}

function RemoveArsenalEffects($player, $cardToReturn, $uniqueID)
{
  if ($uniqueID == SearchCurrentTurnEffects("dreadbore", $player, returnUniqueID: true)) SearchCurrentTurnEffects("dreadbore", $player, true);
  if ($uniqueID == SearchCurrentTurnEffects("bulls_eye_bracers", $player, returnUniqueID: true)) SearchCurrentTurnEffects("bulls_eye_bracers", $player, true);
  if ($cardToReturn == "head_shot_red") {
    SearchCurrentTurnEffects("head_shot_red", $player, true);
  }
  if ($cardToReturn == "head_shot_yellow") {
    SearchCurrentTurnEffects("head_shot_yellow", $player, true);
  }
  if ($cardToReturn == "head_shot_blue") {
    SearchCurrentTurnEffects("head_shot_blue", $player, true);
  }
}

function LookAtHand($player)
{
  $hand = &GetHand($player);
  $cards = "";
  for ($i = 0; $i < count($hand); $i += HandPieces()) {
    if ($cards != "") $cards .= ",";
    $cards .= $hand[$i];
  }
  RevealCards($cards, $player);
}

function LookAtArsenal($player)
{
  $arsenal = &GetArsenal($player);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    if ($arsenal[$i + 1] == "DOWN") {
      RevealCards($arsenal[$i], $player);
    }
  }
}

function GainActionPoints($amount = 1, $player = 0)
{
  global $actionPoints, $mainPlayer, $currentPlayer;
  if ($player == 0) $player = $currentPlayer;
  if ($player == $mainPlayer) $actionPoints += $amount;
}

function AddCharacterUses($player, $index, $numToAdd)
{
  $character = &GetPlayerCharacter($player);
  if ($character[$index + 1] == 0) return;
  $character[$index + 1] = 2;
  $character[$index + 5] += $numToAdd;
}

function HaveUnblockedEquip($player)
{
  $restriction = ""; // This just needs to exist cause IsBlockRestricted uses a reference op on it.
  $char = &GetPlayerCharacter($player);
  for ($i = CharacterPieces(); $i < count($char); $i += CharacterPieces()) {
    if ($char[$i + 1] == 0) continue;//If broken
    if ($char[$i + 6] == 1) continue;//On combat chain
    if ($char[$i + 12] == "DOWN") continue; //Face-down
    if (!TypeContains($char[$i], "E", $player)) continue;
    if (BlockValue($char[$i]) == -1) continue;
    if (IsBlockRestricted($char[$i], $restriction, $player)) continue; //If restricted via something like stasis cell
    return true;
  }
  return false;
}

function NumEquipBlock()
{
  global $combatChain, $defPlayer, $combatChainState, $CCS_RequiredEquipmentBlock;
  $numEquipBlock = 0;
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (DelimStringContains(CardSubType($combatChain[$i]), "Evo") && $combatChain[$i + 1] == $defPlayer && $combatChainState[$CCS_RequiredEquipmentBlock] < 1) ++$numEquipBlock; // Working, but technically wrong until we get CardTypeContains
    else if (TypeContains($combatChain[$i], "E", $defPlayer) && $combatChain[$i + 1] == $defPlayer) ++$numEquipBlock;
  }
  return $numEquipBlock;
}

function HaveUnblockedNegCounterEquip($player)
{
  $char = &GetPlayerCharacter($player);
  for ($i = CharacterPieces(); $i < count($char); $i += CharacterPieces()) {
    if ($char[$i + 1] == 0) continue;//If broken
    if ($char[$i + 4] == 0) continue;//No negative counters
    if ($char[$i + 6] == 1) continue;//On combat chain
    if ($char[$i + 12] == "DOWN") continue; //Face-down
    if (!TypeContains($char[$i], "E", $player)) continue;
    if (BlockValue($char[$i]) == -1) continue;
    return true;
  }
  return false;
}

function NumNegCounterEquipBlock()
{
  global $combatChain, $defPlayer, $combatChainState, $CCS_RequiredNegCounterEquipmentBlock;
  $numNegCounterEquipBlock = 0;
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (DelimStringContains(CardSubType($combatChain[$i]), "Evo") && $combatChain[$i + 1] == $defPlayer && $combatChain[$i + 4] < 0 && $combatChainState[$CCS_RequiredNegCounterEquipmentBlock] < 1) ++$numNegCounterEquipBlock;
    else if (TypeContains($combatChain[$i], "E", $defPlayer) && $combatChain[$i + 1] == $defPlayer && $combatChain[$i + 4] < 0) ++$numNegCounterEquipBlock;
  }
  return $numNegCounterEquipBlock;
}

function CanPassPhase($phase)
{
  global $combatChainState, $CCS_RequiredEquipmentBlock, $currentPlayer, $CCS_RequiredNegCounterEquipmentBlock;
  if ($phase == "B" && HaveUnblockedEquip($currentPlayer) && NumEquipBlock() < $combatChainState[$CCS_RequiredEquipmentBlock]) {
    return false;
  }
  if ($phase == "B" && HaveUnblockedNegCounterEquip($currentPlayer) && NumNegCounterEquipBlock() < $combatChainState[$CCS_RequiredNegCounterEquipmentBlock]) {
    return false;
  }
  switch ($phase) {
    case "P":
    case "PDECK":
    case "CHOOSEDECK":
    case "CHOOSETHEIRDECK":
    case "HANDTOPBOTTOM":
    case "CHOOSECOMBATCHAIN":
    case "CHOOSECHARACTER":
    case "CHOOSEHAND":
    case "CHOOSEHANDCANCEL":
    case "MULTICHOOSEDISCARD":
    case "CHOOSEDISCARDCANCEL":
    case "CHOOSEARCANE":
    case "CHOOSEARSENAL":
    case "CHOOSEDISCARD":
    case "MULTICHOOSEHAND":
    case "CHOOSEMULTIZONE":
    case "CHOOSEBANISH":
    case "MULTICHOOSEBANISH":
    case "BUTTONINPUTNOPASS":
    case "CHOOSEFIRSTPLAYER":
    case "MULTICHOOSEDECK":
    case "CHOOSEPERMANENT":
    case "MULTICHOOSETEXT":
    case "CHOOSEMYSOUL":
    case "CHOOSEMYAURA":
    case "CHOOSECARD":
    case "CHOOSECARDID":
    case "OVER":
    case "BUTTONINPUT":
      return 0;
    default:
      return 1;
  }
}

//Returns true if done for that player
function EndTurnPitchHandling($player)
{
  global $currentPlayer, $turn;
  $pitch = &GetPitch($player);
  if (count($pitch) == 0) return true;
  else if (count($pitch) == 1) {
    PitchDeck($player, 0);
    return true;
  } else {
    $currentPlayer = $player;
    $turn[0] = "PDECK";
    return false;
  }
}

function ResolveGoAgain($cardID, $player, $from="", $additionalCosts="-")
{
  global $CS_NextNAACardGoAgain, $actionPoints, $mainPlayer, $CS_ActionsPlayed, $CS_AdditionalCosts;
  $actionsPlayed = explode(",", GetClassState($player, $CS_ActionsPlayed));
  $cardType = CardType($cardID);
  $goAgainPrevented = CurrentEffectPreventsGoAgain($cardID, $from);
  if (IsStaticType($cardType, $from, $cardID)) {
    $hasGoAgain = AbilityHasGoAgain($cardID);
    if (!$hasGoAgain && GetResolvedAbilityType($cardID, $from) == "A") $hasGoAgain = CurrentEffectGrantsNonAttackActionGoAgain($cardID, $from);
  } else {
    $hasGoAgain = HasMeld($cardID) ? 0 : HasGoAgain($cardID);
    if (GetClassState($player, $CS_NextNAACardGoAgain) && (DelimStringContains($cardType, "A") || $from == "MELD")) {
      $hasGoAgain = true;
      SetClassState($player, $CS_NextNAACardGoAgain, 0);
    }
    $numActionsPlayed = count($actionsPlayed);
    if ($numActionsPlayed > 2 && TalentContains($actionsPlayed[$numActionsPlayed-3], "LIGHTNING", $mainPlayer) && $actionsPlayed[$numActionsPlayed-2] == "current_funnel_blue" && DelimStringContains($cardType, "A")) {
      if (SearchCurrentTurnEffects("current_funnel_blue", $mainPlayer, remove: true)) $hasGoAgain = true;
    }
    if ($cardType == "AA" && SearchCurrentTurnEffects("blizzard_blue", $player)) $hasGoAgain = false;
    if (DelimStringContains($cardType, "A")) $hasGoAgain = CurrentEffectGrantsNonAttackActionGoAgain($cardID, $from) || $hasGoAgain;
    if (DelimStringContains($cardType, "A") && $hasGoAgain && (SearchAuras("fog_down_yellow", 1) || SearchAuras("fog_down_yellow", 2))) $hasGoAgain = false;
    if (DelimStringContains($cardType, "I") && !HasMeld($cardID)){
      $hasGoAgain = CurrentEffectGrantsInstantGoAgain($cardID, $from) || $hasGoAgain;
    }
    elseif (DelimStringContains($cardType, "I") && $from != "MELD" && IsMeldInstantName($additionalCosts)){
      // handles case of only right side
      $hasGoAgain = CurrentEffectGrantsInstantGoAgain($cardID, $from) || $hasGoAgain;
    }
    elseif ($from == "MELD" && $additionalCosts == "MELD"){
      // handles case of left side resolving after melding
      $hasGoAgain = CurrentEffectGrantsInstantGoAgain($cardID, $from) || $hasGoAgain || HasGoAgain($cardID);
    }
    elseif ($from == "MELD"){
      // handles case of only left side
      $hasGoAgain = $hasGoAgain || HasGoAgain($cardID);
    }
  }
  if ($player == $mainPlayer && $hasGoAgain && !$goAgainPrevented) {
    if(SearchCurrentTurnEffects("arc_lightning_yellow", $player) && !IsMeldInstantName(GetClassState($player, $CS_AdditionalCosts)) && (GetClassState($player, $CS_AdditionalCosts) != "Both" || $from == "MELD")) {
      $count = CountCurrentTurnEffects("arc_lightning_yellow", $player);
      for ($i=0; $i < $count; $i++) {
        AddLayer("TRIGGER", $player, "arc_lightning_yellow");
      }
    }
    ++$actionPoints;
  }}

function PitchDeck($player, $index)
{
  $deck = new Deck($player);
  $cardID = RemovePitch($player, $index);
  $deck->AddBottom($cardID, "PITCH");
}

function GetUniqueId($cardID = "", $player = "")
{
  global $permanentUniqueIDCounter;
  ++$permanentUniqueIDCounter;
  return $player . ";" . $cardID . ";" . $permanentUniqueIDCounter;
}

function IsHeroAttackTarget()
{
  $target = explode("-", GetAttackTarget());
  return $target[0] == "THEIRCHAR";
}

function IsAllyAttackTarget()
{
  $target = explode("-", GetAttackTarget());
  return $target[0] == "THEIRALLY";
}

function IsSpecificAllyAttackTarget($player, $index, $uniqueID)
{
  global $combatChainState, $CCS_AttackTargetUID;
  $mzTarget = GetAttackTarget();
  $mzArr = explode("-", $mzTarget);
  if ($mzArr[0] == "ALLY" || $mzArr[0] == "MYALLY" || $mzArr[0] == "THEIRALLY") {
    return $index == intval($mzArr[1]) && $uniqueID == $combatChainState[$CCS_AttackTargetUID];
  }
  return false;
}

function IsSpecificAuraAttackTarget($player, $index, $uniqueID)
{
  global $combatChainState, $CCS_AttackTargetUID;
  $mzTarget = GetAttackTarget();
  $mzArr = explode("-", $mzTarget);
  if ($mzArr[0] == "AURAS" || $mzArr[0] == "MYAURAS" || $mzArr[0] == "THEIRALLYAURAS") {
    return $index == intval($mzArr[1]) && $uniqueID == $combatChainState[$CCS_AttackTargetUID];
  }
  return false;
}

function IsAllyAttacking()
{
  global $combatChain;
  if (count($combatChain) == 0) return false;
  return DelimStringContains(CardSubtype($combatChain[0]), "Ally");
}

function IsSpecificAllyAttacking($player, $index)
{
  global $combatChain, $combatChainState, $CCS_WeaponIndex, $mainPlayer;
  if (count($combatChain) == 0) return false;
  if ($mainPlayer != $player) return false;
  $weaponIndex = intval($combatChainState[$CCS_WeaponIndex]);
  if ($weaponIndex == -1) return false;
  if ($weaponIndex != $index) return false;
  if (!DelimStringContains(CardSubtype($combatChain[0]), "Ally")) return false;
  return true;
}

function IsSpecificAuraAttacking($player, $index)
{
  global $combatChain, $combatChainState, $CCS_WeaponIndex, $mainPlayer;
  if (count($combatChain) == 0) return false;
  if ($mainPlayer != $player) return false;
  $weaponIndex = intval($combatChainState[$CCS_WeaponIndex]);
  if ($weaponIndex == -1) return false;
  if ($weaponIndex != $index) return false;
  if (!DelimStringContains(CardSubtype($combatChain[0]), "Aura")) return false;
  return true;
}

function CanRevealCards($player)
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  if (SearchAurasForCard("channel_the_bleak_expanse_blue", $player) != "" || SearchAurasForCard("channel_the_bleak_expanse_blue", $otherPlayer) != "") {
    WriteLog("Reveal prevented by " . CardLink("channel_the_bleak_expanse_blue", "channel_the_bleak_expanse_blue"));
    return false;
  }
  return true;
}

function BaseAttackModifiers($attackID, $attackValue)
{
  global $currentTurnEffects, $mainPlayer, $CS_Num6PowBan;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] != $mainPlayer) continue;
    if (!IsCombatEffectActive($currentTurnEffects[$i])) continue;
    $effects = explode("-", $currentTurnEffects[$i]);
    switch ($effects[0]) {
      case "fatigue_shot_red":
      case "fatigue_shot_yellow":
      case "fatigue_shot_blue":
        $attackValue = ceil($attackValue / 2);
        break;
      case "ghostly_touch":
        if ($attackID == "UPR551") $attackValue = $effects[1];
        break;
      default:
        break;
    }
  }
  switch ($attackID) {
    case "diabolic_offering_blue":
      $attackValue = GetClassState($mainPlayer, $CS_Num6PowBan) > 0 ? 6 : 0;
      break;
    default:
      break;
  }
  return $attackValue;
}

function GetDamagePreventionIndices($player, $type)
{
  $rv = "";

  $auras = &GetAuras($player);
  $indices = "";
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    if (AuraDamagePreventionAmount($player, $i, $type, check: true) > 0 || HasWard($auras[$i], $player)) {
      if ($indices != "") $indices .= ",";
      $indices .= $i;
    }
  }
  $mzIndices = SearchMultiZoneFormat($indices, "MYAURAS");

  $char = &GetPlayerCharacter($player);
  $indices = "";
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    if ($char[$i + 1] != 0 && WardAmount($char[$i], $player) > 0 && $char[$i + 12] == "UP") {
      if ($indices != "") $indices .= ",";
      $indices .= $i;
    }
  }
  $indices = SearchMultiZoneFormat($indices, "MYCHAR");
  $mzIndices = CombineSearches($mzIndices, $indices);

  $items = &GetItems($player);
  $itemCount = count($items);
  $indices = "";
  for ($i = 0; $i < $itemCount; $i += ItemPieces()) {
    if (ItemDamagePeventionAmount($player, $i) > 0) {
      if ($indices != "") $indices .= ",";
      $indices .= $i;
    }
  }
  $indices = SearchMultizoneFormat($indices, "MYITEMS");
  $mzIndices = CombineSearches($mzIndices, $indices);

  $ally = &GetAllies($player);
  $indices = "";
  for ($i = 0; $i < count($ally); $i += AllyPieces()) {
    if ($ally[$i + 1] != 0 && WardAmount($ally[$i], $player) > 0) {
      if ($indices != "") $indices .= ",";
      $indices .= $i;
    }
  }
  $indices = SearchMultiZoneFormat($indices, "MYALLY");
  $mzIndices = CombineSearches($mzIndices, $indices);
  $rv = $mzIndices;

  return $rv;
}

function GetDamagePreventionTargetIndices()
{
  global $combatChain, $currentPlayer;
  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  $rv = "";
  $rv = SearchMultizone($otherPlayer, "LAYER");
  if (count($combatChain) > 0) $rv = CombineSearches($rv, "CC-0");
  if (SearchLayer($otherPlayer, "W") == "" && (count($combatChain) == 0 || CardType($combatChain[0]) != "W")) {
    $rv = CombineSearches($rv, SearchMultiZoneFormat(SearchCharacter($otherPlayer, type: "W"), "THEIRCHAR"));
  }
  $rv = CombineSearches($rv, SearchMultiZoneFormat(SearchAllies($otherPlayer), "THEIRALLY"));
  $rv = CombineSearches($rv, SearchMultiZoneFormat(SearchAura($otherPlayer), "THEIRAURAS"));
  $rv = CombineSearches($rv, SearchMultiZoneFormat(SearchAura($currentPlayer), "MYAURAS"));
  $rv = CombineSearches($rv, SearchMultiZoneFormat(SearchItems($otherPlayer), "THEIRITEMS"));
  if (ArsenalHasFaceUpCard($otherPlayer)) $rv = CombineSearches($rv, SearchMultiZoneFormat(SearchArsenal($otherPlayer), "THEIRARS"));
  $rv = CombineSearches($rv, SearchMultiZoneFormat(SearchCharacter($otherPlayer, type: "C"), "THEIRCHAR"));
  return $rv;
}

function SelfCostModifier($cardID, $from)
{
  global $CS_NumCharged, $currentPlayer, $combatChain, $layers, $CS_NumVigorDestroyed, $CS_NumCardsDrawn;
  $otherPlayer = ($currentPlayer == 1) ? 2 : 1;
  switch ($cardID) {
    case "arknight_ascendancy_red":
    case "ninth_blade_of_the_blood_oath_yellow":
    case "reduce_to_runechant_red":
    case "reduce_to_runechant_yellow":
    case "reduce_to_runechant_blue":
    case "amplify_the_arknight_red":
    case "amplify_the_arknight_yellow":
    case "amplify_the_arknight_blue":
    case "drawn_to_the_dark_dimension_red":
    case "drawn_to_the_dark_dimension_yellow":
    case "drawn_to_the_dark_dimension_blue":
    case "rune_flash_red":
    case "rune_flash_yellow":
    case "rune_flash_blue":
      return (-1 * NumRunechants($currentPlayer));
    case "bolting_blade_yellow":
      return (-1 * (2 * GetClassState($currentPlayer, $CS_NumCharged)));
    case "blinding_beam_red":
    case "blinding_beam_yellow":
    case "blinding_beam_blue":
      return TalentContains($combatChain[$layers[3]], "SHADOW") ? -1 : 0;
    case "jump_start_red":
    case "jump_start_yellow":
    case "jump_start_blue":
      return SearchMultizone($currentPlayer, "MYITEMS:isSameName=hyper_driver_red") != "" ? -1 : 0;
    case "bonds_of_ancestry_red":
    case "bonds_of_ancestry_yellow":
    case "bonds_of_ancestry_blue":
      return (ComboActive($cardID) ? -2 : 0);
    case "descendent_gustwave_red":
    case "descendent_gustwave_yellow":
    case "descendent_gustwave_blue":
      return (ComboActive($cardID) ? -1 : 0);
    case "bleed_out_red":
    case "bleed_out_yellow":
    case "bleed_out_blue":
      return (-1 * DamageDealtBySubtype("Dagger"));
    case "pummel_red":
    case "pummel_yellow":
    case "pummel_blue":
      if (GetPlayerCharacter($currentPlayer)[0] == "ROGUE030") {
        return -1;
      } else return 0;
    case "dimenxxional_vortex":
      return ($from == "BANISH" ? -2 : 0);
    case "grim_feast_red":
    case "grim_feast_yellow":
    case "grim_feast_blue":
      return ($from == "BANISH" ? -2 : 0);
    case "vile_inquisition_red":
    case "vile_inquisition_yellow":
    case "vile_inquisition_blue":
      return ($from == "BANISH" ? -2 : 0);
    case "runic_reckoning_red":
      return (-1 * NumRunechants($currentPlayer));
    case "liquidcooled_mayhem_red":
    case "liquidcooled_mayhem_yellow":
    case "liquidcooled_mayhem_blue":
    case "liquidcooled_mayhem_red":
    case "liquidcooled_mayhem_blue":
      return EvoUpgradeAmount($currentPlayer) * -1;
    case "annihilator_engine_red":
    case "terminator_tank_red":
    case "war_machine_red":
      return EvoUpgradeAmount($currentPlayer) >= 2 ? -3 : 0;
    case "rev_up_red":
    case "rev_up_yellow":
    case "rev_up_blue":
      return SearchMultizone($currentPlayer, "MYITEMS:isSameName=hyper_driver_red") != "" ? -1 : 0;
    case "quickfire_red":
    case "quickfire_yellow":
    case "quickfire_blue":
      return SearchCount(SearchMultizone($currentPlayer, "MYITEMS:isSameName=hyper_driver_red")) * -1;
    case "primed_to_fight_red":
      if (GetClassState($currentPlayer, $CS_NumVigorDestroyed) > 0 || CountAura("vigor", $currentPlayer) > 0) return -1;
      else return 0;
    case "stains_of_the_redback_red":
    case "stains_of_the_redback_yellow":
    case "stains_of_the_redback_blue":
      return (IsHeroAttackTarget() && CheckMarked(player: $otherPlayer)) ? -1 : 0;
    case "rising_energy_red":
    case "rising_energy_yellow":
    case "rising_energy_blue":
      return (GetClassState($currentPlayer, $CS_NumCardsDrawn) > 0 ? -1 : 0);
    case "sonata_galaxia_red":
      return (-1 * NumRunechants($currentPlayer));
    case "wrath_of_retribution_red":
    case "blood_drop_red":
    case "blood_line_red":
    case "blood_runs_deep_red":
      return (-1 * NumDraconicChainLinks());
    case "fire_and_brimstone_red":
    case "dynastic_dedication_red":
    case "imperial_intent_red":
      return (-1 * NumDraconicChainLinks());
    case "compounding_anger_red":
    case "bubble_to_the_surface_red":
    case "drop_of_dragon_blood_red":
      return (-1 * NumDraconicChainLinks());
    default:
      return 0;
  }
}

function IsAlternativeCostPaid($cardID, $from)
{
  global $currentTurnEffects, $currentPlayer, $combatChainState, $CCS_WasRuneGate;
  $isAlternativeCostPaid = false;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "moon_wish_red":
        case "cash_in_yellow":
        case "soul_reaping_red":
        case "rise_above_red":
        case "life_of_the_party_red":
        case "life_of_the_party_yellow":
        case "life_of_the_party_blue":
        case "double_down_red-PAID":
        case "10000_year_reunion_red":
          $isAlternativeCostPaid = true;
          $remove = true;
          break;
        default:
          break;
      }
      if ($remove) RemoveCurrentTurnEffect($i);
    }
  }
  if ($from == "BANISH" && SearchAuras("runechant", $currentPlayer) > 0 && HasRunegate($cardID) && SearchCount(SearchAurasForCard("runechant", $currentPlayer)) >= CardCost($cardID, $from)) {
    $combatChainState[$CCS_WasRuneGate] = 1;
    return true;
  }
  return $isAlternativeCostPaid;
}

function BanishCostModifier($from, $index, $cost)
{
  global $currentPlayer;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  if ($from != "BANISH" && $from != "THEIRBANISH") return 0;
  $from == "BANISH" ? $banish = GetBanish($currentPlayer) : $banish = GetBanish($otherPlayer);
  $mod = explode("-", $banish[$index + 1]);
  switch ($mod[0]) {
    case "sonic_boom_yellow":
      return -1 * intval($mod[1]);
    case "TCCGorgonsGaze":
      return -1 * intval($cost);
    default:
      return 0;
  }
}

function IsCurrentAttackName($name)
{
  $names = GetCurrentAttackNames();
  for ($i = 0; $i < count($names); ++$i) {
    if ($name == $names[$i]) return true;
  }
  return false;
}

function IsCardNamed($player, $cardID, $name)
{
  global $currentTurnEffects;
  if (CardName($cardID) == $name) return true;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    $effectArr = explode("-", $currentTurnEffects[$i]);
    $name = CurrentEffectNameModifier($effectArr[0], (count($effectArr) > 1 ? GamestateUnsanitize($effectArr[1]) : "N/A"), $player);
    //You have to do this at the end, or you might have a recursive loop -- e.g. with head_leads_the_tail_red
    if ($name != "" && $currentTurnEffects[$i + 1] == $player) return true;
  }
  return false;
}

function GetCurrentAttackNames()
{
  global $combatChain, $currentTurnEffects, $mainPlayer;
  $names = [];
  if (count($combatChain) == 0) return $names;
  array_push($names, CardName($combatChain[0]));
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    $effectArr = explode("-", $currentTurnEffects[$i]);
    $name = CurrentEffectNameModifier($effectArr[0], (count($effectArr) > 1 ? GamestateUnsanitize($effectArr[1]) : "N/A"), $mainPlayer);
    //You have to do this at the end, or you might have a recursive loop -- e.g. with head_leads_the_tail_red
    if ($name != "" && $currentTurnEffects[$i + 1] == $mainPlayer && IsCombatEffectActive($effectArr[0]) && !IsCombatEffectLimited($i)) {
      array_push($names, $name);
    }
  }
  return $names;
}

function SerializeCurrentAttackNames()
{
  $names = GetCurrentAttackNames();
  $serializedNames = "";
  for ($i = 0; $i < count($names); ++$i) {
    if ($serializedNames != "") $serializedNames .= ",";
    $serializedNames .= GamestateSanitize($names[$i]);
  }
  return $serializedNames;
}

function HasAttackName($name)
{
  global $chainLinkSummary;
  for ($i = 0; $i < count($chainLinkSummary); $i += ChainLinkSummaryPieces()) {
    $names = explode(",", $chainLinkSummary[$i + 4]);
    for ($j = 0; $j < count($names); ++$j) {
      if ($name == GamestateUnsanitize($names[$j])) return true;
    }
  }
  return false;
}

function HitEffectsArePrevented($cardID)
{
  global $combatChainState, $CCS_ChainLinkHitEffectsPrevented, $mainPlayer, $defPlayer;
  if (CardType($cardID) == "AA" && (SearchAuras("stamp_authority_blue", 1) || SearchAuras("stamp_authority_blue", 2))) return true;
  if (SearchCurrentTurnEffects("dense_blue_mist_blue-HITPREVENTION", $defPlayer)) return true;
  if ($combatChainState[$CCS_ChainLinkHitEffectsPrevented]) SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, true);
  return $combatChainState[$CCS_ChainLinkHitEffectsPrevented];
}

function HitEffectsPreventedThisLink()
{
  global $combatChainState, $CCS_ChainLinkHitEffectsPrevented;
  $combatChainState[$CCS_ChainLinkHitEffectsPrevented] = 1;
}

function HitsInRow()
{
  global $chainLinkSummary;
  $numHits = 0;
  for ($i = count($chainLinkSummary) - ChainLinkSummaryPieces(); $i >= 0 && intval($chainLinkSummary[$i + 5]) > 0; $i -= ChainLinkSummaryPieces()) {
    ++$numHits;
  }
  return $numHits;
}

function HitsInCombatChain()
{
  global $chainLinkSummary, $combatChainState, $CCS_HitThisLink;
  $numHits = intval($combatChainState[$CCS_HitThisLink]);
  for ($i = count($chainLinkSummary) - ChainLinkSummaryPieces(); $i >= 0; $i -= ChainLinkSummaryPieces()) {
    $numHits += intval($chainLinkSummary[$i + 5]);
  }
  return $numHits;
}

function NumAttacksHit()
{
  global $chainLinkSummary;
  $numHits = 0;
  for ($i = count($chainLinkSummary) - ChainLinkSummaryPieces(); $i >= 0; $i -= ChainLinkSummaryPieces()) {
    if ($chainLinkSummary[$i] > 0) ++$numHits;
  }
  return $numHits;
}

function NumChainLinks()
{
  global $chainLinkSummary, $combatChain;
  $numLinks = count($chainLinkSummary) / ChainLinkSummaryPieces();
  if (count($combatChain) > 0) ++$numLinks;
  return $numLinks;
}

function ClearGameFiles($gameName)
{
  @unlink("./Games/" . $gameName . "/gamestateBackup.txt");
  @unlink("./Games/" . $gameName . "/beginTurnGamestate.txt");
  @unlink("./Games/" . $gameName . "/lastTurnGamestate.txt");
}

function PlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "-")
{
  global $currentPlayer, $CS_NumCrouchingTigerPlayedThisTurn, $currentTurnEffects;
  $cardID = ShiyanaCharacter($cardID);
  $set = CardSet($cardID);
  $class = CardClass($cardID);
  if ($target != "-") {
    $targetArr = explode("-", $target);
    if ($targetArr[0] == "LAYERUID") {
      $targetArr[0] = "LAYER";
      $targetArr[1] = SearchLayersForUniqueID($targetArr[1]);
    }
    if (isset($targetArr[1])) $target = $targetArr[0] . "-" . $targetArr[1];
    else $target = $targetArr[0];
  }
  if (($set == "ELE" || $set == "UPR") && $additionalCosts != "-" && HasFusion($cardID)) {
    FuseAbility($cardID, $currentPlayer, $additionalCosts);
  }
  if (IsCardNamed($currentPlayer, $cardID, "Crouching Tiger")) IncrementClassState($currentPlayer, $CS_NumCrouchingTigerPlayedThisTurn);
  if (HasMeld($cardID)) {
    AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
    AddDecisionQueue("MELD", $currentPlayer, $cardID, 1);
    return "";
  }
  if ($set == "WTR") return WTRPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "ARC") {
    switch ($class) {
      case "MECHANOLOGIST":
        return ARCMechanologistPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "RANGER":
        return ARCRangerPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "RUNEBLADE":
        return ARCRunebladePlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "WIZARD":
        return ARCWizardPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "GENERIC":
        return ARCGenericPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      default:
        return "";
    }
  } else if ($set == "CRU") return CRUPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "MON") {
    switch ($class) {
      case "BRUTE":
        return MONBrutePlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "ILLUSIONIST":
        return MONIllusionistPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "RUNEBLADE":
        return MONRunebladePlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "WARRIOR":
        return MONWarriorPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "GENERIC":
        return MONGenericPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "NONE":
        return MONTalentPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      default:
        return "";
    }
  } else if ($set == "ELE") {
    switch ($class) {
      case "GUARDIAN":
        return ELEGuardianPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "RANGER":
        return ELERangerPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      case "RUNEBLADE":
        return ELERunebladePlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      default:
        return ELETalentPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
    }
  } else if ($set == "EVR") return EVRPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "UPR") return UPRPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "DVR") return DVRPlayAbility($cardID);
  else if ($set == "RVD") return RVDPlayAbility($cardID);
  else if ($set == "DYN") return DYNPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "OUT") return OUTPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "DTD") return DTDPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "TCC") return TCCPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "EVO") return EVOPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "HVY") return HVYPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "AKO") return AKOPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "MST") return MSTPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "ASB") return ASBPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "AAZ") return AAZPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "ROS") return ROSPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "TER") return TERPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "AUR") return AURPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "AIO") return AIOPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "AJV") return AJVPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else if ($set == "HNT") return HNTPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
  else {
    switch ($cardID) {
      case "jackolantern_red":
      case "jackolantern_yellow":
      case "jackolantern_blue":
        $deck = new Deck($currentPlayer);
        if (!$deck->Empty()) if (ColorContains($deck->BanishTop(), PitchValue($cardID), $currentPlayer)) PlayAura("runechant", $currentPlayer, 1, true);
        return "";
      case "blaze_firemind":
        $index = SearchCurrentTurnEffectsForIndex("blaze_firemind", $currentPlayer);
        $dynCost = explode("-", $currentTurnEffects[$index]);
        MZMoveCard($currentPlayer, "MYHAND:type=A;class=WIZARD;arcaneDamage=" . $dynCost[1], "MYBANISH,HAND,INST," . $cardID);
        return "";
      case "magrar";
          PlayAura("zen_state", $currentPlayer);
          PlayAura("inertia", $currentPlayer);
        return "";
      default:
        break;
    }
  }
  return ROGUEPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
}

function PitchAbility($cardID)
{
  global $currentPlayer, $CS_NumAddedToSoul;

  $pitchValue = PitchValue($cardID);
  if (GetClassState($currentPlayer, $CS_NumAddedToSoul) > 0 && SearchCharacterActive($currentPlayer, "vestige_of_sol") && TalentContains($cardID, "LIGHT", $currentPlayer)) {
    $resources = &GetResources($currentPlayer);
    $resources[0] += 1;
  }
  if ($pitchValue == 1) {
    $talismanOfRecompenseIndex = GetItemIndex("talisman_of_recompense_yellow", $currentPlayer);
    if ($talismanOfRecompenseIndex > -1) {
      WriteLog("Talisman of Recompense gained 3 instead of 1 and destroyed itself");
      DestroyItemForPlayer($currentPlayer, $talismanOfRecompenseIndex);
      GainResources($currentPlayer, 2);
    }
    if (ColorContains($cardID, 1, $currentPlayer) && SearchCharacterActive($currentPlayer, "dromai_ash_artist") || SearchCharacterActive($currentPlayer, "dromai") || SearchCurrentTurnEffects("dromai_ash_artist-SHIYANA", $currentPlayer) || SearchCurrentTurnEffects("dromai-SHIYANA", $currentPlayer)) {
      WriteLog("Dromai creates an Ash");
      PutPermanentIntoPlay($currentPlayer, "ash");
    }
  }
  if (SubtypeContains($cardID, "Chi", $currentPlayer)
    && SearchCharacterAlive($currentPlayer, "meridian_pathway")
    && SearchCharacterForCard($currentPlayer, "meridian_pathway")
    && GetCharacterGemState($currentPlayer, "meridian_pathway") == 1
    && !SearchCurrentTurnEffects("MERIDIANWARD", $currentPlayer)) {
    AddLayer("TRIGGER", $currentPlayer, "meridian_pathway");
  }
  switch ($cardID) {
    case "heart_of_fyendal_blue":
    case "eye_of_ophidia_blue":
    case "arknight_shard_blue":
    case "plague_hive_yellow":
    case "light_of_sol_yellow":
    case "schism_of_chaos_blue":
      AddLayer("TRIGGER", $currentPlayer, $cardID);
      break;
    case "master_cog_yellow": // Technically wrong, it should be a trigger, but since we can't reorder those it works better gameplay-wise to not have that one as a trigger
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:hasCrank=true&LAYER:hasCrank=true");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Crank to put a steam counter", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDCOUNTER", $currentPlayer, $cardID, 1);
      break;
    case "grandeur_of_valahai_blue":
      PlayAura("seismic_surge", $currentPlayer);
      break;
    case "blood_of_the_dracai_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "will_of_arcana_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      Writelog(CardLink($cardID, $cardID) . " is amping 1");
      break;
    default:
      break;
  }
}

function UnityEffect($cardID)
{
  global $defPlayer;
  switch ($cardID) {
    case "united_we_stand_yellow"://United We Stand
      $char = &GetPlayerCharacter($defPlayer);
      if ($char[0] == "ser_boltyn_breaker_of_dawn" || $char[0] == "boltyn") PlayAura("courage", $defPlayer);
      else if ($char[0] == "bravo_showstopper" || $char[0] == "bravo") PlayAura("seismic_surge", $defPlayer);
      else if ($char[0] == "briar_warden_of_thorns" || $char[0] == "briar") PlayAura("embodiment_of_earth", $defPlayer);
      else if ($char[0] == "briar_warden_of_thorns" || $char[0] == "briar") PlayAura("embodiment_of_earth", $defPlayer);
      else if ($char[0] == "dorinthea_ironsong" || $char[0] == "dorinthea") PlayAura("courage", $defPlayer);
      else if ($char[0] == "lexi_livewire" || $char[0] == "lexi") PlayAura("embodiment_of_lightning", $defPlayer);
      else if ($char[0] == "oldhim_grandfather_of_eternity" || $char[0] == "oldhim") PlayAura("spellbane_aegis", $defPlayer);
      else if ($char[0] == "prism_sculptor_of_arc_light" || $char[0] == "prism" || $char[0] == "prism_awakener_of_sol" || $char[0] == "prism_advent_of_thrones") PlayAura("spectral_shield", $defPlayer);
      else if ($char[0] == "shiyana_diamond_gemini") PlayAura("eloquence", $defPlayer);
      break;
    case "anthem_of_spring_blue"://Anthem of Spring
      PlayAura("embodiment_of_earth", $defPlayer);
      break;
    case "northern_winds_blue"://Northern Winds
      PlayAura("spellbane_aegis", $defPlayer);
      break;
    case "call_down_the_lightning_yellow":
      PlayAura("embodiment_of_lightning", $defPlayer);
      break;
    case "star_struck_yellow":
      PlayAura("seismic_surge", $defPlayer);
      break;
    case "bastion_of_unity":
      AddCurrentTurnEffect("bastion_of_unity", $defPlayer);
      break;
    case "chorus_of_ironsong_yellow":
      PlayAura("courage", $defPlayer);
      break;
    case "alluring_inducement_yellow":
      PlayAura("eloquence", $defPlayer);
      break;
    default:
      break;
  }
}

function Draw($player, $mainPhase = true, $fromCardEffect = true, $effectSource = "-")
{
  global $EffectContext, $mainPlayer, $CS_NumCardsDrawn;
  $otherPlayer = ($player == 1 ? 2 : 1);
  if ($mainPhase && $player != $mainPlayer) {
    $talismanOfTithes = SearchItemsForCard("talisman_of_tithes_blue", $otherPlayer);
    if ($talismanOfTithes != "") {
      $indices = explode(",", $talismanOfTithes);
      DestroyItemForPlayer($otherPlayer, $indices[0]);
      WriteLog(CardLink("talisman_of_tithes_blue", "talisman_of_tithes_blue") . " prevented a draw and was destroyed");
      return "";
    }
  }
  if ($fromCardEffect && (SearchAurasForCard("channel_the_bleak_expanse_blue", $otherPlayer) != "" || SearchAurasForCard("channel_the_bleak_expanse_blue", $player) != "")) {
    WriteLog("Draw prevented by " . CardLink("channel_the_bleak_expanse_blue", "channel_the_bleak_expanse_blue"));
    return "";
  }
  $deck = new Deck($player);
  $hand = &GetHand($player);
  if ($deck->Empty()) return -1;
  if (CurrentEffectPreventsDraw($player, $mainPhase)) return -1;
  $cardID = $deck->Top(remove: true);
  if ($mainPhase && (SearchAurasForCard("chains_of_mephetis_blue", 1) != "" || SearchAurasForCard("chains_of_mephetis_blue", 2) != "")) {
    BanishCardForPlayer($cardID, $player, "DECK", "TT", $player);
  } else {
    array_push($hand, $cardID);
    IncrementClassState($player, $CS_NumCardsDrawn, 1);
  }
  if ($mainPhase && (SearchCharacterActive($otherPlayer, "valda_brightaxe") || (SearchCurrentTurnEffects("valda_brightaxe-SHIYANA", $otherPlayer) && SearchCharacterActive($otherPlayer, "shiyana_diamond_gemini")))) PlayAura("seismic_surge", $otherPlayer);
  if (SearchCharacterActive($player, "earthlore_bounty")) {
    $context = $effectSource != "-" ? $effectSource : $EffectContext;
    if ($context != "-") {
      $cardType = CardType($context);
      if (DelimStringContains($cardType, "A") || $cardType == "AA") PlayAura("seismic_surge", $player);
    }
  }
  if ($mainPhase && SearchCharacterActive($otherPlayer, "ROGUE026")) {
    $health = &GetHealth($otherPlayer);
    $health += -10;
    if ($health < 1) {
      $health = 1;
      WriteLog("NO! You will not banish me! I refuse!");
    }
  }
  if ($mainPhase) {
    $numBrainstorm = CountCurrentTurnEffects("brainstorm_blue", $player);
    if ($numBrainstorm > 0) {
      $character = &GetPlayerCharacter($player);
      for ($i = 0; $i < $numBrainstorm; ++$i) DealArcane(1, 2, "TRIGGER", $character[0]);
    }
  }
  PermanentDrawCardAbilities($player);
  $hand = array_values($hand);
  return $hand[count($hand) - 1];
}

function ChooseToPay($player, $cardID, $amounts)
{
  AddDecisionQueue("SETDQCONTEXT", $player, "Choose how much to pay for " . CardLink($cardID, $cardID));
  AddDecisionQueue("BUTTONINPUT", $player, $amounts);
  AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
  AddDecisionQueue("LESSTHANPASS", $player, "1", 1);
}

function WardPoppedAbility($player, $cardID)
{
  if (SearchCharacterActive($player, "celestial_kimono", setInactive: true)) {
    GainResources($player, 1);
    WriteLog("Player " . $player . " gained 1 resource from " . CardLink("celestial_kimono", "celestial_kimono"));
  }
  if (SearchCharacterActive($player, "diadem_of_dreamstate", setInactive: true) || $cardID == "diadem_of_dreamstate") {
    AddDecisionQueue("YESNO", $player, "if_you_want_to_pay_1_to_create_a_".CardLink("ponder", "ponder"));
    AddDecisionQueue("NOPASS", $player, "-");
    AddDecisionQueue("PAYRESOURCES", $player, "1", 1);
    AddDecisionQueue("PLAYAURA", $player, "ponder-1", 1);
  }
  if ($cardID == "fluttersteps") {
    AddCurrentTurnEffect("fluttersteps", $player);
  }
}

function BanishHand($player)
{
  $hand = &GetHand($player);
  $banishedCards = "";
  for ($i = 0; $i < count($hand); ++$i) {
    if ($banishedCards != "") $banishedCards .= ",";
    $banishedCards .= $hand[$i];
    BanishCardForPlayer($hand[$i], $player, "HAND", "-", $player);
  }
  $hand = [];
  return $banishedCards;
}

function EvoOnPlayHandling($player)
{
  if (SearchCurrentTurnEffects("teklovossen_esteemed_magnate", $player, true) || SearchCurrentTurnEffects("teklovossen", $player, true)) Draw($player);
}

function EvoHandling($cardID, $player, $from)
{
  global $dqVars, $CombatChain;
  $char = &GetPlayerCharacter($player);
  $slot = "";
  $otherPlayer = $player == 1 ? 2 : 1;
  if (SubtypeContains($cardID, "Head")) $slot = "Head";
  else if (SubtypeContains($cardID, "Chest")) $slot = "Chest";
  else if (SubtypeContains($cardID, "Arms")) $slot = "Arms";
  else if (SubtypeContains($cardID, "Legs")) $slot = "Legs";
  $replaced = 0;
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    if (!$replaced && SubtypeContains($char[$i], $slot, uniqueID:$char[$i + 11])) {
      if (SubtypeContains($char[$i], "Base") && $char[$i + 1] != 0) {
        $CombatChain->Remove(GetCombatChainIndex($char[$i], $player));
        CharacterAddSubcard($player, $i, $char[$i]);
        $fromCardID = $char[$i];
        $char[$i + 2] = 0;//Reset counters
        $char[$i + 4] = 0;//Reset defense counters
        $char[$i + 6] = 0;//Not on chain anymore
        $char[$i] = $cardID . "_equip";
        $char[$i + 7] = 0;//Should not be flagged for destruction
        $char[$i + 8] = 0;//Should not be frozen
        $char[$i + 9] = CharacterDefaultActiveState($char[$i]);
        $dqVars[1] = $i;
        EvoTransformAbility($char[$i], $fromCardID, $player);
        $replaced = 1;
      }
    }
  }
  if (!$replaced) {
    if (substr($from, 0, 5) != "THEIR") AddGraveyard($cardID, $player, "HAND", $player);
    else AddGraveyard($cardID, $otherPlayer, "GRAVEYARD", $player);
    WriteLog("<b>🚫 *ERR0R* // No base of that type equipped //</b>");
  }
}

function CharacterAddSubcard($player, $index, $card)
{
  $char = &GetPlayerCharacter($player);
  if (isSubcardEmpty($char, $index)) $char[$index + 10] = $card;
  else $char[$index + 10] = $char[$index + 10] . "," . $card;
}

function CharacterChooseSubcard($player, $index, $fromDQ = false, $count = 1, $isMandatory = true)
{
  $character = &GetPlayerCharacter($player);
  $subcards = explode(",", $character[$index + 10]);
  $subcardsCount = count($subcards);
  $chooseMultizoneData = "";
  for ($i = 0; $i < $subcardsCount; $i++) {
    if ($chooseMultizoneData == "") $chooseMultizoneData = "CARDID-" . $subcards[$i];
    else $chooseMultizoneData = $chooseMultizoneData . ",CARDID-" . $subcards[$i];
  }
  if ($chooseMultizoneData != "") {
    if ($count == 1) {
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a subcard to banish from " . CardName($character[$index]));
      if ($isMandatory) AddDecisionQueue("CHOOSEMULTIZONE", $player, $chooseMultizoneData);
      else AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, $chooseMultizoneData);
      AddDecisionQueue("MZOP", $player, "GETCARDINDEX", 1);
      if ($character[0] == "teklovossen_the_mechropotent") AddDecisionQueue("REMOVESOUL", $player, $index, 1);
      AddDecisionQueue("REMOVESUBCARD", $player, $index, 1);
    } else {
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose " . $count . " subcards to banish from " . CardName($character[$index]));
      AddDecisionQueue("MULTICHOOSESUBCARDS", $player, $count . "-" . str_replace("CARDID-", "", $chooseMultizoneData) . "-" . $count);
      if ($character[0] == "teklovossen_the_mechropotent") AddDecisionQueue("REMOVESOUL", $player, $index);
      AddDecisionQueue("REMOVESUBCARD", $player, $index);
    }
  }
}

function EvoHasUnderCard($player, $index)
{
  $char = &GetPlayerCharacter($player);
  return $char[$index + 10] != "";
}

function EvoTransformAbility($toCardID, $fromCardID, $player = "")
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  switch ($toCardID) {
    case "evo_steel_soul_memory_blue":
    case "evo_steel_soul_memory_blue_equip":
      if (SubtypeContains($fromCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID))
        AddCurrentTurnEffect($toCardID, $player);
      break;
    case "evo_steel_soul_processor_blue":
    case "evo_steel_soul_processor_blue_equip":
      if (SubtypeContains($fromCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID))
        GainResources($player, 3);
      break;
    case "evo_steel_soul_controller_blue":
    case "evo_steel_soul_controller_blue_equip":
      if (SubtypeContains($fromCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID)) {
        MZMoveCard($player, "MYDISCARD:type=AA;maxAttack=6;minAttack=6", "MYTOPDECK-4", true);
      }
      break;
    case "evo_steel_soul_tower_blue":
    case "evo_steel_soul_tower_blue_equip":
      if (SubtypeContains($fromCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID))
        GainActionPoints(1, $player);
      break;
    case "evo_zoom_call_yellow":
    case "evo_zoom_call_yellow_equip":
      MZChooseAndBanish($player, "MYHAND", "HAND,-", may: true);
      AddDecisionQueue("DRAW", $player, "-", 1);
      break;
    case "evo_buzz_hive_yellow":
    case "evo_buzz_hive_yellow_equip":
      GainResources($player, 1);
      break;
    case "evo_whizz_bang_yellow":
    case "evo_whizz_bang_yellow_equip":
      AddCurrentTurnEffect("evo_whizz_bang_yellow", $player);
      break;
    case "evo_zip_line_yellow":
    case "evo_zip_line_yellow_equip":
      GiveAttackGoAgain();
      break;
    case "evo_recall_blue":
    case "evo_recall_blue_equip":
      MZMoveCard($player, "MYBANISH:type=AA;class=MECHANOLOGIST&MYBANISH:type=A;class=MECHANOLOGIST", "MYTOPDECK", true, true);
      break;
    case "evo_heartdrive_blue":
    case "evo_heartdrive_blue_equip":
      AddCurrentTurnEffect("evo_heartdrive_blue", $player);
      break;
    case "evo_shortcircuit_blue":
    case "evo_shortcircuit_blue_equip":
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYCHAR:type=C&THEIRCHAR:type=C&MYALLY&THEIRALLY", 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a target to deal 1 damage");
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZDAMAGE", $player, "1,DAMAGE," . $toCardID, 1);
      break;
    case "evo_speedslip_blue":
    case "evo_speedslip_blue_equip":
      AddCurrentTurnEffect("evo_speedslip_blue", $player);
      break;
    default:
      break;
  }
  switch ($fromCardID) {
    case "evo_steel_soul_memory_blue_equip":
      if (TypeContains($toCardID, "C", $player)) {
        AddCurrentTurnEffect($fromCardID, $player);
        AddCurrentTurnEffect($fromCardID, $player);
      } else if (SubtypeContains($toCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID)) {
        AddCurrentTurnEffect($fromCardID, $player);
      }
      break;
    case "evo_steel_soul_processor_blue_equip":
      if (TypeContains($toCardID, "C", $player)) {
        GainResources($player, 6);
      } else if (SubtypeContains($toCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID)) {
        GainResources($player, 3);
      }
      break;
    case "evo_steel_soul_controller_blue_equip":
      if (TypeContains($toCardID, "C", $player)) {
        MZMoveCard($player, "MYDISCARD:type=AA;minAttack=6", "MYTOPDECK-4", true);
        MZMoveCard($player, "MYDISCARD:type=AA;minAttack=6", "MYTOPDECK-4", true);
      } else if (SubtypeContains($toCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID)) {
        MZMoveCard($player, "MYDISCARD:type=AA;minAttack=6", "MYTOPDECK-4", true);
      }
      break;
    case "evo_steel_soul_tower_blue_equip":
      if (TypeContains($toCardID, "C", $player)) {
        GainActionPoints(2, $player);
      } else if (SubtypeContains($toCardID, "Evo", $player) && CardName($fromCardID) != CardName($toCardID)) {
        GainActionPoints(1, $player);
      }
      break;
    default:
      break;
  }
}

function EvoUpgradeAmount($player)
{
  $amount = 0;
  $amount += SearchCount(SearchCharacter($player, subtype: "Evo"));
  if (FindCharacterIndex($player, "teklovossen_the_mechropotent") != -1) $amount += 2; //Only +2 as we already find teklovossen_the_mechropotent and teklovossen_the_mechropotentb counted in the search SearchCount(SearchCharacter($player, subtype:"Evo"))
  return $amount;
}

function EquipmentsUsingSteamCounter($charID)
{
  switch ($charID) {
    case "cogwerx_base_head":
    case "cogwerx_base_chest":
    case "cogwerx_base_arms":
    case "cogwerx_base_legs":
      return true;
    default:
      return false;
  }
}

function CheckIfConstructNitroMechanoidConditionsAreMet($currentPlayer)
{
  $hasHead = false;
  $hasChest = false;
  $hasArms = false;
  $hasLegs = false;
  $hasWeapon = false;
  $char = &GetPlayerCharacter($currentPlayer);
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    $characterCardID = $char[$i];
    if ($char[$i + 1] == 0) continue;
    if (!ClassContains($characterCardID, "MECHANOLOGIST", $currentPlayer)) continue;
    if (CardType($characterCardID) == "W") $hasWeapon = true;
    else {
      if (SubtypeContains($characterCardID, "Head", $currentPlayer, $char[$i + 11])) $hasHead = true;
      if (SubtypeContains($characterCardID, "Chest", $currentPlayer, $char[$i + 11])) $hasChest = true;
      if (SubtypeContains($characterCardID, "Arms", $currentPlayer, $char[$i + 11])) $hasArms = true;
      if (SubtypeContains($characterCardID, "Legs", $currentPlayer, $char[$i + 11])) $hasLegs = true;
    }
  }
  if (!$hasHead || !$hasChest || !$hasArms || !$hasLegs || !$hasWeapon) return "You do not meet the equipment requirement";
  if (SearchCount(SearchMultizone($currentPlayer, "MYITEMS:isSameName=hyper_driver_red")) < 3) return "You do not meet the Hyper Driver requirement";
  return "";
}

function CheckIfSingularityConditionsAreMet($currentPlayer)
{
  $hasWeapon = false;
  $evoCount = 0;
  $char = &GetPlayerCharacter($currentPlayer);
  $charCount = count($char);
  $charPieces = CharacterPieces();
  for ($i = 0; $i < $charCount; $i += $charPieces) {
    if ($char[$i + 1] == 0) continue;
    if (CardType($char[$i]) == "W") $hasWeapon = true;
    if (SubtypeContains($char[$i], "Evo")) $evoCount++;
  }
  if (!$hasWeapon) return "You do not meet the weapon requirement";
  if ($evoCount < 4) return "You do not meet the Evo requirement";
  return "";
}

function CanOnlyTargetHeroes($cardID)
{
  switch ($cardID) {
    case "apocalypse_automaton_red":
      return true;
    default:
      return false;
  }
}

function NonHitEffects($cardID)
{
  global $mainPlayer, $defPlayer, $currentTurnEffects;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i] == $cardID && $currentTurnEffects[$i + 1] == $mainPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "send_packing_yellow":
          RemoveCurrentTurnEffect($i);
          $banish = new Banish($defPlayer);
          $banishedCard = $banish->FirstCardWithModifier($cardID);
          if ($banishedCard == null) break;
          $banishIndex = $banishedCard->Index();
          if ($banishIndex > -1) AddPlayerHand($banish->Remove($banishIndex), $defPlayer, "BANISH");
          break;
        default:
          break;
      }
    }
  }
}

function MentorTrigger($player, $index, $specificCard = "")
{
  $cardID = RemoveArsenal($player, $index);
  BanishCardForPlayer($cardID, $player, "ARS", "-");
  if ($specificCard != "") AddDecisionQueue("MULTIZONEINDICES", $player, "MYDECK:cardID=$specificCard");
  else AddDecisionQueue("MULTIZONEINDICES", $player, "MYDECK:specOnly=true");
  AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
  AddDecisionQueue("MZADDZONE", $player, "MYARS,DECK,UP", 1);
  AddDecisionQueue("MZREMOVE", $player, "-", 1);
  AddDecisionQueue("SHUFFLEDECK", $player, "-");
}

function ResolveGoesWhere($goesWhere, $cardID, $player, $from, $effectController = "", $modifier = "NA")
{
  if($effectController == "") $effectController = $player;
  $otherPlayer = $player == 1 ? 2 : 1;
  switch ($goesWhere) {
    case "BOTDECK":
      AddBottomDeck($cardID, $player, $from);
      break;
    case "HAND":
      AddPlayerHand($cardID, $player, $from);
      break;
    case "GY":
      if ($from == "CC") break; //Things that would go to the GY stay on till the chain is closing and the close step.
      if ($from == "CHAINCLOSING") $from = "CC";
      if (DelimStringContains(CardSubType($cardID), "Affliction") && $from != "CC") $player = $otherPlayer;
      AddGraveyard($cardID, $player, $from, $effectController);
      break;
    case "SOUL":
      AddSoul($cardID, $player, $from);
      break;
    case "BANISH":
      BanishCardForPlayer($cardID, $player, $from, $modifier);
      break;
    case "THEIRHAND":
      AddPlayerHand($cardID, $otherPlayer, $from);
      break;
    case "THEIRBANISH":
      BanishCardForPlayer($cardID, $otherPlayer, $from, $modifier);
      break;
    case "THEIRDISCARD":
      AddGraveyard($cardID, $otherPlayer, $from, $effectController);
      break;
    case "THEIRBOTDECK":
      AddBottomDeck($cardID, $otherPlayer, $from);
      break;
    default:
      break;
  }
}

function isPreviousLinkDraconic()
{
  global $chainLinkSummary;
  $isDraconic = false;
  if (count($chainLinkSummary) == 0) return $isDraconic; # No previous links so nothing happens if this is true
  $talents = explode(",", $chainLinkSummary[count($chainLinkSummary) - ChainLinkSummaryPieces() + 2]); # Search through the talent types logged on the previous link
  for ($i = 0; $i < count($talents); ++$i) { # Cycle through talents to see if that previous link was Draconic
    if ($talents[$i] == "DRACONIC") $isDraconic = true;
  }
  return $isDraconic;
}
