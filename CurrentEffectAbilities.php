<?php


//Return 1 if the effect should be removed
function EffectHitEffect($cardID, $from, $source = "-")
{
  global $combatChainState, $CCS_GoesWhereAfterLinkResolves, $defPlayer, $mainPlayer, $CCS_WeaponIndex, $CombatChain, $CCS_DamageDealt;
  global $CID_BloodRotPox, $CID_Frailty, $CID_Inertia, $Card_LifeBanner, $Card_ResourceBanner, $layers;
  $attackID = $CombatChain->AttackCard()->ID();
  if ($source == "-") {
    if (CardType($attackID) == "AA" && SearchCurrentTurnEffects("OUT108", $mainPlayer, count($layers) < LayerPieces())) {
      WriteLog("Hit effect prevented by " . CardLink("OUT108", "OUT108"));
      return true;
    }
  }
  else if (CardType($source) == "AA" && SearchCurrentTurnEffects("OUT108", $mainPlayer, count($layers) < LayerPieces())) {
    WriteLog("Hit effect prevented by " . CardLink("OUT108", "OUT108"));
    return true;
  }
  $effectArr = explode(",", $cardID);
  $cardID = $effectArr[0];
  switch ($cardID) {
    case "WTR129":
    case "WTR130":
    case "WTR131":
      GiveAttackGoAgain();
      break;
    case "WTR147":
    case "WTR148":
    case "WTR149":
      NaturesPathPilgrimageHit();
      break;
    case "WTR206":
    case "WTR207":
    case "WTR208":
      if (IsHeroAttackTarget() && CardType($attackID) == "AA") PummelHit();
      break;
    case "WTR209":
    case "WTR210":
    case "WTR211":
      if (CardType($attackID) == "AA") GiveAttackGoAgain();
      break;
    case "ARC170-1":
    case "ARC171-1":
    case "ARC172-1":
      Draw($mainPlayer);
      return 1;
    case "CRU124":
      if (IsHeroAttackTarget()) PummelHit();
      break;
    case "CRU145":
    case "CRU146":
    case "CRU147":
      if (ClassContains($attackID, "RUNEBLADE", $mainPlayer)) {
        if ($cardID == "CRU145") $amount = 3;
        else if ($cardID == "CRU146") $amount = 2;
        else $amount = 1;
        PlayAura("ARC112", $mainPlayer, $amount);
      }
      break;
    case "ROS119":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want your opponent to discard", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZDISCARD", $mainPlayer, "HAND," . $mainPlayer, 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      return 1;
    case "CRU084-2":
      PutItemIntoPlayForPlayer("CRU197", $mainPlayer, 0, 2);
      break;
    case "MON034":
      $deck = new Deck($mainPlayer);
      if (!$deck->Reveal()) return;
      $top = $deck->Top(remove: true);
      if (TalentContains($top, "LIGHT", $mainPlayer)) {
        AddSoul($top, $mainPlayer, "DECK");
        GainHealth(1, $mainPlayer);
      } else $deck->AddBottom($top, "DECK");
      break;
    case "MON081":
    case "MON082":
    case "MON083":
      $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL";
      break;
    case "MON110":
    case "MON111":
    case "MON112":
      DuskPathPilgrimageHit();
      break;
    case "MON193":
      ShadowPuppetryHitEffect();
      break;
    case "MON218":
      if (count(GetSoul($defPlayer)) > 0) {
        BanishFromSoul($defPlayer);
        LoseHealth(1, $defPlayer);
      }
      break;
    case "MON299":
    case "MON300":
    case "MON301":
      if (substr($from, 0, 5) != "THEIR") $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BOTDECK";
      else $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "THEIRBOTDECK";
      break;
    case "ELE005":
      if (IsHeroAttackTarget()) {
        $hand = &GetHand($defPlayer);
        $cards = "";
        for ($i = 0; $i < 2 && count($hand) > 0; ++$i) {
          $index = GetRandom() % count($hand);
          if ($cards != "") $cards .= ",";
          $cards .= $hand[$index];
          unset($hand[$index]);
          $hand = array_values($hand);
        }
        if ($cards != "") AddDecisionQueue("CHOOSEBOTTOM", $defPlayer, $cards);
      }
      break;   
    case "ELE019":
    case "ELE020":
    case "ELE021":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to put on the bottom of the deck", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZADDZONE", $mainPlayer, "THEIRBOTDECK", 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    case "ELE022":
    case "ELE023":
    case "ELE024":
      if (IsHeroAttackTarget()) PlayAura("ELE111", $defPlayer, effectController: $mainPlayer);
      break;
    case "ELE035-2":
      if (IsHeroAttackTarget()) {
        AddCurrentTurnEffect("ELE035-3", $defPlayer);
        AddNextTurnEffect("ELE035-3", $defPlayer);
      }
      break;
    case "ELE037-2":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", "ELE037");
      break;
    case "ELE047":
    case "ELE048":
    case "ELE049":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", $cardID);
      break;
    case "ELE066-TRIGGER":
      if (HasIncreasedAttack()) Draw($mainPlayer);
      break;
    case "ELE092-BUFF":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 3, "ATTACKHIT", $cardID);
      break;
    case "ELE151-HIT":
    case "ELE152-HIT":
    case "ELE153-HIT":
      if (IsHeroAttackTarget()) PlayAura("ELE111", $defPlayer, effectController: $mainPlayer);
      break;
    case "ELE163":
      if (IsHeroAttackTarget()) PlayAura("ELE111", $defPlayer, 3, effectController: $mainPlayer);
      break;
    case "ELE164":
      if (IsHeroAttackTarget()) PlayAura("ELE111", $defPlayer, 2, effectController: $mainPlayer);
      break;
    case "ELE165":
      if (IsHeroAttackTarget()) PlayAura("ELE111", $defPlayer, effectController: $mainPlayer);
      break;
    case "ELE173":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", $cardID);
      return 1;
    case "ELE195":
    case "ELE196":
    case "ELE197":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", $cardID);
      break;
    case "ELE198":
    case "ELE199":
    case "ELE200":
      if (IsHeroAttackTarget()) {
        if ($cardID == "ELE198") $damage = 3;
        else if ($cardID == "ELE199") $damage = 2;
        else $damage = 1;
        DamageTrigger($defPlayer, $damage, "ATTACKHIT", $cardID);
        return 1;
      }
      break;
    case "ELE205":
      if (IsHeroAttackTarget()) {
        PummelHit();
        PummelHit();
      }
      break;
    case "ELE215":
      if (IsHeroAttackTarget()) {
        AddNextTurnEffect($cardID . "-1", $defPlayer);
      }
      break;
    case "EVR047-1":
    case "EVR048-1":
    case "EVR049-1":
      $idArr = explode("-", $cardID);
      AddCurrentTurnEffectFromCombat($idArr[0] . "-2", $mainPlayer);
      break;
    case "EVR066-1":
    case "EVR067-1":
    case "EVR068-1":
      PutItemIntoPlayForPlayer("CRU197", $mainPlayer);
      return 1;
    case "EVR161-1":
    case "EVR162-1":
    case "EVR163-1":
      GainHealth(2, $mainPlayer);
      break;
    case "EVR164":
    case "EVR165":
    case "EVR166":
      if ($cardID == "EVR164") $amount = 6;
      else if ($cardID == "EVR165") $amount = 4;
      else $amount = 2;
      PutItemIntoPlayForPlayer("CRU197", $mainPlayer, 0, $amount);
      return 1;
    case "EVR170-1":
    case "EVR171-1":
    case "EVR172-1":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS:minCost=0;maxCost=2");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
        return 1;
      }
      break;
    case "DVR008-1":
      $char = &GetPlayerCharacter($mainPlayer);
      if (IsHeroAttackTarget()) {
        ++$char[$combatChainState[$CCS_WeaponIndex] + 3];
      }
      break;
    case "DYN028":
      if(IsHeroAttackTarget()) Mangle();
      break;
    case "DYN071":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRALLY", 1);
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a target to deal " . $combatChainState[$CCS_DamageDealt] . " damage.");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZDAMAGE", $mainPlayer, $combatChainState[$CCS_DamageDealt] . ",DAMAGE," . $cardID, 1);
      break;
    case "DYN155":
      if (IsHeroAttackTarget() && HasAimCounter()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want your opponent to discard", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDISCARD", $mainPlayer, "HAND," . $mainPlayer, 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    case "DYN185-HIT":
    case "DYN186-HIT":
    case "DYN187-HIT":
      if (ClassContains($attackID, "RUNEBLADE", $mainPlayer)) {
        if ($cardID == "DYN185-HIT") $amount = 3;
        else if ($cardID == "DYN186-HIT") $amount = 2;
        else $amount = 1;
        PlayAura("ARC112", $mainPlayer, $amount, true);
      }
      break;
    case "OUT021":
      if (IsHeroAttackTarget()) PlayAura($CID_BloodRotPox, $defPlayer, effectController: $mainPlayer);
      break;
    case "OUT022":
      if (IsHeroAttackTarget()) PlayAura($CID_Frailty, $defPlayer, effectController: $mainPlayer);
      break;
    case "OUT023":
      if (IsHeroAttackTarget()) PlayAura($CID_Inertia, $defPlayer, effectController: $mainPlayer);
      break;
    case "OUT105":
      if (IsHeroAttackTarget() && HasAimCounter()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRCHAR:minAttack=1;maxAttack=1;type=W;is1h=true");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a weapon to destroy");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
      }
      break;
    case "OUT112":
      if (IsHeroAttackTarget()) PlayAura($CID_BloodRotPox, $defPlayer, effectController: $mainPlayer);
      break;
    case "OUT113":
      if (IsHeroAttackTarget()) PlayAura($CID_Frailty, $defPlayer, effectController: $mainPlayer);
      break;
    case "OUT114":
      if (IsHeroAttackTarget()) PlayAura($CID_Inertia, $defPlayer, effectController: $mainPlayer);
      break;
    case "OUT140":
      WriteLog("Mask of Shifting Perspectives lets you sink a card");
      BottomDeck($mainPlayer, true, shouldDraw: true);
      break;
    case "OUT143":
      $weapons = "";
      $char = &GetPlayerCharacter($mainPlayer);
      $inventory = &GetInventory($mainPlayer);
      if ($char[CharacterPieces() + 1] == 0 || $char[CharacterPieces() * 2 + 1] == 0) { //Only Equip if there is a broken weapon/off-hand
        foreach ($inventory as $cardID) {
          if (TypeContains($cardID, "W", $mainPlayer) && SubtypeContains($cardID, "Dagger")) {
            if ($weapons != "") $weapons .= ",";
            $weapons .= $cardID;
          };
        }
        if ($weapons == "") {
          WriteLog("Player " . $mainPlayer . " doesn't have any dagger in their inventory");
          return;
        }
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a dagger to equip");
        AddDecisionQueue("CHOOSECARD", $mainPlayer, $weapons);
        AddDecisionQueue("APPENDLASTRESULT", $mainPlayer, "-INVENTORY");
        AddDecisionQueue("EQUIPCARDINVENTORY", $mainPlayer, "<-");
      }
      break;
    case "OUT158":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("CHOOSECARD", $mainPlayer, $CID_BloodRotPox . "," . $CID_Frailty . "," . $CID_Inertia);
        AddDecisionQueue("PUTPLAY", $defPlayer, $mainPlayer, 1);
      }
      break;
    case "OUT165":
      LoseHealth(5, $defPlayer);
      break;
    case "OUT166":
      LoseHealth(4, $defPlayer);
      break;
    case "OUT167":
      LoseHealth(3, $defPlayer);
      break;
    case "OUT188_1":
      if (IsHeroAttackTarget()) {
        PlayAura("DYN244", $mainPlayer);
        return 1;
      }
      break;
    case "DTD051":
      MZMoveCard($mainPlayer, "MYDISCARD:type=AA", "MYTOPDECK", may: true);
      break;
    case "DTD052":
      PlayAura("DTD232", $mainPlayer);
      break;
    case "DTD066":
    case "DTD067":
    case "DTD068":
      GiveAttackGoAgain();
      break;
    case "DTD080-2":
      Draw($mainPlayer);
      break;
    case "DTD080-3":
      GiveAttackGoAgain();
      break;
    case "DTD207":
      if (IsHeroAttackTarget()) PlayAura("DTD232", $mainPlayer);
      break;
    case $Card_LifeBanner:
      GainHealth(1, $mainPlayer);
      return 1;
    case $Card_ResourceBanner:
      GainResources($mainPlayer, 1);
      return 1;
    case "DTD229-HIT":
      if (IsHeroAttackTarget()) {
        MZChooseAndDestroy($mainPlayer, "THEIRAURAS:type=A;maxCost=" . $combatChainState[$CCS_DamageDealt] . "&THEIRAURAS:type=I;maxCost=" . $combatChainState[$CCS_DamageDealt]);
        return 1;
      }
      break;
    case "EVO155":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to take");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "GAINCONTROL", 1);
      }
      break;
    case "EVO434":
      Draw($mainPlayer);
      break;
    case "HVY090":
    case "HVY091":
      if (IsHeroAttackTarget()) PutItemIntoPlayForPlayer("DYN243", $mainPlayer, effectController: $mainPlayer);
      return 1;
    case "HVY099":
      Draw($mainPlayer);
      break;
    case "HVY136":
      if ($combatChainState[$CCS_DamageDealt] >= $effectArr[1]) {
        PlayAura("HVY241", $mainPlayer, $effectArr[1]);
        return 1;
      }
      break;
    case "MST105-HIT":
      if (IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        $deck->BanishTop("Source-" . $attackID, banishedBy: $attackID);
      }
      break;
    case "MST162-HIT":
      BanishCardForPlayer("DYN065", $mainPlayer, "-", "TT", $mainPlayer);
      BanishCardForPlayer("DYN065", $mainPlayer, "-", "TT", $mainPlayer);
      break;
    case "AAZ004":
      Draw($mainPlayer);
      break;
    case "ROS012":
      if (IsHeroAttackTarget()) DealArcane(4, 1, "PLAYCARD", $cardID, false, $mainPlayer);
      return 1;
    case "HNT003-HIT":
      AddDecisionQueue("FINDINDICES", $defPlayer, "HAND");
      AddDecisionQueue("SETDQCONTEXT", $defPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("CHOOSEHAND", $defPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $defPlayer, "-", 1);
      AddDecisionQueue("BANISHCARD", $defPlayer, "HAND,-", 1);
      return 0;
    case "HNT004-HIT":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      AddDecisionQueue("BANISHCARD", $defPlayer, "MYARS,-,$source", 1);
      return 0;
    case "HNT051-ATTACK":
      if (IsHeroAttackTarget()) MarkHero($defPlayer);
      break;
    case "HNT102-MARK":
      $character = &GetPlayerCharacter($mainPlayer);
      if (IsHeroAttackTarget() && $character[$combatChainState[$CCS_WeaponIndex] + 11] == $effectArr[1]) {
        MarkHero($defPlayer);
        return 1;
      }
      break;
    case "HNT111":
    case "HNT114":
      MarkHero($defPlayer);
      break;
    case "HNT122":
    case "HNT123":
    case "HNT124":
      $character = &GetPlayerCharacter($mainPlayer);
      $character[$combatChainState[$CCS_WeaponIndex] + 1] = 2;
      ++$character[$combatChainState[$CCS_WeaponIndex] + 5];
      return 0;
    case "HNT131":
    case "HNT132":
    case "HNT133":
      if (IsHeroAttackTarget()) MarkHero($defPlayer);
      break;
    case "HNT140":
    case "HNT141":
    case "HNT142":
      if (IsHeroAttackTarget()) MarkHero($defPlayer);
      break;
    case "HNT185":
    case "HNT186":
    case "HNT187":
      WriteLog("The " . CardLink($cardID, $cardID) . " drains 1 health");
      LoseHealth(1, $defPlayer);
      break;
    case "HNT198-HIT":
      Draw($mainPlayer, effectSource:"HNT198");
      return 1;
    case "HNT208":
    case "HNT209":
    case "HNT210":
      MarkHero($defPlayer);
      return 1;
    case "HNT211":
    case "HNT212":
    case "HNT213":
      $character = &GetPlayerCharacter($mainPlayer);
      $character[$combatChainState[$CCS_WeaponIndex] + 1] = 2;
      ++$character[$combatChainState[$CCS_WeaponIndex] + 5];
      return 1;
    case "HNT228-HIT":
      DestroyArsenal($defPlayer, effectController:$mainPlayer);
      return 1;
    default:
      break;
  }
  return 0;
}

function EffectAttackModifier($cardID)
{
  $set = CardSet($cardID);
  if ($set == "WTR") return WTREffectAttackModifier($cardID);
  else if ($set == "ARC") return ARCEffectAttackModifier($cardID);
  else if ($set == "CRU") return CRUEffectAttackModifier($cardID);
  else if ($set == "MON") return MONEffectAttackModifier($cardID);
  else if ($set == "ELE") return ELEEffectAttackModifier($cardID);
  else if ($set == "EVR") return EVREffectAttackModifier($cardID);
  else if ($set == "DVR") return DVREffectAttackModifier($cardID);
  else if ($set == "RVD") return RVDEffectAttackModifier($cardID);
  else if ($set == "UPR") return UPREffectAttackModifier($cardID);
  else if ($set == "DYN") return DYNEffectAttackModifier($cardID);
  else if ($set == "OUT") return OUTEffectAttackModifier($cardID);
  else if ($set == "DTD") return DTDEffectAttackModifier($cardID);
  else if ($set == "TCC") return TCCEffectAttackModifier($cardID);
  else if ($set == "EVO") return EVOEffectAttackModifier($cardID);
  else if ($set == "HVY") return HVYEffectAttackModifier($cardID);
  else if ($set == "MST") return MSTEffectAttackModifier($cardID);
  else if ($set == "ROG") return ROGUEEffectAttackModifier($cardID);
  else if ($set == "AAZ") return AAZEffectAttackModifier($cardID);
  else if ($set == "TER") return TEREffectAttackModifier($cardID);
  else if ($set == "AUR") return AUREffectAttackModifier($cardID);
  else if ($set == "ROS") return ROSEffectAttackModifier($cardID);
  else if ($set == "AJV") return AJVEffectAttackModifier($cardID);
  else if ($set == "HNT") return HNTEffectAttackModifier($cardID);
  else if ($set == "AST") return ASTEffectAttackModifier($cardID);
  switch ($cardID) {
    case "HER123":
      return 1;
    default:
      return 0;
  }
}

function EffectHasBlockModifier($cardID)
{
  switch ($cardID) {
    case "MON089":
    case "ELE000-2":
    case "ELE143":
    case "ELE203":
    case "DYN115":
    case "DYN116":
    case "OUT005":
    case "OUT006":
    case "OUT007":
    case "OUT008":
    case "OUT009":
    case "OUT010":
    case "OUT109":
    case "OUT110":
    case "OUT111":
    case "DTD094":
    case "DTD095":
    case "DTD096":
    case "TCC035":
    case "HVY063":
    case "HVY052":
    case "HVY202":
    case "HVY203":
    case "HVY204":
    case "HVY205":
    case "HVY206":
    case "HVY245":
      return true;
    default:
      return false;
  }
}

function EffectBlockModifier($cardID, $index, $from)
{
  global $CombatChain, $defPlayer, $mainPlayer;
  switch ($cardID) {
    case "MON089":
      if ($CombatChain->Card($index)->ID() == $cardID) return 1;
      return 0;
    case "ELE000-2":
      return 1;
    case "ELE143":
      return 1;
    case "ELE203":
      return ($CombatChain->Card($index)->ID() == $cardID ? 1 : 0);
    case "OUT109":
      return (PitchValue($CombatChain->Card($index)->ID()) == 1 && HasAimCounter() ? -1 : 0);
    case "OUT110":
      return (PitchValue($CombatChain->Card($index)->ID()) == 2 && HasAimCounter() ? -1 : 0);
    case "OUT111":
      return (PitchValue($CombatChain->Card($index)->ID()) == 3 && HasAimCounter() ? -1 : 0);
    case "DTD094":
    case "DTD095":
    case "DTD096":
      return (CardType($CombatChain->Card($index)->ID()) != "E" && TalentContains($CombatChain->Card($index)->ID(), "LIGHT", $defPlayer) && TalentContains($CombatChain->AttackCard()->ID(), "SHADOW", $mainPlayer) ? 1 : 0);
    case "TCC035":
    case "HVY063":
      return (CachedTotalAttack() >= 13 && !TypeContains($CombatChain->Card($index)->ID(), "E") && !DelimStringContains(CardSubType($CombatChain->Card($index)->ID()), "Evo")) ? -1 : 0;
    case "EVO105":
    case "EVO106":
    case "EVO107":
      return IsActionCard($CombatChain->Card($index)->ID()) ? -1 : 0;
    case "HVY202":
    case "HVY203":
    case "HVY204":
    case "HVY205":
    case "HVY206":
      return $CombatChain->Card($index)->ID() == $cardID && PlayerHasLessHealth($defPlayer) ? 1 : 0;
    case "MST085":
      return SearchPitchForColor($mainPlayer, 3);
    case "AIO003":
    case "AIO005":
      return 1;
    default:
      return 0;
  }
}

function RemoveEffectsFromCombatChain($cardID = "")
{
  global $currentTurnEffects;
  $searchedEffect = "";
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if($cardID == "") {
      $effectArr = explode("-", $currentTurnEffects[$i]);
      $effectArr2 = explode(",", $effectArr[0]);
      $searchedEffect = $effectArr2[0];  
    }
    else $searchedEffect = $cardID;
    switch ($searchedEffect) {
      case "WTR079":
      case "CRU106":
      case "CRU107":
      case "CRU108": //High Speed Impact
      case "CRU109":
      case "CRU110":
      case "CRU111": // Combustible Courier
      case "MON035": //V of the Vanguard
      case "MON245": //Exude Confidence
      case "ELE067":
      case "ELE068":
      case "ELE069": //Explosive Growth
      case "ELE186":
      case "ELE187":
      case "ELE188": //Ball Lightning
      case "UPR049": //Spreading Flames
      case "UPR060":
      case "UPR061":
      case "UPR062": //Brand with Cinderclaw
      case "DYN095":
      case "DYN096":
      case "DYN097": //Scramble Pulse
      case "OUT033":
      case "OUT034":
      case "OUT035": //Prowl
      case "OUT052": //Head Leads the Tail
      case "OUT071":
      case "OUT072":
      case "OUT073": //Deadly Duo
      case "DTD052"://Spirit of War
      case "TCC086":
      case "TCC094"://Growl
      case "HVY246"://Coercive Tendency
      case "MST159": //Tiger Taming
      case "MST161"://Chase the Tail
      case "MST185":
      case "MST186":
      case "MST187": //Untamed
      case "MST190": //Stonewall Gauntlet
      case "MST212":
      case "MST213":
      case "MST214": //Water the Seeds
      case "HNT061":
      case "HNT083":
      case "HNT185":
      case "HNT186":
      case "HNT187":
      case "HNT215":
        $remove = 1;
        break;
      default:
        break;
    }
    if ($remove && SearchCurrentTurnEffectsForIndex($searchedEffect, $currentTurnEffects[$i + 1]) != -1) RemoveCurrentTurnEffect($i);
  }
}

function OnAttackEffects($attack)
{
  global $currentTurnEffects, $mainPlayer, $defPlayer;
  $attackType = CardType($attack);
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $mainPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "ELE085":
        case "ELE086":
        case "ELE087":
          if ($attackType == "AA") {
            AddLayer("TRIGGER", $mainPlayer, $currentTurnEffects[$i], $attack);
            $remove = true;
          }
          break;
        case "ELE092-DOM":
          AddDecisionQueue("SETDQCONTEXT", $defPlayer, "Do you want to pay 2 to prevent this attack from getting dominate?", 1);
          AddDecisionQueue("BUTTONINPUT", $defPlayer, "0,2", 0, 1);
          AddDecisionQueue("PAYRESOURCES", $defPlayer, "<-", 1);
          AddDecisionQueue("GREATERTHANPASS", $defPlayer, "0", 1);
          AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, $currentTurnEffects[$i] . "ATK!PLAY", 1);
          break;
        case "EVO247":
          Charge(may: true, player: $mainPlayer);
          AddDecisionQueue("ALLCARDPITCHORPASS", $mainPlayer, "2", 1);
          AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
          $remove = true;
          break;
        case "HVY055-PAID":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(substr($currentTurnEffects[$i], 0, 6));
          }
          break;
        case "HVY083-BUFF":
        case "HVY084-BUFF":
        case "HVY085-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(substr($currentTurnEffects[$i], 0, 6));
          }
          break;
        case "HVY086-BUFF":
        case "HVY087-BUFF":
        case "HVY088-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(substr($currentTurnEffects[$i], 0, 6));
          }
          break;
        case "HVY124-BUFF":
        case "HVY125-BUFF":
        case "HVY126-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(substr($currentTurnEffects[$i], 0, 6));
          }
          break;
        case "HVY130-BUFF":
        case "HVY131-BUFF":
        case "HVY132-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(substr($currentTurnEffects[$i], 0, 6));
          }
          break;
        case "HVY235-BUFF":
        case "HVY236-BUFF":
        case "HVY237-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(substr($currentTurnEffects[$i], 0, 6));
          }
          break;
        case "MST052":
          if (CardNameContains($attack, "Crouching Tiger", $mainPlayer)) {
            AddDecisionQueue("INPUTCARDNAME", $mainPlayer, "-");
            AddDecisionQueue("SETDQVAR", $mainPlayer, "0");
            AddDecisionQueue("PREPENDLASTRESULT", $mainPlayer, "DYN065-");
            AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, "<-");
            AddDecisionQueue("WRITELOG", $mainPlayer, "📣<b>{0}</b> was chosen");
          }
          break;
        case "MST092":
          if (PitchValue($attack) == 3) {
            Draw($mainPlayer);
            $remove = true;
          }
        case "ROS248":
          if (IsCombatEffectActive($currentTurnEffects[$i])){
            CacheCombatResult();
            if (IsWeaponGreaterThanTwiceBasePower()) GiveAttackGoAgain(); // borrowing ideas from merciless battleaxe (DYN068) and shift the tide of battle (HVY102)
          }
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
}

function CurrentEffectBaseAttackSet()
{
  global $currentPlayer, $currentTurnEffects;
  $mod = -1;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer && IsCombatEffectActive($currentTurnEffects[$i])) {
      switch ($currentTurnEffects[$i]) {
        case "UPR155":
          if ($mod < 8) $mod = 8;
          break;
        case "UPR156":
          if ($mod < 7) $mod = 7;
          break;
        case "UPR157":
          if ($mod < 6) $mod = 6;
          break;
        default:
          break;
      }
    }
  }
  return $mod;
}

function CurrentEffectCostModifiers($cardID, $from)
{
  global $currentTurnEffects, $currentPlayer, $CS_PlayUniqueID;
  $costModifier = 0;
  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      if (DelimStringContains($currentTurnEffects[$i], "HNT071", true)) {
        $cardType = CardType($cardID);
        if(TalentContains($cardID, "DRACONIC", $currentPlayer) && !IsStaticType($cardType, $from, $cardID)) {
          $costModifier -= 1;
          --$currentTurnEffects[$i + 3];
          if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
        }
      }
      switch ($currentTurnEffects[$i]) {
        case "WTR060":
        case "WTR061":
        case "WTR062":
          if (IsAction($cardID, $from)) {
            $costModifier += 1;
            $remove = true;
          }
          break;
        case "WTR075":
          if (ClassContains($cardID, "GUARDIAN", $currentPlayer) && CardType($cardID) == "AA") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "WTR152":
          if (CardType($cardID) == "AA" && (GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "")) {
            $costModifier -= 2;
            $remove = true;
          }
          break;
        case "CRU081":
          if (TypeContains($cardID, "W", $currentPlayer) && CardSubType($cardID) == "Sword") {
            $costModifier -= 1;
          }
          break;
        case "CRU085-2":
        case "CRU086-2":
        case "CRU087-2":
          if (CardType($cardID) == "DR") {
            $costModifier += 1;
            $remove = true;
          }
          break;
        case "CRU141-AA":
          if (CardType($cardID) == "AA") {
            $costModifier -= CountAura("ARC112", $currentPlayer);
            $remove = true;
          }
          break;
        case "CRU141-NAA":
          if (CardType($cardID) == "A") {
            $costModifier -= CountAura("ARC112", $currentPlayer);
            $remove = true;
          }
          break;
        case "ARC060":
        case "ARC061":
        case "ARC062":
          if ((CardType($cardID) == "AA" || GetResolvedAbilityType($cardID, $from) == "AA") && (GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "")) {
            $costModifier += 1;
            $remove = true;
          }
          break;
        case "ELE035-1":
          $costModifier += 1;
          break;
        case "ELE038":
        case "ELE039":
        case "ELE040":
          $costModifier += 1;
          break;
        case "ELE144":
          $costModifier += 1;
          break;
        case "EVR179":
          if (IsStaticType(CardType($cardID), $from, $cardID)) {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "UPR000":
          if (TalentContains($cardID, "DRACONIC", $currentPlayer) && $from != "PLAY" && $from != "EQUIP") {
            $costModifier -= 1;
            --$currentTurnEffects[$i + 3];
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
          }
          break;
        case "UPR075":
        case "UPR076":
        case "UPR077":
          if (GetClassState($currentPlayer, $CS_PlayUniqueID) == $currentTurnEffects[$i + 2]) {
            --$costModifier;
            $remove = true;
          }
          break;
        case "UPR166":
          if (IsStaticType(CardType($cardID), $from, $cardID) && DelimStringContains(CardSubType($cardID), "Staff")) {
            $costModifier -= 3;
            $remove = true;
          }
          break;
        case "OUT011":
          if (CardType($cardID) == "AR") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "OUT179_1":
          if (CardType($cardID) == "AA") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "DTD004":
          if (CardType($cardID) == "C") {
            $costModifier -= 2;
            $remove = true;
          }
          break;
        case "DTD212":
          if (CardType($cardID) == "AA" && ClassContains($cardID, "RUNEBLADE", $currentPlayer)) {
            $costModifier -= CountAura("ARC112", $currentPlayer);
            $remove = true;
          }
          break;
        case "TCC038":
        case "TCC043":
          if (ClassContains($cardID, "GUARDIAN", $currentPlayer) && CardType($cardID) == "AA") $costModifier -= 1;
          break;
        case "EVO435":
          if (TypeContains($cardID, "W", $currentPlayer)) {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "AKO004":
          $attack = 0;
          for ($j = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $j >= 0; $j -= CurrentTurnEffectsPieces()) {
            if (IsCombatEffectActive($currentTurnEffects[$j], $cardID)) {
              if ($currentTurnEffects[$j + 1] == $currentPlayer) {
                $attack += EffectAttackModifier($currentTurnEffects[$j]);
              }
            }
          }  
          $attack += ModifiedAttackValue($cardID, $currentPlayer, $from);
          if (CardType($cardID) == "AA" && $attack >= 6) $costModifier -= 1;
          break;
        case "MST229":
          if (CardType($cardID) == "AA") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "HNT058":
          if (TalentContains($cardID, "DRACONIC", $currentPlayer)) {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "HNT061":
          if (SubtypeContains($cardID, "Dagger", $currentPlayer)) {
            $costModifier -= 1;
          }
          break;
        case "ROGUE803":
          if (IsStaticType(CardType($cardID), $from, $cardID)) {
            $costModifier -= 1;
          }
          break;
        case "ROGUE024":
          $costModifier += 1;
          break;
        case "ASB004":
          if (PitchValue($cardID) == 2) $costModifier -= 1;
          break;
        case "ROS249":
          if (SubtypeContains($cardID, "Aura") && $from != "PLAY") {
            $costModifier -= 2;
            $remove = true;
          }
          break;
        case "HNT145":
          $otherChar = &GetPlayerCharacter(player: $otherPlayer);
          $isAttack = GetResolvedAbilityType($cardID, $from) == "AA" || (GetResolvedAbilityType($cardID, $from) == "" && CardType($cardID) == "AA");
          if (CardNameContains($otherChar[0], "Arakni") && $isAttack) {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "HNT197":
          if (GetClassState($currentPlayer, $CS_PlayUniqueID) == $currentTurnEffects[$i + 2]) $costModifier -= 1;
          break;
        default:
          break;
      }
      if ($remove) RemoveCurrentTurnEffect($i);
    }
  }
  return CostCantBeModified($cardID) ? 0 : $costModifier;
}

function CurrentEffectPreventDamagePrevention($player, $type, $damage, $source)
{
  global $currentTurnEffects;
  $preventedDamage = 0;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0; $i -= CurrentTurnEffectPieces()) {
    $remove = false;
    if ($preventedDamage < $damage && $currentTurnEffects[$i + 1] == $player) {
      switch ($currentTurnEffects[$i]) {
        case "MST137":
          if (PitchValue($source) == 1) {
            $preventedDamage += $damage;
            RemoveCurrentTurnEffect($i);
          }
          break;
        case "MST138":
          if (PitchValue($source) == 2) {
            $preventedDamage += $damage;
            RemoveCurrentTurnEffect($i);
          }
          break;
        case "MST139":
          if (PitchValue($source) == 3) {
            $preventedDamage += $damage;
            RemoveCurrentTurnEffect($i);
          }
          break;
        case "HNT222":
        case "HNT230":
          $preventedDamage += 1;
          --$currentTurnEffects[$i + 3];
          if ($currentTurnEffects[$i + 3] == 0) $remove = true;
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  if ($preventedDamage > 0 && SearchCurrentTurnEffects("OUT174", $player) != "") {
    $preventedDamage -= 1;
    SearchCurrentTurnEffects("OUT174", $player, remove:true);
  }
  $damage -= $preventedDamage;
  return $damage;
}

function CurrentEffectDamagePrevention($player, $type, $damage, $source, $preventable)
{
  global $currentTurnEffects;
  $otherPlayer = ($player == 1 ? 2 : 1);
  $vambraceAvailable = SearchCurrentTurnEffects("OUT174", $player) != "";
  $vambraceRemove = false;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0 && $damage > 0; $i -= CurrentTurnEffectPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $player) {
      $preventedDamage = 0;
      $effects = explode("-", $currentTurnEffects[$i]);
      switch ($effects[0]) {
        case "ARC035":
          if ($preventable) $preventedDamage += intval($effects[1]);
          $remove = true;
          break;
        case "CRU041":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 3;
            $remove = true;
          }
          break;
        case "CRU042":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 2;
            $remove = true;
          }
          break;
        case "CRU043":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 1;
            $remove = true;
          }
          break;
        case "EVR033":
        case "EVR034":
        case "EVR035":
          if ($source == $currentTurnEffects[$i + 2]) {
            if ($preventable) {
              $origDamage = $damage;
              $preventedDamage += $currentTurnEffects[$i + 3];
              if ($preventedDamage > $damage) $preventedDamage = $damage;
              $currentTurnEffects[$i + 3] -= $origDamage;
            }
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
          }
          break;
        case "EVR180":
          $remove = true;
          break;
        case "UPR183":
          if ($source == $currentTurnEffects[$i + 2]) {
            if ($preventable) {
              $sourceDamage = $damage;
              $preventedDamage += $currentTurnEffects[$i + 3];
              $currentTurnEffects[$i + 3] -= $sourceDamage;
            }
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
            if ($source == "ARC112" || $source == "UPR042") $remove = true; //To be removed when coded with Unique ID instead of cardID name as $source
          }
          break;
        case "UPR221":
        case "UPR222":
        case "UPR223":
          if ($source == $currentTurnEffects[$i + 2]) {
            if ($preventable) {
              $sourceDamage = $damage;
              $preventedDamage += $currentTurnEffects[$i + 3];
              $currentTurnEffects[$i + 3] -= $sourceDamage;
            }
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
            if (TypeContains($source, "AA") || $source == "ARC112" || $source == "UPR042") $remove = true; //To be removed when coded with Unique ID instead of cardID name as $source
          }
          break;
        case "OUT175":
        case "OUT176":
        case "OUT177":
        case "OUT178":
          if ($preventable) {
            $preventedDamage += 1;
          }
          $remove = true;
          break;
        case "OUT228":
          if ($preventable && $damage <= 3) {
            $preventedDamage = $damage;
            $remove = true;
          }
          break;
        case "OUT229":
          if ($preventable && $damage <= 2) {
            $preventedDamage = $damage;
            $remove = true;
          }
          break;
        case "OUT230":
          if ($preventable && $damage == 1) {
            $preventedDamage = $damage;
            $remove = true;
          }
          break;
        case "OUT231":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 4;
            $remove = true;
          }
          break;
        case "OUT232":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 3;
            $remove = true;
          }
          break;
        case "OUT233":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 2;
            $remove = true;
          }
          break;
        case "DTD100":
        case "DTD101":
        case "DTD102":
          if ($effects[0] == "DTD100") $prevention = 4;
          else if ($effects[0] == "DTD101") $prevention = 3;
          else if ($effects[0] == "DTD102") $prevention = 2;
          if (TalentContains($source, "SHADOW", $otherPlayer)) {
            if ($preventable) $preventedDamage += $prevention;
            $remove = true;
          }
          break;
        case "TCC058":
          if ($preventable) $preventedDamage += 3;
          $remove = true;
          break;
        case "TCC062":
          if ($preventable) $preventedDamage += 2;
          $remove = true;
          break;
        case "TCC075":
          if ($preventable) $preventedDamage += 1;
          $remove = true;
          break;
        case "EVO087":
        case "EVO088":
        case "EVO089":
          $remove = true;
          break;
        case "EVO030":
        case "EVO031":
        case "EVO032":
        case "EVO033": //Card
        case "EVO430":
        case "EVO431":
        case "EVO432":
        case "EVO433": //Equipment
          if ($preventable) $preventedDamage += intval($effects[1]);
          $remove = true;
          break;
        case "HVY016":
          if ($preventable) $preventedDamage += 2 + intval($effects[1]);
          $remove = true;
          break;
        case "HVY140":
          if ($preventable) {
            $preventedDamage += 2;
            PlayAura("HVY241", $player); //Might
          }
          $remove = true;
          break;
        case "HVY160":
          if ($preventable) {
            $preventedDamage += 2;
            PlayAura("HVY240", $player); //Agility
          }
          $remove = true;
          break;
        case "HVY180":
          if ($preventable) {
            $preventedDamage += 2;
            PlayAura("HVY242", $player); //Vigor
          }
          $remove = true;
          break;
        case "HVY197":
          if ($preventable) $preventedDamage += 2;
          $remove = true;
          break;
        case "AKO019":
        case "MST203":
        case "MST204":
        case "MST205":
          $remove = true;
          break;
        case "MST034":
          if ($preventable) {
            if ($currentTurnEffects[$i] == "MST034-1") $preventedDamage += 3;
            else $preventedDamage += 5;
          }
          $remove = true;
          break;
        case "MST035":
          if ($preventable) {
            if ($currentTurnEffects[$i] == "MST035-1") $preventedDamage += 2;
            else $preventedDamage += 4;
          }
          $remove = true;
          break;
        case "MST036":
          if ($preventable) {
            if ($currentTurnEffects[$i] == "MST036-1") $preventedDamage += 1;
            else $preventedDamage += 3;
          }
          $remove = true;
          break;
        case "AUR023":
        case "TER020":
          if ($preventable) {
            $preventedDamage += 2;
          }
          $remove = true;
          break;
        case "TER026":
          if ($preventable) {
            $preventedDamage += 1;
          }
          $remove = true;
          break;
        case "ROS027":
          if ($source == $currentTurnEffects[$i + 2]) {
            if ($preventable) {
              $sourceDamage = $damage;
              $preventedDamage += $currentTurnEffects[$i + 3];
              $currentTurnEffects[$i + 3] -= $sourceDamage;
            }
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
          }
          break;
        case "HNT250":
          if ($preventable) {
            $preventedDamage += intval($effects[1]);
            $remove = true;
            break;
          }
          break;
        default:
          break;
      }
      if ($remove) RemoveCurrentTurnEffect($i);
      //apply vambrace only once and only after the first instance of prevention
      if ($type == "COMBAT" && $vambraceAvailable && $preventedDamage > 0) {
        $preventedDamage -= 1;
        $vambraceAvailable = false;
        $vambraceRemove = true;
      }
      $damage -= $preventedDamage;
    }
  }
  if ($vambraceRemove) SearchCurrentTurnEffects("OUT174", $player, remove:true);
  return $damage;
}

function CurrentEffectAttackAbility()
{
  global $currentTurnEffects, $CombatChain, $mainPlayer;
  global $CS_PlayIndex;
  if (!$CombatChain->HasCurrentLink()) return;
  $attackID = $CombatChain->AttackCard()->ID();
  $attackType = CardType($attackID);
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $mainPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "EVR056":
          if ($attackType == "W") {
            $character = &GetPlayerCharacter($mainPlayer);
            ++$character[GetClassState($mainPlayer, $CS_PlayIndex) + 3];
          }
          break;
        case "MON183":
        case "MON184":
        case "MON185":
          if ($currentTurnEffects[$i] == "MON183") $maxCost = 2;
          else if ($currentTurnEffects[$i] == "MON184") $maxCost = 1;
          else $maxCost = 0;
          if ($attackType == "AA" && CardCost($attackID) <= $maxCost) {
            WriteLog(CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]) . " dealt 1 damage.");
            DealArcane(1, 0, "PLAYCARD", $currentTurnEffects[$i], true, $mainPlayer);
            $remove = true;
          }
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
}

function CurrentEffectPlayAbility($cardID, $from)
{
  global $currentTurnEffects, $currentPlayer, $actionPoints, $CS_LastDynCost;

  if (DynamicCost($cardID) != "") $cost = GetClassState($currentPlayer, $CS_LastDynCost);
  else $cost = CardCost($cardID, $from);
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "ARC209":
          $cardType = CardType($cardID);
          if ((DelimStringContains($cardType, "A") || $cardType == "AA") && $cost >= 0) {
            ++$actionPoints;
            $remove = true;
          }
          break;
        case "ARC210":
          $cardType = CardType($cardID);
          if ((DelimStringContains($cardType, "A") || $cardType == "AA") && $cost >= 1) {
            ++$actionPoints;
            $remove = true;
          }
          break;
        case "ARC211":
          $cardType = CardType($cardID);
          if ((DelimStringContains($cardType, "A") || $cardType == "AA") && $cost >= 2) {
            ++$actionPoints;
            $remove = true;
          }
          break;
        default:
          break;
      }
      if ($remove) RemoveCurrentTurnEffect($i);
    }
  }
  return false;
}

function CurrentEffectPlayOrActivateAbility($cardID, $from)
{
  global $currentTurnEffects, $currentPlayer;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      $remove = false;
      $effectArr = explode(",", $currentTurnEffects[$i]);
      switch ($effectArr[0]) {
        case "MON153":
        case "MON154":
          $cardType = CardType($cardID);
          if (($cardType == "AA" || $cardType == "W" || $cardType == "T") && (ClassContains($cardID, "RUNEBLADE", $currentPlayer) || TalentContains($cardID, "SHADOW", $currentPlayer))) {
            GiveAttackGoAgain();
            $remove = true;
          }
          break;
        default:
          break;
      }
      if ($remove) RemoveCurrentTurnEffect($i);
    }
  }
  $currentTurnEffects = array_values($currentTurnEffects); //In case any were removed
  return false;
}

function CurrentEffectAfterPlayOrActivateAbility($cache = true)
{
  global $currentTurnEffects, $currentPlayer;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      $remove = false;
      $effectArr = explode(",", $currentTurnEffects[$i]);
      switch ($effectArr[0]) {
        case "HVY053":
          if ($cache) CacheCombatResult();
          if ($effectArr[1] != "ACTIVE" && CachedTotalAttack() > intval($effectArr[1])) $currentTurnEffects[$i] = "HVY053,ACTIVE";
          break;
        default:
          break;
      }
      if ($remove) RemoveCurrentTurnEffect($i);
    }
  }
  $currentTurnEffects = array_values($currentTurnEffects); //In case any were removed
  return false;
}

function CurrentEffectGrantsInstantGoAgain($cardID, $from)
{
  global $currentTurnEffects, $currentPlayer;
  $hasGoAgain = false;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "ROS071": 
          $hasGoAgain = true;
          break;
        default:
          break;
      }
    }
  }
  return $hasGoAgain;
}

function CurrentEffectGrantsNonAttackActionGoAgain($cardID, $from)
{
  global $currentTurnEffects, $currentPlayer, $CS_AdditionalCosts;
  $hasGoAgain = false;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      if (strlen($currentTurnEffects[$i]) > 6) $turnEffects = explode(",", $currentTurnEffects[$i]);
      else $turnEffects[0] = $currentTurnEffects[$i];
      switch ($turnEffects[0]) {
        case "WTR007-GOAGAIN":
          $hasGoAgain = true;
          $remove = true;
          break;
        case "MON153":
        case "MON154":
          if (ClassContains($cardID, "RUNEBLADE", $currentPlayer) || TalentContains($cardID, "SHADOW", $currentPlayer)) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "ELE177":
          if (CardCost($cardID) >= 0) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "ELE178":
          if (CardCost($cardID) >= 1) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "ELE179":
          if (CardCost($cardID) >= 2) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "ELE201":
          $hasGoAgain = true;
          $remove = true;
          break;
        case "ARC185-GA":
          $hasGoAgain = ($cardID == "ARC212" || $cardID == "ARC213" || $cardID == "ARC214");
          break;
        case "DTD190":
        case "DTD191":
        case "DTD192":
          if (SearchCurrentTurnEffects($turnEffects[0] . "," . $cardID, $currentPlayer) && $from == "BANISH") {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "MST094":
          if (ColorContains($cardID, 3, $currentPlayer)) {
            $hasGoAgain = true;
            if ($cardID != $turnEffects[0]) $remove = true;
          }
          break;
        case "ROS010-GOAGAIN":
          if(SearchCurrentTurnEffects("ROS010", $currentPlayer) && !IsMeldInstantName(GetClassState($currentPlayer, $CS_AdditionalCosts)) && (GetClassState($currentPlayer, $CS_AdditionalCosts) != "Both" || $from == "MELD")) {
            $hasGoAgain = true;
            if ($cardID != "ROS010") $remove = true;
          }
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  return $hasGoAgain;
}

function CurrentEffectGrantsGoAgain()
{
  global $currentTurnEffects, $mainPlayer, $combatChainState, $CCS_AttackFused, $CS_NumAuras, $defPlayer;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if (!isset($currentTurnEffects[$i + 1])) continue;
    if ($currentTurnEffects[$i + 1] == $mainPlayer && IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i)) {
      if (strlen($currentTurnEffects[$i]) > 6) $turnEffects = explode(",", $currentTurnEffects[$i]);
      else $turnEffects[0] = $currentTurnEffects[$i];
      switch ($turnEffects[0]) {
        case "WTR144":
        case "WTR145":
        case "WTR146":
          return true;
        case "WTR154":
          return true;
        case "ARC047":
          return true;
        case "ARC160-3":
          return true;
        case "CRU053":
          return true;
        case "CRU055":
          return true;
        case "CRU084":
          return true;
        case "CRU091-1":
        case "CRU092-1":
        case "CRU093-1":
          return true;
        case "CRU122":
          return true;
        case "CRU145":
        case "CRU146":
        case "CRU147":
          return true;
        case "MON141":
        case "MON142":
        case "MON143":
          return true;
        case "MON165":
        case "MON166":
        case "MON167":
          return true;
        case "MON193":
          return true;
        case "MON247":
          return true;
        case "MON260-2":
        case "MON261-2":
        case "MON262-2":
          return true;
        case "ELE031-1":
          return true;
        case "ELE034-2":
          return true;
        case "ELE091-GA":
          return true;
        case "ELE177":
        case "ELE178":
        case "ELE179":
          return true;
        case "ELE180":
        case "ELE181":
        case "ELE182":
          if ($combatChainState[$CCS_AttackFused] == 1) return true;
          else break;
        case "ELE201":
          return true;
        case "EVR017":
          return true;
        case "EVR044":
        case "EVR045":
        case "EVR046":
          return true;
        case "EVR161-3":
        case "EVR162-3":
        case "EVR163-3":
          return true;
        case "DVR008":
          return true;
        case "DVR019":
          return true;
        case "UPR081":
        case "UPR082":
        case "UPR083":
          return true;
        case "UPR094":
          return true;
        case "DYN076":
        case "DYN077":
        case "DYN078":
          return true;
        case "DTD190":
        case "DTD191":
        case "DTD192":
          return true;
        case "HVY240":
          return true;
        case "HVY254-1":
        case "HVY254-2":
          if (SearchPitchForColor($mainPlayer, 2) > 0) return true;
          else break;
        case "HVY246":
          return true;
        case "MST003":
          return true;
        case "MST024":
          return true;
        case "MST094":
          return true;
        case "MST236-2":
          return true;
        case "ROGUE710-GA":
          return true;
        case "AAZ007":
          return true;
        case "ROS010-GOAGAIN":
          return true;
        case "ROS118":
          if(GetClassState($mainPlayer, $CS_NumAuras) >= 1) return true;
          else break;
        case "HNT125":
          return true;
        case "HNT134-GOAGAIN":
        case "HNT135-GOAGAIN":
        case "HNT136-GOAGAIN":
          return IsHeroAttackTarget() && CheckMarked($defPlayer);
        case "HNT143":
        case "HNT147":
          return true;
        case "HNT240":
          return true;
        case "HNT407":
          return true;
        default:
          break;
      }
    }
  }
  return false;
}

function CurrentEffectPreventsGoAgain($cardID, $from="-")
{
  global $currentTurnEffects, $mainPlayer, $CS_AdditionalCosts;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if (!isset($currentTurnEffects[$i + 1])) continue;
    if ($currentTurnEffects[$i + 1] == $mainPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "WTR044":
          if(HasMeld($cardID) && !IsMeldInstantName(GetClassState($mainPlayer, $CS_AdditionalCosts))
          || DelimStringContains(CardType($cardID), "AA") 
          || DelimStringContains(CardType($cardID), "A") 
          || GetResolvedAbilityType($cardID, $from) == "AA"
          || GetResolvedAbilityType($cardID, $from) == "A" ){
            return true;
          }
          break;
        default:
          break;
      }
    }
  }
  return false;
}

function CurrentEffectPreventsDefenseReaction($from)
{
  global $currentTurnEffects, $currentPlayer;
  $reactionPrevented = false;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if (!isset($currentTurnEffects[$i + 1])) continue;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "CRU123":
          if ($from == "ARS" && IsCombatEffectActive($currentTurnEffects[$i])) $reactionPrevented = true;
          break;
        case "CRU135-1":
        case "CRU136-1":
        case "CRU137-1":
          if ($from == "HAND" && IsCombatEffectActive($currentTurnEffects[$i])) $reactionPrevented = true;
          break;
        case "EVR091-1":
        case "EVR092-1":
        case "EVR093-1":
          if ($from == "ARS" && IsCombatEffectActive($currentTurnEffects[$i])) $reactionPrevented = true;
          break;
        default:
          break;
      }
    }
  }
  return $reactionPrevented;
}

function CurrentEffectPreventsDraw($player, $isMainPhase)
{
  global $currentTurnEffects;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] == $player) {
      switch ($currentTurnEffects[$i]) {
        case "WTR045":
          if ($isMainPhase) WriteLog("Draw prevented by " . CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]));
          return $isMainPhase;
        default:
          break;
      }
    }
  }
  return false;
}

function CurrentEffectIntellectModifier($remove = false)
{
  global $currentTurnEffects, $mainPlayer;
  $intellectModifier = 0;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0; $i -= CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] == $mainPlayer) {
      $cardID = substr($currentTurnEffects[$i], 0, 6);
      switch ($cardID) {
        case "WTR042":
        case "ARC161":
        case "CRU028":
        case "MON000":
        case "MON246":
        case "EVO026":
        case "EVO426":
          if($remove){// Handle transformations (Blasmophet, Dishonor, etc) restarting Intellect
            RemoveCurrentTurnEffect($i);
            break;
          }
          $intellectModifier += 1;
          break;
        case "HVY009":
          if($remove){// Handle transformations (Blasmophet, Dishonor, etc) restarting Intellect
            RemoveCurrentTurnEffect($i);
            break;
          }
          $characters = GetPlayerCharacter($mainPlayer);
          $intellectModifier -= CharacterIntellect($characters[0]) - substr($currentTurnEffects[$i], -1);
          break;
        case "ROS217":
          if($remove){// Handle transformations (Blasmophet, Dishonor, etc) restarting Intellect
            RemoveCurrentTurnEffect($i);
            break;
          }
          $intellectModifier -= 2;
          break;
        default:
          break;
      }
    }
  }
  return $intellectModifier;
}

function CurrentEffectEndTurnAbilities()
{
  global $currentTurnEffects, $mainPlayer;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    $cardID = substr($currentTurnEffects[$i], 0, 6);
    if (SearchCurrentTurnEffects($cardID . "-UNDER", $currentTurnEffects[$i + 1])) {
      AddNextTurnEffect($currentTurnEffects[$i], $currentTurnEffects[$i + 1]);
    }
    switch ($cardID) {
      case "MON069":
      case "MON070":
      case "MON071":
      case "EVR056":
        if ($mainPlayer == $currentTurnEffects[$i + 1]) {
          $char = &GetPlayerCharacter($currentTurnEffects[$i + 1]);
          for ($j = 0; $j < count($char); $j += CharacterPieces()) {
            if (TypeContains($char[$j], "W", $mainPlayer)) $char[$j + 3] = 0;
          }
          $remove = true;
        }
        break;
      case "EVO013": case "ROS246": case "ELE111":
        AddNextTurnEffect($currentTurnEffects[$i], $currentTurnEffects[$i + 1]);
        break;
      default:
        break;
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
}

function IsCombatEffectActive($cardID, $defendingCard = "", $SpectraTarget = false, $flicked = false)
{
  global $CombatChain;
  if ($SpectraTarget) return;
  if ($cardID == "AIM") return true;
  $cardID = ShiyanaCharacter($cardID);
  if ($defendingCard == "") $cardToCheck = $CombatChain->AttackCard()->ID();
  else $cardToCheck = $defendingCard;
  $set = CardSet($cardID);
  if ($set == "WTR") return WTRCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "ARC") return ARCCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "CRU") return CRUCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "MON") return MONCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "ELE") return ELECombatEffectActive($cardID, $cardToCheck);
  else if ($set == "EVR") return EVRCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "DVR") return DVRCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "UPR") return UPRCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "DYN") return DYNCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "OUT") return OUTCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "DTD") return DTDCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "TCC") return TCCCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "EVO") return EVOCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "HVY") return HVYCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "MST") return MSTCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "ROG") return ROGUECombatEffectActive($cardID, $cardToCheck);
  else if ($set == "AAZ") return AAZCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "TER") return TERCombatEffectActive($cardID);
  else if ($set == "AUR") return AURCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "ROS") return ROSCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "AIO") return AIOCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "AJV") return AJVCombatEffectActive($cardID, $cardToCheck);
  else if ($set == "HNT") return HNTCombatEffectActive($cardID, $cardToCheck, $flicked);
  else if ($set == "AST") return ASTCombatEffectActive($cardID, $cardToCheck);
  switch ($cardID) {
    case "LGS180":
      return DTDCombatEffectActive($cardID, $cardToCheck);
    case "LGS181":
      return DTDCombatEffectActive($cardID, $cardToCheck);
    case "HER123":
      return true;
    default:
      return;
  }
}

function IsCombatEffectPersistent($cardID)
{
  global $Card_LifeBanner, $Card_ResourceBanner;
  $effectArr = explode(",", $cardID);
  $cardID = ShiyanaCharacter($effectArr[0]);
  if (DelimStringContains($cardID, "HNT071", true)) return true;
  switch ($cardID) {
    case "WTR007":
    case "WTR038":
    case "WTR039":
      return true;
    case "ARC047":
      return true;
    case "ARC160-1":
      return true;
    case "ARC170-1":
    case "ARC171-1":
    case "ARC172-1":
      return true;
    case "CRU025":
    case "CRU053":
    case "CRU084-2":
    case "CRU105":
    case "CRU122":
    case "CRU124":
    case "CRU188":
      return true;
    case "MON034":
    case "MON035":
    case "MON087":
    case "MON089":
    case "MON108":
    case "MON109":
    case "MON218":
    case "MON239":
    case "MON245":
      return true;
    case "ELE044":
    case "ELE045":
    case "ELE046":
      return true;
    case "ELE047":
    case "ELE048":
    case "ELE049":
      return true;
    case "ELE050":
    case "ELE051":
    case "ELE052":
      return true;
    case "ELE059":
    case "ELE060":
    case "ELE061":
      return true;
    case "ELE066-HIT":
    case "ELE067":
    case "ELE068":
    case "ELE069":
    case "ELE091-BUFF":
    case "ELE091-GA":
      return true;
    case "ELE092-DOM":
    case "ELE092-BUFF":
    case "ELE143":
    case "ELE151-HIT":
    case "ELE152-HIT":
    case "ELE153-HIT":
      return true;
    case "ELE173":
    case "ELE198":
    case "ELE199":
    case "ELE200":
    case "ELE203":
      return true;
    case "EVR001":
    case "EVR019":
    case "EVR066-1":
    case "EVR067-1":
    case "EVR068-1":
    case "EVR090":
      return true;
    case "EVR160":
    case "EVR164":
    case "EVR165":
    case "EVR166":
    case "EVR170-1":
    case "EVR171-1":
    case "EVR172-1":
    case "EVR186":
      return true;
    case "DVR008-1":
      return true;
    case "UPR036":
    case "UPR037":
    case "UPR038":
    case "UPR047":
    case "UPR049":
      return true;
    case "DYN009":
    case "DYN049":
    case "DYN085":
    case "DYN086":
    case "DYN087":
    case "DYN089-UNDER":
    case "DYN154":
      return true;
    case "OUT052":
    case "OUT140":
    case "OUT141":
    case "OUT144":
    case "OUT188_1":
      return true;
    case "DTD011":
      return true;
    case "DTD051":
      return true;//Beckoning Light
    case "DTD052":
      return true;//Spirit of War
    case "DTD111":
      return true;
    case "DTD198":
      return true;//Call Down the Lightning
    case "DTD208":
      return true;
    case "DTD229-HIT":
      return true;
    case "DTD410":
      return true;
    case "DTD411":
      return true;
    case "EVO426":
      return true;
    case $Card_LifeBanner:
      return true;
    case $Card_ResourceBanner:
      return true;
    case "HVY052":
    case "HVY090":
    case "HVY091":
      return true;
    case "HVY104":
      return true;
    case "HVY136":
      return true;
    case "EVO146":
      return true;
    case "HVY176":
      return true;
    case "HVY246":
      return true;
    case "HVY247":
      return true;
    case "MST053":
      return true;
    case "MST079-DEBUFF":
      return true;
    case "MST079-HITPREVENTION":
      return true;
    case "MST190":
      return true;
    case "AAZ004":
      return true;
    case "TER019":
      return true;
    case "ROS012":
    case "ROS119":
      return true;
    case "AJV006-E":
    case "AJV006-I":
      return true;
    case "HNT061":
    case "HNT105":
    case "HNT125":
    case "HNT127":
    case "HNT134-GOAGAIN":
    case "HNT135-GOAGAIN":
    case "HNT136-GOAGAIN":
    case "HNT137-MARKEDBUFF":
    case "HNT138-MARKEDBUFF":
    case "HNT139-MARKEDBUFF":
    case "HNT156":
    case "HNT185":
    case "HNT186":
    case "HNT187":
    case "HNT198-HIT":
    case "HNT215":
    case "HNT228":
    case "HNT228-HIT":
    case "HNT258-BUFF":
    case "HNT258-DMG":
      return true;
    //Roguelike
    case "ROGUE018":
    case "ROGUE601":
    case "ROGUE702":
    case "ROGUE704":
    case "ROGUE707":
      return true;
    case "ROGUE603":
    case "ROGUE612":
    case "ROGUE613":
    case "ROGUE614":
    case "ROGUE615":
    case "ROGUE616":
      return true;
    case "ROGUE710-GA":
    case "ROGUE710-DO":
    case "ROGUE711":
    case "ROGUE802":
    case "ROGUE805":
    case "ROGUE806":
      return true;
    default:
      return false;
  }
}

function BeginEndPhaseEffects()
{
  global $currentTurnEffects, $mainPlayer, $EffectContext, $defPlayer;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    $EffectContext = $currentTurnEffects[$i];
    switch ($currentTurnEffects[$i]) {
      case "EVR106":
        if (CountAura("ARC112", $mainPlayer) > 0) {
          WriteLog(CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]) . " destroyed your Runechant tokens");
          DestroyAllThisAura($currentTurnEffects[$i + 1], "ARC112");
        }
        break;
      case "UPR183-1":
        $attackCharIndex = FindCharacterIndex($mainPlayer, "UPR183");
        $defendCharIndex = FindCharacterIndex($defPlayer, "UPR183");
        if ($attackCharIndex > -1) {
          DestroyCharacter($mainPlayer, $attackCharIndex);
        } elseif ($defendCharIndex > -1) {
          DestroyCharacter($defPlayer, $defendCharIndex);
        }
      case "UPR200":
      case "UPR201":
      case "UPR202":
        Draw($currentTurnEffects[$i + 1], false);
        break;
      case "AAZ005":
        $attackCharIndex = FindCharacterIndex($mainPlayer, "AAZ005");
        $defendCharIndex = FindCharacterIndex($defPlayer, "AAZ005");
        if ($attackCharIndex > -1) {
          DestroyCharacter($mainPlayer, $attackCharIndex);
        } elseif ($defendCharIndex > -1) {
          DestroyCharacter($defPlayer, $defendCharIndex);
        }
        break;
      case "ROS027-1":
        $player = $currentTurnEffects[$i+1];
        $sanctuaryIndex = GetItemIndex("ROS027", $player);
        DestroyItemForPlayer($player, $sanctuaryIndex, true);
        break;
      default:
        break;
    }
  }
}

function BeginEndPhaseEffectTriggers()
{
  global $currentTurnEffects, $mainPlayer, $defPlayer;
  $numBloodDebt = SearchCount(SearchBanish($mainPlayer, "", "", -1, -1, "", "", true));
  if (!IsImmuneToBloodDebt($mainPlayer) && $numBloodDebt > 0) AddLayer("TRIGGER", $mainPlayer, "BLOODDEBT");
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    switch ($currentTurnEffects[$i]) {
      case "ELE215-1": 
        AddLayer("TRIGGER", $defPlayer, "ELE215", $currentTurnEffects[$i + 1], "-", "-");
        break;
      case "DYN153":
        AddLayer("TRIGGER", $mainPlayer, "DYN153", $currentTurnEffects[$i + 1], "-", "-");
        break;
      case "ROS247-1":
        AddLayer("TRIGGER", $defPlayer, "ROS247", $currentTurnEffects[$i + 1], "-", "-");
        break;
      default:
        break;
    }
  }
}

function ActivateAbilityEffects()
{
  global $currentPlayer, $currentTurnEffects, $mainPlayer;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "ELE004-HIT":
          WriteLog(CardLink("ELE004", "ELE004") . " created a frostbite");
          PlayAura("ELE111", $currentPlayer, effectController:$mainPlayer);
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  $currentTurnEffects = array_values($currentTurnEffects);
}

function CurrentEffectNameModifier($effectID, $effectParameter, $player)
{
  $name = "";
  if (SearchCurrentTurnEffects("OUT183", $player)) return $name;
  switch ($effectID) {
    case "OUT049":
      $name = $effectParameter;
      break;
    case "OUT068":
    case "OUT069":
    case "OUT070":
      $name = $effectParameter;
      break;
    case "DYN065":
      $name = $effectParameter;
      break;
    case "HNT249":
      $name = $effectParameter;
      break;
    default:
      break;
  }
  return $name;
}

function EffectDefenderAttackModifiers($cardID)
{
  $mod = 0;
  global $defPlayer, $currentTurnEffects;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $defPlayer && IsCombatEffectActive($currentTurnEffects[$i], $cardID)) {
      switch ($currentTurnEffects[$i]) {
        case "MON008":
        case "MON009":
        case "MON010":
          $mod -= 1;
          break;
        case "DTD011":
        case "DTD411":
          $mod -= 1;
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  $currentTurnEffects = array_values($currentTurnEffects);
  return $mod;
}

function EffectAttackRestricted($cardID, $type, $from, $revertNeeded = false, $index = -1)
{
  global $mainPlayer, $currentTurnEffects;
  $mainChar = &GetPlayerCharacter($mainPlayer);
  $attackValue = AttackValue($cardID, $index);
  $hasNoAbilityTypes = GetAbilityTypes($cardID, from: $from) == "";
  $resolvedAbilityType = GetResolvedAbilityType($cardID);
  $abilityType = GetAbilityType($cardID, from: $from);

  if ($mainChar[0] == "DUMMY") return false;
  $restrictedBy = "";
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $mainPlayer) {
      $effectArr = explode(",", $currentTurnEffects[$i]);
      $effectID = $effectArr[0];
      switch ($effectID) {
        case "DTD203":
          if ($attackValue <= $effectArr[1] && ($type == "AA" || $resolvedAbilityType == "AA" || $abilityType == "AA") && ($hasNoAbilityTypes || $resolvedAbilityType == "AA")) {
              $restrictedBy = "DTD203";
          }
          break;
        case "WarmongersPeace":
          if (($type == "AA" && !str_contains(GetAbilityTypes($cardID), "I") || (TypeContains($cardID, "W", $mainPlayer) && $resolvedAbilityType != "I"))) $restrictedBy = "DTD230";
          break;
        default:
          break;
      }
    }
  }
  if ($revertNeeded && $restrictedBy != "") {
    WriteLog("The attack is restricted by " . CardLink($restrictedBy, $restrictedBy) . ". Reverting the gamestate.");
    RevertGamestate();
    return true;
  }
  return $restrictedBy;
}

function EffectPlayCardConstantRestriction($cardID, $type, &$restriction, $phase)
{
  global $currentTurnEffects, $currentPlayer, $turn;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      $effectArr = explode(",", $currentTurnEffects[$i]);
      $effectID = $effectArr[0];
      switch ($effectID) {
        case "OUT187":
          if (in_array(GamestateSanitize(NameOverride($cardID, $currentPlayer)), $effectArr) && CardType($cardID) == "DR" && ($turn[0] == "A" || $turn[0] == "D" || $turn[0] == "INSTANT")) $restriction = "OUT187";
          break;
        default:
          break;
      }
    }
  }
  return $restriction != "";
}

function EffectPlayCardRestricted($cardID, $type, $from, $revertNeeded = false, $resolutionCheck = false)
{
  global $currentTurnEffects, $currentPlayer;
  $restrictedBy = "";
  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      $effectArr = explode(",", $currentTurnEffects[$i]);
      $effectID = $effectArr[0];
      switch ($effectID) {
        case "ARC162":
          if (GamestateSanitize(NameOverride($cardID)) == $effectArr[1]) $restrictedBy = "ARC162";
          break;
        case "DTD226":
          if ($from != "PLAY" && !IsStaticType(CardType($cardID)) && GamestateSanitize(CardName($cardID)) == $effectArr[1]) $restrictedBy = "DTD226";
          break;
        case "WarmongersWar":
          // warmongers processing for meld cards handled in AddPrePitchDecisionQueue
          if (DelimStringContains($type, "A") && !HasMeld($cardID) && CardType($cardID) != "W") $restrictedBy = "DTD230";
          break;
        case "WarmongersPeace":
          // !str_contains(GetAbilityTypes($cardID), "I") should allow discarding attack actions for instant abilities under peace
          if (($type == "AA" && !str_contains(GetAbilityTypes($cardID), "I")) || (TypeContains($cardID, "W", $currentPlayer) && GetResolvedAbilityType($cardID) != "I")) $restrictedBy = "DTD230";
          break;
        case "HNT115":
          if (IsWeapon($cardID, $from) && !WeaponWithNonAttack($cardID, $from)) $restrictedBy = "HNT115";
          break;
        case "HNT148":
        case "HNT149":
          if (!$resolutionCheck) {
            if (!SearchCurrentTurnEffects("HNT167", $currentPlayer) && !TalentContains($cardID, "DRACONIC") && $from != "PLAY" && $from != "EQUIP" && $from != "CHAR" && !str_contains(GetAbilityTypes($cardID), "I")) {
              if (TypeContains($cardID, "AA")) {
                // this case is needed because brand with cinderclaw isn't set to become active until after the attack is played
                $restrict = true;
                for ($j = 0; $j < count($currentTurnEffects); $j += CurrentTurnEffectPieces()) {
                  switch ($currentTurnEffects[$j]) {
                    case "UPR060":
                    case "UPR061":
                    case "UPR062":
                      $restrict = false;
                      break;
                    default:
                      break;
                    }
                }
                if ($restrict) $restrictedBy = $effectID;
              }
              else $restrictedBy = $effectID;
            }
          }
          break;
        default:
          break;
      }
    }
  }
  $foundNullTime = SearchItemForModalities(GamestateSanitize(NameOverride($cardID)), $otherPlayer, "HNT251") != -1;
  $foundNullTime = $foundNullTime || SearchItemForModalities(GamestateSanitize(NameOverride($cardID)), $currentPlayer, "HNT251") != -1;
  if($foundNullTime && $from == "HAND"){
    $restrictedBy = "HNT251";
    return true;
  }
  if ($revertNeeded && $restrictedBy != "") {
    WriteLog("The attack is restricted by " . CardLink($restrictedBy, $restrictedBy) . ". Reverting the gamestate.");
    RevertGamestate();
    return true;
  }
  return $restrictedBy;
}

function EffectCardID($effect)
{
  if ($effect == "") return $effect;
  $arr = explode(",", $effect);
  $id = $arr[0];
  $arr = explode("-", $id);
  return $arr[0];
}

function EffectsAttackYouControlModifiers($cardID, $player)
{
  global $currentTurnEffects;
  $attackModifier = 0;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] == $player) {
      switch ($currentTurnEffects[$i]) {
        case "ARC160-1":
          if (CardType($cardID) == "AA") $attackModifier += 1;
        default:
          break;
      }
    }
  }
  return $attackModifier;
}

function AdministrativeEffect($effectID)
{
  $cardID = substr($effectID, 0, 6);
  switch ($cardID) {
    case "ELE111":
    case "OUT093":
    case "EVO013":
    case "ROS246":
    case "HNT244":
      return true;
    default:
      return false;
  }
}
