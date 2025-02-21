<?php

//0 - Card ID
//1 - Status (2=ready, 1=unavailable, 0=destroyed)
//2 - Num counters
//3 - Num attack counters
//4 - Num defense counters
//5 - Num uses
//6 - On chain (1 = yes, 0 = no)
//7 - Flagged for destruction (1 = yes, 0 = no)
//8 - Frozen (1 = yes, 0 = no)
//9 - Is Active (2 = always active, 1 = yes, 0 = no)
//10 - Subcards , delimited
//11 - Unique ID
//12 - Face Up/Down
class Character
{
  // property declaration
  public $cardID = "";
  public $status = 2;
  public $numCounters = 0;
  public $numAttackCounters = 0;
  public $numDefenseCounters = 0;
  public $numUses = 0;
  public $onChain = 0;
  public $flaggedForDestruction = 0;
  public $frozen = 0;
  public $isActive = 2;
  public $subCards = "";
  public $uniqueID = 0;
  public $facing = "UP";
  public $marked = 0;


  private $player = null;
  private $arrIndex = -1;

  public function __construct($player, $index)
  {
    $this->player = $player;
    $this->arrIndex = $index;
    $array = &GetPlayerCharacter($player);

    $this->cardID = $array[$index];
    $this->status = $array[$index + 1];
    $this->numCounters = $array[$index + 2];
    $this->numAttackCounters = $array[$index + 3];
    $this->numDefenseCounters = $array[$index + 4];
    $this->numUses = $array[$index + 5];
    $this->onChain = $array[$index + 6];
    $this->flaggedForDestruction = $array[$index + 7];
    $this->frozen = $array[$index + 8];
    $this->isActive = $array[$index + 9];
    $this->subCards = $array[$index + 10];
    $this->uniqueID = $array[$index + 11];
    $this->facing = $array[$index + 12];
    $this->marked = $array[$index + 13];
  }

  public function Finished()
  {
    $array = &GetPlayerCharacter($this->player);
    $array[$this->arrIndex] = $this->cardID;
    $array[$this->arrIndex + 1] = $this->status;
    $array[$this->arrIndex + 2] = $this->numCounters;
    $array[$this->arrIndex + 3] = $this->numAttackCounters;
    $array[$this->arrIndex + 4] = $this->numDefenseCounters;
    $array[$this->arrIndex + 5] = $this->numUses;
    $array[$this->arrIndex + 6] = $this->onChain;
    $array[$this->arrIndex + 7] = $this->flaggedForDestruction;
    $array[$this->arrIndex + 8] = $this->frozen;
    $array[$this->arrIndex + 9] = $this->isActive;
    $array[$this->arrIndex + 10] = $this->subCards;
    $array[$this->arrIndex + 11] = $this->uniqueID;
    $array[$this->arrIndex + 12] = $this->facing;
    $array[$this->arrIndex + 13] = $this->marked;
  }
}

function PutCharacterIntoPlayForPlayer($cardID, $player)
{
  $char = &GetPlayerCharacter($player);
  $index = count($char);
  array_push($char, $cardID);
  array_push($char, 2);
  array_push($char, CharacterCounters($cardID));
  array_push($char, 0);
  array_push($char, 0);
  array_push($char, 1);
  array_push($char, 0);
  array_push($char, 0);
  array_push($char, 0);
  array_push($char, CharacterDefaultActiveState($cardID));
  array_push($char, "-");
  array_push($char, GetUniqueId($cardID, $player));
  array_push($char, HasCloaked($cardID, $player));
  array_push($char, 0);
  return $index;
}

function CharacterCounters($cardID)
{
  switch ($cardID) {
    case "nitro_mechanoida":
      return 8;
    default:
      return 0;
  }
}

//CR 2.1 6.4.10f If an effect states that a prevention effect can not prevent the damage of an event, the prevention effect still applies to the event but its prevention amount is not reduced
function CharacterTakeDamageAbility($player, $index, $damage, $preventable)
{
  $char = &GetPlayerCharacter($player);
  $type = "-";
  $remove = false;
  $preventedDamage = 0;
  if ($damage > 0 && HasWard($char[$index], $player)) {
    if ($preventable) $preventedDamage += WardAmount($char[$index], $player);//$damage -= WardAmount($char[$index], $player);
    $remove = true;
  }
  switch ($char[$index]) {
    default:
      break;
  }
  if ($remove) DestroyCharacter($player, $index);
  if ($preventedDamage > 0 && SearchCurrentTurnEffects("vambrace_of_determination", $player) != "") {
    $preventedDamage -= 1;
    SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
  }
  $damage -= $preventedDamage;
  if ($damage <= 0) $damage = 0;
  return $damage;
}

function CharacterStartTurnAbility($index)
{
  global $mainPlayer, $CS_TunicTicks;
  $otherPlayer = $mainPlayer == 1 ? 2 : 1;
  $char = new Character($mainPlayer, $index);
  $character = GetPlayerCharacter($mainPlayer);
  if ($char->status != 2) return;
  $cardID = $char->cardID;
  if ($index == 0) $cardID = ShiyanaCharacter($cardID);
  switch ($cardID) {
    case "fyendals_spring_tunic":
      if (!ManualTunicSetting($mainPlayer)) {
        if ($char->numCounters < 3) {
          ++$char->numCounters;
          IncrementClassState($mainPlayer, $CS_TunicTicks);
        }
        $char->Finished();
      }
      break;
    case "shiyana_diamond_gemini":
      AddLayer("TRIGGER", $mainPlayer, $char->cardID);
      break;
    case "carrion_husk":
      if (GetHealth($mainPlayer) <= 13) {
        $char->status = 0;
        BanishCardForPlayer($char->cardID, $mainPlayer, "EQUIP", "NA");
        WriteLog(CardLink($char->cardID, $char->cardID) . " got banished for having 13 or less life");
        $char->Finished();
      }
      break;
    case "bravo_star_of_the_show":
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "You may reveal an Earth, Ice, and Lightning card for Bravo, Star of the Show");
      AddDecisionQueue("FINDINDICES", $mainPlayer, "BRAVOSTARSHOW");
      AddDecisionQueue("MULTICHOOSEHAND", $mainPlayer, "<-", 1);
      AddDecisionQueue("BRAVOSTARSHOW", $mainPlayer, "-", 1);
      break;
    case "valda_brightaxe":
      if (CountAura("seismic_surge", $mainPlayer) >= 3) {
        WriteLog(CardLink($char->cardID, $char->cardID) . " gives Crush attacks Dominate this turn");
        AddCurrentTurnEffect("valda_brightaxe", $mainPlayer);
      }
      break;
    case "levia_redeemed":
      if ($character[1] < 3) {
        AddCurrentTurnEffect("levia_redeemed", $mainPlayer);
      }
      break;
    case "vynnset_iron_maiden":
    case "vynnset":
      if ($character[1] < 3) {
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card to banish for Vynnset");
        MZMoveCard($mainPlayer, "MYHAND", "MYBANISH,HAND,-");
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, "runechant", 1);
        AddDecisionQueue("PUTPLAY", $mainPlayer, "-", 1);
      }
      break;
    case "ROGUE015":
      $hand = &GetHand($mainPlayer);
      array_unshift($hand, "crouching_tiger");
      break;
    case "ROGUE017":
      $hand = &GetHand($mainPlayer);
      array_unshift($hand, "gorganian_tome");
      Draw($mainPlayer);
      break;
    case "ROGUE018":
      AddCurrentTurnEffect("ROGUE018", $mainPlayer);
      break;
    case "ROGUE010":
      PlayAura("runechant", $mainPlayer);
      PlayAura("runechant", $mainPlayer);
      break;
    case "ROGUE021":
      $hand = &GetHand($mainPlayer);
      array_unshift($hand, "smash_with_big_tree_red");
      $resources = &GetResources($mainPlayer);
      $resources[0] += 2;
      break;
    case "ROGUE022":
      $defBanish = &GetBanish($otherPlayer);
      $health = &GetHealth($mainPlayer);
      $totalBD = 0;
      for ($i = 0; $i < count($defBanish); $i += BanishPieces()) {
        if (HasBloodDebt($defBanish[$i])) ++$totalBD;
      }
      $health += $totalBD;
      array_push($defBanish, "ghostly_visit_red");
      array_push($defBanish, "");
      array_push($defBanish, GetUniqueId());
      break;
    case "ROGUE024":
      AddCurrentTurnEffect("ROGUE024", $otherPlayer);
      break;
    case "ROGUE028":
      PlayAura("spectral_shield", $mainPlayer);
      break;
    case "victor_goldmane_high_and_mighty":
    case "victor_goldmane":
      if (!SearchCurrentTurnEffects($cardID . "-1", $mainPlayer) && $character[1] < 3) AddCurrentTurnEffect($cardID . "-1", $mainPlayer);
      break;
    case "barbed_castaway":
      AddCurrentTurnEffect("barbed_castaway-Load", $mainPlayer);
      AddCurrentTurnEffect("barbed_castaway-Aim", $mainPlayer);
      break;
    case "luminaris_angels_glow":
      AddCurrentTurnEffect("luminaris_angels_glow-1", $mainPlayer);
      AddCurrentTurnEffect("luminaris_angels_glow-2", $mainPlayer);
      break;
    case "heirloom_of_snake_hide":
      $index = FindCharacterIndex($mainPlayer, $cardID);
      if ($character[$index + 12] == "DOWN" && GetHealth($mainPlayer) == 1) {
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Do you want to turn face-up " . CardLink($cardID, $cardID) . "?", 1);
        AddDecisionQueue("YESNO", $mainPlayer, "an_action", 1);
        AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, $index, 1);
        AddDecisionQueue("TURNCHARACTERFACEUP", $mainPlayer, "-", 1);
      }
      break;
    case "heirloom_of_rabbit_hide":
      $index = FindCharacterIndex($mainPlayer, $cardID);
      if ($character[$index + 12] == "DOWN" && GetHealth($mainPlayer) == 1) {
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Do you want to turn face-up " . CardLink($cardID, $cardID) . "?", 1);
        AddDecisionQueue("YESNO", $mainPlayer, "an_action", 1);
        AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, $index, 1);
        AddDecisionQueue("TURNCHARACTERFACEUP", $mainPlayer, "-", 1);
      }
      break;
    case "heirloom_of_tiger_hide":
      $index = FindCharacterIndex($mainPlayer, $cardID);
      if ($character[$index + 12] == "DOWN" && GetHealth($mainPlayer) == 1) {
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Do you want to turn face-up " . CardLink($cardID, $cardID) . "?", 1);
        AddDecisionQueue("YESNO", $mainPlayer, "an_action", 1);
        AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, $index, 1);
        AddDecisionQueue("TURNCHARACTERFACEUP", $mainPlayer, "-", 1);
      }
      break;
    case "aqua_seeing_shell":
    case "waves_of_aqua_marine":
    case "aqua_laps":
      $index = FindCharacterIndex($mainPlayer, $cardID);
      if ($character[$index + 12] == "UP") DestroyCharacter($mainPlayer, $index);
      break;
    case "koi_blessed_kimono":
      $index = FindCharacterIndex($mainPlayer, $cardID);
      if ($character[$index + 12] == "DOWN" && GetHealth($mainPlayer) == 1) {
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Do you want to turn face-up " . CardLink($cardID, $cardID) . "?", 1);
        AddDecisionQueue("YESNO", $mainPlayer, "an_action", 1);
        AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, $index, 1);
        AddDecisionQueue("TURNCHARACTERFACEUP", $mainPlayer, "-", 1);
        AddDecisionQueue("DESTROYCHARACTER", $mainPlayer, "-", 1);
        MZMoveCard($mainPlayer, "MYDECK:isSameName=MST099_inner_chi_blue", "MYHAND", may: true, isReveal: true, isSubsequent: true);
        AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-", 1);
      }
      break;
    default:
      break;
  }
}

function DefCharacterStartTurnAbilities()
{
  global $defPlayer, $mainPlayer;
  $character = &GetPlayerCharacter($defPlayer);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] == 0 || $character[$i + 1] == 1) continue; //Do not process ability if it is destroyed
    $character[$i] = ShiyanaCharacter($character[$i]);
    switch ($character[$i]) {
      case "silver_palms":
        if (PlayerHasLessHealth($mainPlayer)) {
          AddDecisionQueue("CHARREADYORPASS", $defPlayer, $i);
          AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_draw_a_card_and_give_your_opponent_a_".CardLink("silver","silver").".", 1);
          AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
          AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
          AddDecisionQueue("PASSPARAMETER", $defPlayer, "silver", 1);
          AddDecisionQueue("PUTPLAY", $defPlayer, "0", 1);
        }
        break;
      case "barbed_castaway":
        AddCurrentTurnEffect("barbed_castaway-Load", $defPlayer);
        AddCurrentTurnEffect("barbed_castaway-Aim", $defPlayer);
        break;
      case "levia_redeemed":
        $character = GetPlayerCharacter($defPlayer);
        if ($character[1] < 3) {
          AddCurrentTurnEffect("levia_redeemed", $defPlayer);
        }
        break;
      case "victor_goldmane_high_and_mighty":
      case "victor_goldmane":
        $character = GetPlayerCharacter($defPlayer);
        if (!SearchCurrentTurnEffects($character[$i] . "-1", $defPlayer) && $character[1] < 3) AddCurrentTurnEffect($character[$i] . "-1", $defPlayer);
        break;
      case "ROGUE018":
        AddCurrentTurnEffect("ROGUE018", $mainPlayer);
        break;
      default:
        break;
    }
  }
}

function CharacterDestroyEffect($cardID, $player)
{
  switch ($cardID) {
    case "new_horizon":
      WriteLog(Cardlink($cardID, $cardID) . " destroys your arsenal");
      DestroyArsenal($player, effectController: $player);
      break;
    case "wave_of_reality":
      AddLayer("TRIGGER", $player, "wave_of_reality", "-", "-");
      break;
    case "nitro_mechanoidb":
      $weaponIndex = FindCharacterIndex($player, "nitro_mechanoida");
      if (intval($weaponIndex) != -1) DestroyCharacter($player, $weaponIndex, true);
      break;
    case "teklovossen_the_mechropotentb":
      # Add easter egg here when Teklovessen lore drops
      #WriteLog("Teklovessen lost his humanity for the greater good however as the machine shuts down he can no longer breathe.");
      include_once "./includes/dbh.inc.php";
      include_once "./includes/functions.inc.php";
      $conceded = true;
      if (!IsGameOver()) PlayerLoseHealth($player, GetHealth($player));
      break;
    case "meridian_pathway":
      SearchCurrentTurnEffects("MERIDIANWARD", $player, true);
      break;
    default:
      break;
  }
}

function CharacterBanishEffect($cardID, $player)
{
  switch ($cardID) {
    case "galvanic_bender":
      global $currentTurnEffects;
      $effectsCount = count($currentTurnEffects);
      $effectPieces = CurrentTurnEffectsPieces();
      for ($i = 0; $i < $effectsCount; $i += $effectPieces) {
        if ($currentTurnEffects[$i] == "galvanic_bender-UNDER") {
          RemoveCurrentTurnEffect($i);
          break;
        }
      }
      break;
    default:
      break;
  }
}

function MainCharacterBeginEndPhaseAbilities()
{
  global $mainPlayer, $defPlayer;
  $mainCharacter = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($mainCharacter); $i += CharacterPieces()) {
    $characterID = ShiyanaCharacter($mainCharacter[$i]);
    switch ($characterID) {
      case "terra":
        if ($mainCharacter[$i + 1] == 1) break; //Do not process ability if it is disabled (e.g. Humble)
        TerraEndPhaseAbility($characterID, $mainPlayer);
        break;
      case "arakni_marionette":
      case "arakni_web_of_deceit":
        if (CheckMarked($defPlayer) && $mainCharacter[$i + 1] < 3) ChaosTransform($characterID, $mainPlayer);
        break;
      case "arakni_black_widow":
      case "arakni_funnel_web":
      case "arakni_orbweaver":
      case "arakni_redback":
      case "arakni_tarantula":
      case "arakni_trapdoor":
        if ($mainCharacter[$i + 1] < 3) ChaosTransform($characterID, $mainPlayer);
        break;
      default:
        break;
    }
  }

  $defCharacter = &GetPlayerCharacter($defPlayer);
  for ($i = 0; $i < count($defCharacter); $i += CharacterPieces()) {
    $characterID = ShiyanaCharacter($defCharacter[$i]);
    switch ($characterID) {
      case "terra":
        if ($defCharacter[$i + 1] == 1) break; //Do not process ability if it is disabled (e.g. Humble)
        TerraEndPhaseAbility($characterID, $defPlayer);
        break;
      default:
        break;
    }
  }
}

function MainCharacterEndTurnAbilities()
{
  global $mainClassState, $CS_HitsWDawnblade, $CS_AtksWWeapon, $mainPlayer, $CS_NumNonAttackCards, $defPlayer;
  global $CS_NumAttackCards, $CS_ArcaneDamageDealt;
  $mainCharacter = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($mainCharacter); $i += CharacterPieces()) {
    $characterID = ShiyanaCharacter($mainCharacter[$i]);
    switch ($characterID) {
      case "dawnblade":
        if (GetClassState($mainPlayer, $CS_HitsWDawnblade) == 0) $mainCharacter[$i + 3] = 0;
        break;
      case "kassai_cintari_sellsword":
        if ($mainCharacter[$i + 1] == 1) break; //Do not process ability if it is disabled (e.g. Humble)
        KassaiEndTurnAbility();
        break;
      case "valiant_dynamo":
        if ($mainClassState[$CS_AtksWWeapon] >= 2 && $mainCharacter[$i + 4] < 0) ++$mainCharacter[$i + 4];
        break;
      case "duskblade":
        if (GetClassState($mainPlayer, $CS_NumNonAttackCards) == 0 || GetClassState($mainPlayer, $CS_NumAttackCards) == 0) $mainCharacter[$i + 3] = 0;
        break;
      case "spellbound_creepers":
        if (GetClassState($mainPlayer, $CS_ArcaneDamageDealt) < $mainCharacter[$i + 2]) DestroyCharacter($mainPlayer, $i);
        break;
      case "frontline_helm":
      case "frontline_plating":
      case "frontline_gauntlets":
      case "frontline_legs":
        if ($mainCharacter[$i + 1] == 0) break; //Do not add negative counters if destroyed
        if ($mainCharacter[$i + 12] != "UP") break;
        --$mainCharacter[$i + 4];
        break;
      case "ROGUE018":
        PlayAura("embodiment_of_earth", $mainPlayer);
        break;
      default:
        break;
    }
  }
}

function MainCharacterHitTrigger($cardID = "-")
{
  global $CombatChain, $combatChainState, $CCS_WeaponIndex, $mainPlayer, $chainLinks;
  $attackID = $CombatChain->AttackCard()->ID();
  $defPlayer = ($mainPlayer == 1 ? 2 : 1);
  $mainCharacter = &GetPlayerCharacter($mainPlayer);
  $isAA = ($cardID == "-" && CardType($attackID) == "AA") || (CardType($cardID) == "AA");
  $damageSource = $cardID != "-" ? $cardID : $attackID;
  for ($i = 0; $i < count($mainCharacter); $i += CharacterPieces()) {
    $characterID = ShiyanaCharacter($mainCharacter[$i], $mainPlayer);
    //tarantula and cindra should still have active triggers after using their abilities
    if (($characterID != "arakni_tarantula" && $characterID != "cindra_dracai_of_retribution" && $characterID != "cindra") && (TypeContains($mainCharacter[$i], "W", $mainPlayer) || ($mainCharacter[$i + 1] != "2"))) continue;
    switch ($characterID) {
      case "katsu_the_wanderer":
      case "katsu":
        if ($isAA) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
          $mainCharacter[$i + 1] = 1;
        }
        break;
      case "mask_of_momentum":
        $count = CountCurrentTurnEffects($characterID, $mainPlayer);
        if($mainCharacter[$i + 1] == 2 && $count <= HitsInRow() && $count <= count($chainLinks) && $count <= 3) {
          AddCurrentTurnEffect("mask_of_momentum", $mainPlayer); 
        } 
        if ($isAA && HitsInRow() >= 2) {
          while (SearchCurrentTurnEffects($characterID, $mainPlayer, true));
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
          $mainCharacter[$i + 1] = 1;
        }
        break;
      case "dorinthea_ironsong":
      case "dorinthea":
        if ($mainCharacter[$i + 1] == 2 && TypeContains($attackID, "W", $mainPlayer) && $mainCharacter[$combatChainState[$CCS_WeaponIndex] + 1] != 0) {
          $mainCharacter[$i + 1] = 1;
          $mainCharacter[$combatChainState[$CCS_WeaponIndex] + 1] = 2;
          ++$mainCharacter[$combatChainState[$CCS_WeaponIndex] + 5];
        }
        break;
      case "refraction_bolters":
        if (TypeContains($attackID, "W", $mainPlayer) && IsCharacterActive($mainPlayer, $i)) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "vest_of_the_first_fist":
        if ($isAA && IsCharacterActive($mainPlayer, $i)) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "benji_the_piercing_wind":
        if ($isAA && $mainCharacter[$i + 5] == 1) {
          AddCurrentTurnEffectFromCombat("benji_the_piercing_wind", $mainPlayer);
          $mainCharacter[$i + 5] = 0;
        }
        break;
      case "breeze_rider_boots":
        if ($isAA && ClassContains($damageSource, "NINJA", $mainPlayer) && IsCharacterActive($mainPlayer, $i)) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "briar_warden_of_thorns":
      case "briar":
        if (IsHeroAttackTarget() && $isAA && !SearchAuras("embodiment_of_earth", $mainPlayer)) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "mask_of_the_pouncing_lynx":
        if ($isAA && IsCharacterActive($mainPlayer, $i)) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "grains_of_bloodspill":
        if (TypeContains($attackID, "W", $mainPlayer) && IsCharacterActive($mainPlayer, $i)) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "aether_crackers":
        if (IsHeroAttackTarget()) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "arakni_marionette":
      case "arakni_web_of_deceit":
        if ($mainCharacter[$i+1] < 3) {
          if (IsHeroAttackTarget() && CheckMarked($defPlayer) && HasStealth($attackID) && ($cardID == "-" || $cardID == $attackID)) {
            AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
          }
        }
        break;
      case "arakni_tarantula":
        if ($mainCharacter[$i+1] < 3) {
          if (IsHeroAttackTarget() && ($cardID == "-" && SubtypeContains($attackID, "Dagger", $mainPlayer) || SubtypeContains($cardID, "Dagger", $mainPlayer))) {
            AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
          }
        }
        break;
      case "cindra_dracai_of_retribution":
      case "cindra":
      case "fang_dracai_of_blades":
      case "fang":
        if ($mainCharacter[$i+1] < 3) {
          if (IsHeroAttackTarget() && CheckMarked($defPlayer)) {
            AddLayer("TRIGGER", $mainPlayer, $characterID,$damageSource, "MAINCHARHITEFFECT");
          }
        }
        break;
      case "blood_splattered_vest":
        if ((SubtypeContains($attackID, "Dagger", $mainPlayer) || SubtypeContains($cardID, "Dagger", $mainPlayer)) && IsCharacterActive($mainPlayer, $i)) {
          AddLayer("TRIGGER", $mainPlayer, $characterID, $damageSource, "MAINCHARHITEFFECT");
        }
        break;
      case "ROGUE016":
        if (CardType($attackID) == "AA") {
          $deck = &GetDeck($mainPlayer);
          array_unshift($deck, "searing_shot_red");
        }
        break;
      case "ROGUE024":
        if (IsHeroAttackTarget()) {
          $otherPlayer = ($mainPlayer == 1 ? 2 : 1);
          DamageTrigger($otherPlayer, 1, "ATTACKHIT");
        }
        break;
      case "ROGUE028":
        if (IsHeroAttackTarget()) {
          PlayAura("spectral_shield", $mainPlayer);
          PlayAura("spectral_shield", $mainPlayer);
        }
        break;
      default:
        break;
    }
  }
}

function MainCharacterAttackModifiers(&$attackModifiers, $index = -1, $onlyBuffs = false, $player = -1)
{
  global $combatChainState, $CCS_WeaponIndex, $mainPlayer, $CombatChain;
  $modifier = 0;
  $mainCharacterEffects = &GetMainCharacterEffects($mainPlayer);
  $mainCharacter = &GetPlayerCharacter($mainPlayer);
  if ($index == -1) $index = $combatChainState[$CCS_WeaponIndex];
  for ($i = 0; $i < count($mainCharacterEffects); $i += CharacterEffectPieces()) {
    if ($player != -1 && !SearchCurrentTurnEffects(ExtractCardID($mainCharacterEffects[$i + 1]), $player)) return false;
    if ($mainCharacterEffects[$i] == $index) {
      switch ($mainCharacterEffects[$i + 1]) {
        case "steelblade_supremacy_red":
          $modifier += 2;
          array_push($attackModifiers, $mainCharacterEffects[$i + 1]);
          array_push($attackModifiers, 2);
          break;
        case "ironsong_determination_yellow":
        case "biting_blade_red":
        case "biting_blade_yellow":
        case "biting_blade_blue":
        case "cintari_saber":
        case "cintari_saber":
        case "hatchet_of_body":
        case "hatchet_of_mind":
        case "plow_through_red":
        case "plow_through_yellow":
        case "plow_through_blue":
        case "blood_on_her_hands_yellow-1":
          $modifier += 1;
          array_push($attackModifiers, $mainCharacterEffects[$i + 1]);
          array_push($attackModifiers, 1);
          break;
        default:
          break;
      }
    }
  }
  if ($onlyBuffs) return $modifier;
  $mainCharacter = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($mainCharacter); $i += CharacterPieces()) {
    if (!IsCharacterAbilityActive($mainPlayer, $i)) continue;
    $characterID = ShiyanaCharacter($mainCharacter[$i]);
    switch ($characterID) {
      case "ser_boltyn_breaker_of_dawn":
      case "boltyn":
        if (HaveCharged($mainPlayer) && NumAttacksBlocking() > 0) {
          $modifier += 1;
          array_push($attackModifiers, $characterID);
          array_push($attackModifiers, 1);
        }
        break;
      case "arakni_marionette":
      case "arakni_web_of_deceit":
        $otherPlayer = ($mainPlayer == 1 ? 2 : 1);
        if (HasStealth($CombatChain->CurrentAttack()) & CheckMarked($otherPlayer)) {
          $modifier += 1;
          array_push($attackModifiers, $characterID);
          array_push($attackModifiers, 1);
        }
        break;
      default:
        break;
    }
  }
  return $modifier;
}

function MainCharacterHitEffects()
{
  global $combatChainState, $CCS_WeaponIndex, $mainPlayer;
  $modifier = 0;
  $mainCharacterEffects = &GetMainCharacterEffects($mainPlayer);
  for ($i = 0; $i < count($mainCharacterEffects); $i += 2) {
    if ($mainCharacterEffects[$i] == $combatChainState[$CCS_WeaponIndex]) {
      switch ($mainCharacterEffects[$i + 1]) {
        case "steelblade_supremacy_red":
          AddLayer("TRIGGER", $mainPlayer, $mainCharacterEffects[$i + 1]);
          break;
        default:
          break;
      }
    }
  }
  return $modifier;
}

function MainCharacterGrantsGoAgain()
{
  global $combatChainState, $CCS_WeaponIndex, $mainPlayer;
  if ($combatChainState[$CCS_WeaponIndex] == -1) return false;
  $mainCharacterEffects = &GetMainCharacterEffects($mainPlayer);
  for ($i = 0; $i < count($mainCharacterEffects); $i += 2) {
    if ($mainCharacterEffects[$i] == $combatChainState[$CCS_WeaponIndex]) {
      switch ($mainCharacterEffects[$i + 1]) {
        case "blood_on_her_hands_yellow-2":
          return true;
        default:
          break;
      }
    }
  }
  return false;
}

function WeaponHasGoAgainLabel($index, $player)
{
  global $mainPlayer;
  $mainCharacterEffects = &GetMainCharacterEffects($mainPlayer);
  for ($i = 0; $i < count($mainCharacterEffects); $i += 2) {
    if ($mainCharacterEffects[$i] == $index) {
      if (!SearchCurrentTurnEffects(ExtractCardID($mainCharacterEffects[$i + 1]), $player)) return false;
      switch ($mainCharacterEffects[$i + 1]) {
        case "blood_on_her_hands_yellow-2":
          return true;
        default:
          break;
      }
    }
  }
  return false;
}

function CharacterCostModifier($cardID, $from, $cost)
{
  global $currentPlayer, $CS_NumSwordAttacks, $CS_NumCardsDrawn, $CS_NumSpectralShieldAttacks;
  $modifier = 0;
  $char = &GetPlayerCharacter($currentPlayer);
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    if ($char[$i + 1] >= 3 || $char[$i + 1] == 0) continue;
    if (CardType($char[$i]) == "C") $thisChar = ShiyanaCharacter($char[$i]);
    else $thisChar = $char[$i];
    switch ($thisChar) {
      case "kassai_cintari_sellsword":
        if (CardSubtype($cardID) == "Sword" && GetClassState($currentPlayer, $CS_NumSwordAttacks) == 1) --$modifier;
        break;
      case "professor_teklovossen":
        if (SubtypeContains($cardID, "Evo")) --$modifier;
        break;
      case "evo_energy_matrix_blue_equip":
        if ($cardID == "teklo_blaster") --$modifier;
        break;
      case "dash_io":
      case "dash_database":
        if ($from == "DECK" && SubtypeContains($cardID, "Item", $currentPlayer) && CardCost($cardID, $from) < 2) ++$modifier;
        break;
      case "kassai_of_the_golden_sand":
      case "kassai":
        if (CardSubtype($cardID) == "Sword" && GetClassState($currentPlayer, $CS_NumCardsDrawn) >= 1) --$modifier;
        break;
      case "nuu_alluring_desire":
      case "nuu":
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        if ($from == "THEIRBANISH" && ColorContains($cardID, 3, $otherPlayer) && (SearchCurrentTurnEffects("nuu_alluring_desire", $currentPlayer) || SearchCurrentTurnEffects("nuu", $currentPlayer))) $modifier -= $cost;
        break;
      case "enigma_ledger_of_ancestry":
      case "enigma":
        if (CardNameContains($cardID, "Spectral Shield", $currentPlayer) && GetClassState($currentPlayer, $CS_NumSpectralShieldAttacks) == 0) --$modifier;
        break;
      case "frostbite": //Jarl's frostbites
        $modifier += 1;
        AddLayer("TRIGGER", $currentPlayer, "frostbite", "-", "EQUIP", $char[$i + 11]);
        break;
      case "arakni_orbweaver":
        if (CardNameContains($cardID, "Graphene Chelicera", $currentPlayer)) --$modifier;
        break;
      case "fang_dracai_of_blades":
      case "fang": // Fang
        $fealties = SearchAurasForCardName("Fealty", $currentPlayer);
        if (SubtypeContains($cardID, "Dagger") && count(explode(",", $fealties)) >= 3) --$modifier;
        break;
       default:
        break;
    }
  }
  return CostCantBeModified($cardID) ? 0 : $modifier;
}

function EquipEquipment($player, $card, $slot = "")
{
  if ($slot == "") {
    if (SubtypeContains($card, "Head")) $slot = "Head";
    else if (SubtypeContains($card, "Chest")) $slot = "Chest";
    else if (SubtypeContains($card, "Arms")) $slot = "Arms";
    else if (SubtypeContains($card, "Legs")) $slot = "Legs";
  }
  $char = &GetPlayerCharacter($player);
  $uniqueID = GetUniqueId($card, $player);
  $replaced = 0;
  //Replace the first destroyed weapon; if none you can't re-equip
  for ($i = CharacterPieces(); $i < count($char) && !$replaced; $i += CharacterPieces()) {
    if (SubtypeContains($char[$i], $slot, $player, $uniqueID)) {
      $char[$i] = $card;
      $char[$i + 1] = 2;
      $char[$i + 2] = 0;
      $char[$i + 3] = 0;
      $char[$i + 4] = 0;
      $char[$i + 5] = 1;
      $char[$i + 6] = 0;
      $char[$i + 7] = 0;
      $char[$i + 8] = 0;
      $char[$i + 9] = CharacterDefaultActiveState($card);
      $char[$i + 10] = "-";
      $char[$i + 11] = $uniqueID;
      $char[$i + 12] = HasCloaked($card, $player);
      $char[$i + 13] = 0;
      $replaced = 1;
    }
  }
  if (!$replaced) {
    $insertIndex = count($char);
    array_splice($char, $insertIndex, 0, $card);
    array_splice($char, $insertIndex + 1, 0, 2);
    array_splice($char, $insertIndex + 2, 0, 0);
    array_splice($char, $insertIndex + 3, 0, 0);
    array_splice($char, $insertIndex + 4, 0, 0);
    array_splice($char, $insertIndex + 5, 0, 1);
    array_splice($char, $insertIndex + 6, 0, 0);
    array_splice($char, $insertIndex + 7, 0, 0);
    array_splice($char, $insertIndex + 8, 0, 0);
    array_splice($char, $insertIndex + 9, 0, 2);
    array_splice($char, $insertIndex + 10, 0, "-");
    array_splice($char, $insertIndex + 11, 0, $uniqueID);
    array_splice($char, $insertIndex + 12, 0, HasCloaked($card, $player));
    array_splice($char, $insertIndex + 13, 0, 0);
  }
  if ($card == "adaptive_plating") AddCurrentTurnEffect("adaptive_plating-" . $uniqueID . "," . $slot, $player);
  if ($card == "adaptive_dissolver") AddCurrentTurnEffect("adaptive_dissolver-" . $uniqueID . ",Base," . $slot, $player);
  if ($card == "frostbite") AddCurrentTurnEffect("frostbite-" . $uniqueID . "," . $slot, $player);
  AddEquipTrigger($card, $player);
}

function AddEquipTrigger($cardID, $player)
{
  switch ($cardID) {
    case "crown_of_dominion":
      AddLayer("TRIGGER", $player, $cardID);
      break;
    default:
      break;
  }
}

function EquipWeapon($player, $card, $source = "-")
{
  $otherPlayer = $player == 1 ? 2 : 1;
  if (SearchCurrentTurnEffects("ripple_away_blue", $player) != "" || (SearchCurrentTurnEffects("ripple_away_blue", $otherPlayer)) != "") {
    if (TypeContains($card, "T", $player, true) && (CardType($source) == "A" || CardType($source) == "AA")) {
      WriteLog("You can't equip token weapons from an action card under ripple away");
      return;
    }
  }
  $char = &GetPlayerCharacter($player);
  $lastWeapon = 0;
  $replaced = 0;
  $numHands = 0;
  $uniqueID = GetUniqueId($card, $player);
  for ($i = CharacterPieces(); $i < count($char); $i += CharacterPieces()) {
    if (TypeContains($char[$i], "W", $player) || SubtypeContains($char[$i], "Off-Hand", $player)) {
      if ($char[$i + 1] != 0) {
        if (Is1H($char[$i])) ++$numHands;
        else $numHands += 2;
      }
    }
  }
  //check if you have enough hands to equip it
  if ((Is1H($card) && $numHands < 2) || (!Is1H($card) && $numHands == 0)){
    //Replace the first destroyed weapon; if none you can't re-equip
    for ($i = CharacterPieces(); $i < count($char) && !$replaced; $i += CharacterPieces()) {
      if (TypeContains($char[$i], "W", $player) || SubtypeContains($char[$i], "Off-Hand")) {
        $lastWeapon = $i;
        if ($char[$i + 1] == 0) {
          $char[$i] = $card;
          $char[$i + 1] = 2;
          $char[$i + 2] = 0;
          $char[$i + 3] = 0;
          $char[$i + 4] = 0;
          $char[$i + 5] = 1;
          $char[$i + 6] = 0;
          $char[$i + 7] = 0;
          $char[$i + 8] = 0;
          $char[$i + 9] = CharacterDefaultActiveState($card);
          $char[$i + 10] = "-";
          $char[$i + 11] = $uniqueID;
          $char[$i + 12] = HasCloaked($card, $player);
          $char[$i + 13] = 0;
          $replaced = 1;
        }
      }
    }
  }
  if ($numHands < 2 && !$replaced) {
    $insertIndex = $lastWeapon + CharacterPieces();
    array_splice($char, $insertIndex, 0, $card);
    array_splice($char, $insertIndex + 1, 0, 2);
    array_splice($char, $insertIndex + 2, 0, 0);
    array_splice($char, $insertIndex + 3, 0, 0);
    array_splice($char, $insertIndex + 4, 0, 0);
    array_splice($char, $insertIndex + 5, 0, 1);
    array_splice($char, $insertIndex + 6, 0, 0);
    array_splice($char, $insertIndex + 7, 0, 0);
    array_splice($char, $insertIndex + 8, 0, 0);
    array_splice($char, $insertIndex + 9, 0, 2);
    array_splice($char, $insertIndex + 10, 0, "-");
    array_splice($char, $insertIndex + 11, 0, GetUniqueId($card, $player));
    array_splice($char, $insertIndex + 12, 0, HasCloaked($card, $player));
    array_splice($char, $insertIndex + 13, 0, 0);
  }
  return $uniqueID;
}

function ShiyanaCharacter($cardID, $player = "")
{
  global $currentPlayer;
  if ($player == "") $player = $currentPlayer;
  if ($cardID == "shiyana_diamond_gemini") {
    $otherPlayer = ($player == 1 ? 2 : 1);
    $otherCharacter = &GetPlayerCharacter($otherPlayer);
    if (SearchCurrentTurnEffects($otherCharacter[0] . "-SHIYANA", $player)) $cardID = $otherCharacter[0];
  }
  return $cardID;
}

function EquipPayAdditionalCosts($cardIndex, $from)
{
  global $currentPlayer, $CS_TunicTicks;
  $character = &GetPlayerCharacter($currentPlayer);
  $cardID = $character[$cardIndex];
  $cardID = ShiyanaCharacter($cardID);
  switch ($cardID) {
    case "fyendals_spring_tunic": //Tunic energy counters
      if (!ManualTunicSetting($currentPlayer) || $character[$cardIndex + 2] == 3) {
        $character[$cardIndex + 2] -= 3;
        IncrementClassState($currentPlayer, $CS_TunicTicks, 1);
      }
      break;
    case "talishar_the_lost_prince": //Talishar rust counters
      $character[$cardIndex + 1] = 1;
      ++$character[$cardIndex + 2];
      break;
    case "primeval_bellow_blue":
    case "bravo_showstopper":
    case "teklo_plasma_pistol":
    case "kano_dracai_of_aether":
    case "kano":
    case "sledge_of_anvilheim":
    case "plasma_barrel_shot":
    case "ser_boltyn_breaker_of_dawn":
    case "boltyn":
    case "shock_charmers":
    case "emperor_dracai_of_aesir":
    case "quiver_of_rustling_leaves":
    case "jinglewood_smash_hit":
    case "hidden_agenda":
      break; //Unlimited uses
    case "spellbound_creepers": //Spellbound Creepers - Bind counters
      ++$character[$cardIndex + 2];//Add a counter
      --$character[$cardIndex + 5];
      if ($character[$cardIndex + 5] == 0) $character[$cardIndex + 1] = 1;
      break;
    case "ghostly_touch": //Ghostly Touch - Haunt counters
      $character[$cardIndex + 2] -= 1;//Remove a counter
      --$character[$cardIndex + 5];
      if ($character[$cardIndex + 5] == 0) $character[$cardIndex + 1] = 1;
      break;
    case "alluvion_constellas": //Alluvion Constellas - Energy counters
      $character[$cardIndex + 2] -= 2;
      break;
    case "hanabi_blaster": //Hanabi Blaster - Steam counters, once per turn
      $character[$cardIndex + 2] -= 2;
      $character[$cardIndex + 1] = 1;
      break;
    case "nitro_mechanoida":
      --$character[$cardIndex + 2];
      break;
    case "barkbone_strapping":
    case "helm_of_isens_peak":
    case "breaking_scales":
    case "hope_merchants_hood":
    case "heartened_cross_strap":
    case "goliath_gauntlet":
    case "snapdragon_scalers":
    case "achilles_accelerator":
    case "bulls_eye_bracers":
    case "crown_of_dichotomy":
    case "storm_striders":
    case "robe_of_rapture":
    case "talismanic_lens":
    case "bracers_of_belief":
    case "mage_master_boots":
    case "skullhorn":
    case "crater_fist":
    case "courage_of_bladehold":
    case "viziertronic_model_i":
    case "perch_grapplers":
    case "bloodsheath_skeleta":
    case "halo_of_illumination":
    case "dream_weavers":
    case "gallantry_gold":
    case "ebon_fold":
    case "aether_ironweave":
    case "blood_drop_brocade":
    case "stubby_hammerers":
    case "time_skippers":
    case "plume_of_evergrowth":
    case "coat_of_frost":
    case "honing_hood":
    case "sutcliffes_suede_hides":
    case "ragamuffins_hat":
    case "deep_blue":
    case "cracker_jax":
    case "runaways":
    case "helm_of_sharp_eye":
    case "vexing_quillhand":
    case "crown_of_reflection":
    case "blossom_of_spring":
    case "gallantry_gold":
    case "blossom_of_spring":
    case "silken_form":
    case "heat_wave":
    case "sash_of_sandikai":
    case "conduit_of_frostburn":
    case "glacial_horns":
    case "tide_flippers":
    case "spellfire_cloak":
    case "tearing_shuko":
    case "blacktek_whisperers":
    case "mask_of_perdition":
    case "amethyst_tiara":
    case "ornate_tessen":
    case "redback_shroud":
    case "mask_of_many_faces":
    case "quiver_of_abyssal_depths":
    case "driftwood_quiver":
    case "mask_of_shifting_perspectives":
    case "blade_cuff":
    case "mask_of_malicious_manifestations":
    case "toxic_tips":
    case "seekers_hood":
    case "seekers_gilet":
    case "seekers_mitts":
    case "seekers_leggings":
    case "silken_gi":
    case "threadbare_tunic":
    case "fisticuffs":
    case "fleet_foot_sandals":
    case "mask_of_three_tails":
    case "pouncing_paws":
    case "shriek_razors":
    case "warband_of_bellona":
    case "nom_de_plume":
    case "heartthrob":
    case "fiddledee":
    case "quickstep":
    case "blood_scent":
    case "knucklehead":
    case "monstrous_veil":
    case "prized_galea":
    case "gauntlet_of_might":
    case "flat_trackers":
    case "vigor_girth":
    case "balance_of_justice":
    case "glory_seeker":
    case "sheltered_cove":
    case "savage_sash":
    case "arousing_wave":
    case "undertow_stilettos":
    case "twelve_petal_kasaya":
    case "target_totalizer":
    case "sharp_shooters":
    case "flight_path":
    case "heavy_industry_gear_shift":
    case "blossom_of_spring":
    case "well_grounded":
    case "harvest_season_blue":
    case "lightning_greaves":
    case "twinkle_toes":
    case "bloodtorn_bodice":
    case "runehold_release":
    case "aether_bindings_of_the_third_age":
    case "inklined_cloak":
    case "hold_focus":
    case "hood_of_second_thoughts": 
    case "bruised_leather": 
    case "four_finger_gloves":
    case "calming_cloak":
    case "calming_gesture":
    case "heavy_industry_power_plant":
    case "dragonscaler_flight_path":
    case "vow_of_vengeance":
    case "heart_of_vengeance":
    case "hand_of_vengeance":
    case "path_of_vengeance":
    case "coat_of_allegiance":
    case "danger_digits":
    case "starting_point":
    case "bunker_beard":
    case "tremorshield_sabatons":
    case "misfire_dampener":
    case "enchanted_quiver":
    case "magrar":
      DestroyCharacter($currentPlayer, $cardIndex);
      break;
    case "prism_awakener_of_sol":
    case "prism_advent_of_thrones":
      BanishFromSoul($currentPlayer);
      --$character[$cardIndex + 5];
      break;
    case "radiant_view":
    case "radiant_raiment":
    case "radiant_touch":
    case "radiant_flow":
      $char = new Character($currentPlayer, $cardIndex);
      $char->status = 0;
      BanishCardForPlayer($char->cardID, $currentPlayer, "EQUIP", "NA");
      $char->Finished();
      BanishFromSoul($currentPlayer);
      break;
    case "spoiled_skull":
      $char = new Character($currentPlayer, $cardIndex);
      $char->status = 0;
      BanishCardForPlayer($char->cardID, $currentPlayer, "EQUIP", "NA");
      $char->Finished();
      break;
    case "flail_of_agony":
      LoseHealth(1, $currentPlayer);
      --$character[$cardIndex + 5];
      if ($character[$cardIndex + 5] == 0) $character[$cardIndex + 1] = 1; //By default, if it's used, set it to used
      break;
    case "grimoire_of_the_haunt":
      BanishCardForPlayer("grimoire_of_the_haunt", $currentPlayer, "EQUIP", "NA");
      DestroyCharacter($currentPlayer, $cardIndex, true);
      break;
    case "symbiosis_shot":
      $character[$cardIndex + 2] -= 1;
      break;
    case "cogwerx_base_head":
    case "cogwerx_base_chest":
    case "cogwerx_base_arms":
    case "cogwerx_base_legs":
      $character[$cardIndex + 2] = 0;
      break;
    case "evo_command_center_yellow_equip":
    case "evo_engine_room_yellow_equip":
    case "evo_smoothbore_yellow_equip":
    case "evo_thruster_yellow_equip":
    case "evo_data_mine_yellow_equip":
    case "evo_battery_pack_yellow_equip":
    case "evo_cogspitter_yellow_equip":
    case "evo_charging_rods_yellow_equip":
      --$character[$cardIndex + 5];
      if ($character[$cardIndex + 5] == 0) $character[$cardIndex + 1] = 1; //By default, if it's used, set it to used
      break;
    case "good_time_chapeau":
      $index = GetItemIndex("gold", $currentPlayer);
      if ($index != -1) DestroyItemForPlayer($currentPlayer, $index);
      else {
        $charIndex = FindCharacterIndex($currentPlayer, "aurum_aegis");
        if ($charIndex != -1) DestroyCharacter($currentPlayer, $charIndex);
      }
      break;
    case "hood_of_red_sand":
      DestroyCharacter($currentPlayer, $cardIndex);
      break;
    case "truths_retold":
    case "uphold_tradition":
    case "aqua_seeing_shell":
    case "waves_of_aqua_marine":
    case "aqua_laps":
      $character[$cardIndex + 12] = "UP";
      break;
    case "skycrest_keikoi":
    case "skybody_keikoi":
    case "skyhold_keikoi":
    case "skywalker_keikoi":
      $character[$cardIndex + 12] = "UP";
      DestroyCharacter($currentPlayer, $cardIndex);
      break;
    case "longdraw_halfglove":
      DestroyCharacter($currentPlayer, $cardIndex, true);
      break;
    case "solar_plexus":
      DestroyCharacter($currentPlayer, $cardIndex);
      BanishFromSoul($currentPlayer);
      break;
    default:
      --$character[$cardIndex + 5];
      if ($character[$cardIndex + 5] == 0) $character[$cardIndex + 1] = 1; //By default, if it's used, set it to used
      break;
  }
}

function CharacterModifiesPlayAura($player, $isToken, $effectController)
{
  $char = &GetPlayerCharacter($player);
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    if (intval($char[$i + 1]) != 2) continue;
    switch ($char[$i]) {
      case "florian_rotwood_harbinger":
        if (!$isToken || ($effectController != $player)) return 0;
        // Now we need to check that we banished 8 earth cards.
        $results = SearchCount(SearchMultiZone($player, "MYBANISH:talent=EARTH"));
        if ($results >= 8) {
          WriteLog(CardLink($char[$i], $char[$i]) . " increases the number of auras tokens created by 1.");
          return 1;
        }
        return 0;
      case "florian":
        if (!$isToken || ($effectController != $player)) return 0;
        // Now we need to check that we banished 4 earth cards.
        $results = SearchCount(SearchMultiZone($player, "MYBANISH:talent=EARTH"));
        if ($results >= 4) {
          WriteLog(CardLink($char[$i], $char[$i]) . " increases the number of auras tokens created by 1.");
          return 1;
        }
        return 0;
      default:
        return 0;
    }
  }
}

function CharacterTakeDamageAbilities($player, $damage, $type, $preventable)
{
  global $CS_NumCharged;
  $char = &GetPlayerCharacter($player);
  $otherPlayer = $player == 1 ? 2 : 1;
  $preventedDamage = 0;
  for ($i = count($char) - CharacterPieces(); $i >= 0; $i -= CharacterPieces()) {
    if ($char[$i + 1] == 0) continue;
    switch ($char[$i]) {
      case "soulbond_resolve":
        if ($damage > 0 && $preventable && $char[$i + 5] > 0 && GetClassState($player, $CS_NumCharged) > 0) {
          if(SearchCurrentTurnEffects("soulbond_resolve", $player, true)){
            ++$preventedDamage;
            --$char[$i + 5];
          }
        }
        break;
      case "shroud_of_darkness":
      case "cloak_of_darkness":
      case "grasp_of_darkness":
      case "dance_of_darkness":
        if ($char[$i + 9] == 0) break;
        if ($damage > 0) {
          if ($preventable) $preventedDamage += 2;
          BanishCardForPlayer($char[$i], $player, "PLAY");
          DestroyCharacter($player, $i, skipDestroy: true);
        }
        break;
      default:
        break;
    }
    if ($preventedDamage > 0 && SearchCurrentTurnEffects("vambrace_of_determination", $player) != "") {
      $preventedDamage -= 1;
      SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
    }
  }
  $damage -= $preventedDamage;
  return $damage > 0 ? $damage : 0;
}

function CharacterDamageTakenAbilities($player, $damage)
{
  $char = &GetPlayerCharacter($player);
  for ($i = count($char) - CharacterPieces(); $i >= 0; $i -= CharacterPieces()) {
    if ($char[$i + 1] != 2) continue;
    switch ($char[$i]) {
      case "ROGUE015":
        $hand = &GetHand($player);
        for ($j = 0; $j < $damage; ++$j) {
          $randomNimb = rand(1, 3);
          if ($randomNimb == 1) array_unshift($hand, "nimblism_red");
          else if ($randomNimb == 2) array_unshift($hand, "nimblism_yellow");
          else array_unshift($hand, "nimblism_blue");
        }
        break;
      case "ROGUE019":
        PlayAura("zen_state", $player, 4, false, true);
        break;
      default:
        break;
    }
  }
}

function CharacterAttackDestroyedAbilities($attackID)
{
  global $mainPlayer;
  $character = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] == 0) continue;
    switch ($character[$i]) {
      case "phantasmal_footsteps":
        if ($character[$i + 5] > 0 && CardType($attackID) == "AA" && ClassContains($attackID, "ILLUSIONIST", $mainPlayer)) {
          AddDecisionQueue("ADDTRIGGER", $mainPlayer, $character[$i], $i);
          --$character[$i + 5];
        }
        break;
      case "silent_stilettos":
        $hand = &GetHand($mainPlayer);
        $resources = &GetResources($mainPlayer);
        if (Count($hand) > 0 || $resources[0] > 0) {
          AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_pay_3_to_gain_an_action_point", 0, 1);
          AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, 3, 1);
          AddDecisionQueue("PAYRESOURCES", $mainPlayer, "<-", 1);
          AddDecisionQueue("GAINACTIONPOINTS", $mainPlayer, "1", 1);
          AddDecisionQueue("FINDINDICES", $mainPlayer, "EQUIPCARD,silent_stilettos", 1);
          AddDecisionQueue("DESTROYCHARACTER", $mainPlayer, "-", 1);
        }
        break;
      default:
        break;
    }
  }
}

function CharacterPlayCardAbilities($cardID, $from)
{
  global $currentPlayer, $CS_NumLess3PowAAPlayed, $CS_NumAttacks;
  $character = &GetPlayerCharacter($currentPlayer);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] != 2) continue;
    $characterID = ShiyanaCharacter($character[$i]);
    switch ($characterID) {
      case "tiger_stripe_shuko"://Tiger Stripe Shuko
        if (GetClassState($currentPlayer, $CS_NumLess3PowAAPlayed) == 2 && AttackValue($cardID) <= 2) {
          AddCurrentTurnEffect($characterID, $currentPlayer);
          $character[$i + 1] = 1;
        }
        break;
      case "ira_crimson_haze":
      case "ROGUE008":
      case "ira_scarlet_revenger":
        if (GetClassState($currentPlayer, $CS_NumAttacks) == 2) {
          AddCurrentTurnEffect($characterID, $currentPlayer);
          $character[$i + 1] = 1;
        }
        break;
      case "ROGUE025":
        $resources = &GetResources($currentPlayer);
        ++$resources[0];
        break;
      case "melody_singalong"://Melody, Sing-Along
        if (SubtypeContains($cardID, "Song", $currentPlayer)) PutItemIntoPlayForPlayer("copper", $currentPlayer);
        break;
      default:
        break;
    }
  }
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $otherCharacter = &GetPlayerCharacter($otherPlayer);
  for ($i = 0; $i < count($otherCharacter); $i += CharacterPieces()) {
    $characterID = $otherCharacter[$i];
    switch ($characterID) {
      case "ROGUE026":
        if (CardType($cardID) != "W" && CardType($cardID) != "E") {
          $generatedAmount = CardCost($cardID, $from);
          if ($generatedAmount < 1) $generatedAmount = 1;
          for ($j = 0; $j < $generatedAmount; ++$j) {
            PutItemIntoPlayForPlayer("gold", $currentPlayer, effectController: $currentPlayer);
          }
        }
        break;
      default:
        break;
    }
  }
}

function MainCharacterPlayCardAbilities($cardID, $from)
{
  global $currentPlayer, $mainPlayer, $CS_NumNonAttackCards, $CS_NumBoostPlayed;
  $character = &GetPlayerCharacter($currentPlayer);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    // don't turn off hanabi blaster counting if it has fired that turn
    if ($character[$i + 1] != 2 && $character[$i] != "hanabi_blaster") continue;
    $characterID = ShiyanaCharacter($character[$i]);
    switch ($characterID) {
      case "viserai_rune_blood":
      case "viserai": //Viserai
        if (!IsStaticType(CardType($cardID), $from, $cardID) && ClassContains($cardID, "RUNEBLADE", $currentPlayer) && !TypeContains($cardID, "B", $currentPlayer)) {
          AddLayer("TRIGGER", $currentPlayer, $characterID, $cardID);
        }
        break;
      case "metacarpus_node":
        if ((ActionsThatDoArcaneDamage($cardID, $currentPlayer) || ActionsThatDoXArcaneDamage($cardID)) && SearchCharacterActive($currentPlayer, "metacarpus_node", checkGem: true) && GetResolvedAbilityType($cardID) != "I") AddLayer("TRIGGER", $currentPlayer, "metacarpus_node");
        break;
      case "briar_warden_of_thorns":
      case "briar":
        if (DelimStringContains(CardType($cardID), "A") && GetClassState($currentPlayer, $CS_NumNonAttackCards) == 2 && $from != "PLAY") {
          AddLayer("TRIGGER", $currentPlayer, $characterID);
        }
        break;
      case "iyslander":
      case "iyslander_stormbind":
      case "iyslander": //Iyslander
        if ($currentPlayer != $mainPlayer && TalentContains($cardID, "ICE", $currentPlayer) && !IsStaticType(CardType($cardID), $from, $cardID)) {
          AddLayer("TRIGGER", $currentPlayer, $characterID);
        }
        break;
      case "hanabi_blaster":
        $numBoostPlayed = 0;
        if (HasBoost($cardID, $currentPlayer)) {
          $numBoostPlayed = GetClassState($currentPlayer, $CS_NumBoostPlayed) + 1;
          SetClassState($currentPlayer, $CS_NumBoostPlayed, $numBoostPlayed);
        }
        if ($numBoostPlayed == 3) {
          $index = FindCharacterIndex($currentPlayer, "hanabi_blaster");
          ++$character[$index + 2];
        }
        break;
      case "arakni_huntsman":
      case "arakni":
        if (ContractType($cardID) != "") AddLayer("TRIGGER", $currentPlayer, $characterID);
        break;
      case "riptide_lurker_of_the_deep":
      case "riptide": //Riptide
        if ($from == "HAND" && GetResolvedAbilityName($cardID, "HAND") != "Ability") {
          AddLayer("TRIGGER", $currentPlayer, $characterID, $cardID);
        }
        break;
      case "vynnset_iron_maiden":
      case "vynnset": //Vynnset
        if (CardType($cardID) == "A" && TalentContains($cardID, "SHADOW", $currentPlayer)) {
          AddLayer("TRIGGER", $currentPlayer, $characterID, $cardID);
        }
        break;
      case "hard_knuckle": // Hard Knuckle
        if (CardType($cardID) == "AA") {
          AddLayer("TRIGGER", $currentPlayer, $characterID, $cardID);
        }
        break;
      case "dash_io":
      case "dash_database":
        if ($from == "DECK") {
          --$character[$i + 1];
          --$character[$i + 5];
        }
        break;
      case "jarl_vetreidi":
        if (TalentContains($cardID, "ICE", $currentPlayer) && !IsStaticType(CardType($cardID), $from, $cardID)) {
          AddLayer("TRIGGER", $currentPlayer, $characterID);
        }
        break;
      case "ROGUE017":
        if (CardType($cardID) == "AA") {
          $deck = &GetDeck($currentPlayer);
          array_unshift($deck, $cardID);
          AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
        }
        break;
      case "ROGUE003":
        if (CardType($cardID) == "AA") {
          $deck = &GetDeck($currentPlayer);
          AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
        }
        break;
      case "ROGUE019":
        if ($cardID == "soulbead_strike_red" || $cardID == "soulbead_strike_yellow" || $cardID == "soulbead_strike_blue") {
          $choices = array("crane_dance_red", "crane_dance_yellow", "crane_dance_blue");
          $hand = &GetHand($currentPlayer);
          array_unshift($hand, $choices[rand(0, count($choices) - 1)]);
        } else if ($cardID == "crane_dance_red" || $cardID == "crane_dance_yellow" || $cardID == "crane_dance_blue") {
          $choices = array("find_center_blue", "herons_flight_red");
          $hand = &GetHand($currentPlayer);
          array_unshift($hand, $choices[rand(0, count($choices) - 1)]);
        }
        break;
      case "ROGUE031":
        global $actionPoints;
        if (CardTalent($cardID) == "LIGHTNING") {
          $actionPoints++;
        }
        break;
      default:
        break;
    }
  }
  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  $otherPlayerCharacter = &GetPlayerCharacter($otherPlayer);
  for ($j = 0; $j < count($otherPlayerCharacter); $j += CharacterPieces()) {
    if ($otherPlayerCharacter[$j + 1] != 2) continue;
    switch ($otherPlayerCharacter[$j]) {
      default:
        break;
    }
  }
}

function CharacterDealDamageAbilities($player, $damage)
{
  $char = &GetPlayerCharacter($player);
  for ($i = count($char) - CharacterPieces(); $i >= 0; $i -= CharacterPieces()) {
    if ($char[$i + 1] != 2) continue;
    switch ($char[$i]) {
      case "ROGUE023":
        if ($damage >= 4) {
          PlayAura("towering_titan_blue", $player, 1, false, true);
        }
        break;
      case "ROGUE029":
        for ($j = count($char) - CharacterPieces(); $j >= 0; $j -= CharacterPieces()) {
          if ($char[$j] == "merciless_battleaxe") $indexCounter = $j + 3;
        }
        $char[$indexCounter] += 1;
        if ($damage >= 4) {
          $char[$indexCounter] = $char[$indexCounter] * 2;
        }
        break;
      default:
        break;
    }
  }
}

function CharacterAttackAbilities($attackID)
{
  global $mainPlayer, $combatChainState, $CCS_LinkBaseAttack, $CS_PlayIndex;
  $char = &GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    if ($char[$i + 1] == 0) continue;//Don't do effect if destroyed
    switch ($char[$i]) {
      case "evo_scatter_shot_blue_equip":
        if ($attackID == "teklo_blaster") {
          AddCurrentTurnEffect($char[$i], $mainPlayer);
          WriteLog("Evo Scatter Shot gives +1");
        }
        break;
      case "evo_rapid_fire_blue_equip":
        if ($attackID == "teklo_blaster") {
          GiveAttackGoAgain();
          WriteLog("Evo Rapid Fire gives Go Again");
        }
        break;
      case "cosmo_scroll_of_ancestral_tapestry":
        if (HasWard($attackID, $mainPlayer) && SubtypeContains($attackID, "Aura", $mainPlayer)) {
          $combatChainState[$CCS_LinkBaseAttack] = WardAmount($attackID, $mainPlayer, GetClassState($mainPlayer, $CS_PlayIndex));
        }
        break;
      default:
        break;
    }
  }
}

function GetCharacterGemState($player, $cardID)
{
  $char = &GetPlayerCharacter($player);
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    if ($char[$i] == $cardID) return $char[$i + 9];
  }
  return 0;
}

function CharacterBoostAbilities($player)
{
  $char = &GetPlayerCharacter($player);
  for ($i = 0; $i < count($char); $i += CharacterPieces()) {
    if (intval($char[$i + 1]) < 2) continue;
    switch ($char[$i]) {
      case "evo_circuit_breaker_red_equip":
        if ($char[$i + 9] == 1 && EvoHasUnderCard($player, $i)) {
          MZMoveCard($player, "MYBANISH:type=AA", "MYTOPDECK", may: false);
          MZMoveCard($player, "MYBANISH:type=AA", "MYTOPDECK", may: false);
          AddDecisionQueue("SHUFFLEDECK", $player, "-");
          CharacterChooseSubcard($player, $i, fromDQ: false);
          AddDecisionQueue("ADDDISCARD", $player, "-", 1);
        }
        break;
      case "evo_atom_breaker_red_equip":
        if ($char[$i + 9] == 1 && EvoHasUnderCard($player, $i)) {
          GainResources($player, 2);
          CharacterChooseSubcard($player, $i, fromDQ: false);
          AddDecisionQueue("ADDDISCARD", $player, "-", 1);
        }
        break;
      case "evo_face_breaker_red_equip":
        if ($char[$i + 9] == 1 && EvoHasUnderCard($player, $i)) {
          AddCurrentTurnEffect($char[$i], $player);
          CharacterChooseSubcard($player, $i, fromDQ: false);
          AddDecisionQueue("ADDDISCARD", $player, "-", 1);
        }
        break;
      case "evo_mach_breaker_red_equip":
        if ($char[$i + 9] == 1 && EvoHasUnderCard($player, $i)) {
          PlayAura("quicken", $player);
          CharacterChooseSubcard($player, $i, fromDQ: false);
          AddDecisionQueue("ADDDISCARD", $player, "-", 1);
        }
        break;
      default:
        break;
    }
  }
}
