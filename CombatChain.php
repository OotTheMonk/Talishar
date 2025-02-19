<?php

function ProcessHitEffect($cardID, $from = "-", $uniqueID = -1)
{
  global $CombatChain, $layers, $mainPlayer, $chainLinks;
  WriteLog("Processing hit effect for " . CardLink($cardID, $cardID));
  if (!DelimStringContains(CardType($cardID), "W")) {//stops flicks from interacting with tarpit trap
    if (CardType($CombatChain->AttackCard()->ID()) == "AA" && SearchCurrentTurnEffects("OUT108", $mainPlayer, count($layers) < LayerPieces())) {
      WriteLog("Hit effect prevented by " . CardLink("OUT108", "OUT108"));
      return true;
    }
  }
  //check tarpit trap against flicked kiss of death if the current attack is a dagger
  if (CardType($cardID) == "AA" && SubtypeContains($cardID, "Dagger", $mainPlayer) && SearchCurrentTurnEffects("OUT108", $mainPlayer, count($layers) < LayerPieces())) {
    WriteLog("Hit effect prevented by " . CardLink("OUT108", "OUT108"));
    return true;
  }
  $cardID = ShiyanaCharacter($cardID);

  $set = CardSet($cardID);
  $class = CardClass($cardID);
  if ($set == "WTR") return WTRHitEffect($cardID);
  else if ($set == "ARC") {
    switch ($class) {
      case "MECHANOLOGIST":
        return ARCMechanologistHitEffect($cardID, $from);
      case "RANGER":
        return ARCRangerHitEffect($cardID, $from);
      case "RUNEBLADE":
        return ARCRunebladeHitEffect($cardID);
      case "WIZARD":
        return ARCWizardHitEffect($cardID);
      case "GENERIC":
        return ARCGenericHitEffect($cardID);
    }
  } else if ($set == "CRU") return CRUHitEffect($cardID);
  else if ($set == "MON") {
    switch ($class) {
      case "BRUTE":
        return MONBruteHitEffect($cardID);
      case "ILLUSIONIST":
        return MONIllusionistHitEffect($cardID);
      case "RUNEBLADE":
        return MONRunebladeHitEffect($cardID);
      case "WARRIOR":
        return MONWarriorHitEffect($cardID);
      case "GENERIC":
        return MONGenericHitEffect($cardID);
      case "NONE":
        return MONTalentHitEffect($cardID);
      default:
        return "";
    }
  } else if ($set == "ELE") {
    switch ($class) {
      case "GUARDIAN":
        return ELEGuardianHitEffect($cardID);
      case "RANGER":
        return ELERangerHitEffect($cardID);
      case "RUNEBLADE":
        return ELERunebladeHitEffect($cardID);
      default:
        return ELETalentHitEffect($cardID);
    }
  } else if ($set == "EVR") return EVRHitEffect($cardID);
  else if ($set == "UPR") return UPRHitEffect($cardID);
  else if ($set == "DYN") return DYNHitEffect($cardID, $from, $CombatChain->AttackCard()->ID());
  else if ($set == "OUT") return OUTHitEffect($cardID, $from);
  else if ($set == "DTD") return DTDHitEffect($cardID);
  else if ($set == "TCC") return TCCHitEffect($cardID);
  else if ($set == "EVO") return EVOHitEffect($cardID);
  else if ($set == "HVY") return HVYHitEffect($cardID);
  else if ($set == "AKO") return AKOHitEffect($cardID);
  else if ($set == "MST") return MSTHitEffect($cardID, $from);
  else if ($set == "AAZ") return AAZHitEffect($cardID);
  else if ($set == "AUR") return AURHitEffect($cardID);
  else if ($set == "ROS") return ROSHitEffect($cardID);
  else if ($set == "AJV") return AJVHitEffect($cardID);
  else if ($set == "HNT") return HNTHitEffect($cardID, $uniqueID);
  else return -1;
}

function AttackModifier($cardID, $from = "", $resourcesPaid = 0, $repriseActive = -1)
{
  global $mainPlayer, $defPlayer, $CS_Num6PowDisc, $CombatChain, $combatChainState, $mainAuras, $CS_CardsBanished;
  global $CS_NumCharged, $CCS_NumBoosted, $defPlayer, $CS_ArcaneDamageTaken, $CS_NumYellowPutSoul, $CS_NumCardsDrawn;
  global $CS_NumPlayedFromBanish, $CS_NumAuras, $CS_AtksWWeapon, $CS_Num6PowBan, $CS_HaveIntimidated, $chainLinkSummary;
  global $combatChain, $CS_Transcended, $CS_NumBluePlayed, $CS_NumLightningPlayed, $CS_DamageDealt, $CS_NumCranked, $CS_ArcaneDamageDealt;
  global $chainLinks, $chainLinkSummary, $CCS_FlickedDamage;
  if ($repriseActive == -1) $repriseActive = RepriseActive();
  if (HasPiercing($cardID, $from)) return NumEquipBlock() > 0 ? 1 : 0;
  switch ($cardID) {
    case "WTR003":
      return (GetClassState($mainPlayer, $CS_Num6PowDisc) > 0 ? 1 : 0);
    case "WTR080":
      return 1;
    case "WTR081":
      return (ComboActive() ? $resourcesPaid : 0);
    case "WTR082":
      return 1;
    case "WTR083":
      return (ComboActive() ? 1 : 0);
    case "WTR084":
      return (ComboActive() ? 1 : 0);
    case "WTR086":
    case "WTR087":
    case "WTR088":
      return (ComboActive() ? NumAttacksHit() : 0);
    case "WTR089":
    case "WTR090":
    case "WTR091":
      return (ComboActive() ? 3 : 0);
    case "WTR095":
    case "WTR096":
    case "WTR097":
      return (ComboActive() ? 1 : 0);
    case "WTR104":
    case "WTR105":
    case "WTR106":
      return (ComboActive() ? 2 : 0);
    case "WTR110":
    case "WTR111":
    case "WTR112":
      return (ComboActive() ? 1 : 0);
    case "WTR120":
      return 3;
    case "WTR121":
      return 1;
    case "WTR123":
      return $repriseActive ? 6 : 4;
    case "WTR124":
      return $repriseActive ? 5 : 3;
    case "WTR125":
      return $repriseActive ? 4 : 2;
    case "WTR132":
      return TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) && $repriseActive ? 3 : 0;
    case "WTR133":
      return TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) && $repriseActive ? 2 : 0;
    case "WTR134":
      return TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) && $repriseActive ? 1 : 0;
    case "WTR135":
      return 3;
    case "WTR136":
      return 2;
    case "WTR137":
      return 1;
    case "WTR138":
      return 3;
    case "WTR139":
      return 2;
    case "WTR140":
      return 1;
    case "WTR176":
    case "WTR177":
    case "WTR178":
      return NumCardsNonEquipBlocking() < 2 ? 1 : 0;
    case "ARC188":
    case "ARC189":
    case "ARC190":
      return $chainLinkSummary[count($chainLinkSummary) - ChainLinkSummaryPieces()] > 0 ? 2 : 0;
    case "CRU016":
    case "CRU017":
    case "CRU018":
      return GetClassState($mainPlayer, $CS_Num6PowDisc) > 0 ? 1 : 0;
    case "CRU056":
      return ComboActive() ? 2 : 0;
    case "CRU057":
    case "CRU058":
    case "CRU059":
      return ComboActive() ? 1 : 0;
    case "CRU060":
    case "CRU061":
    case "CRU062":
      return ComboActive() ? 1 : 0;
    case "CRU063":
    case "CRU064":
    case "CRU065":
      return NumChainLinks() >= 3 ? 2 : 0;
    case "CRU073":
      return NumAttacksHit();
    case "CRU083":
      return 3;
    case "CRU101":
      return 1 + $combatChainState[$CCS_NumBoosted];
    case "CRU112":
    case "CRU113":
    case "CRU114":
      return $combatChainState[$CCS_NumBoosted];
    case "MON031":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 3 : 0;
    case "MON039":
    case "MON040":
    case "MON041":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 3 : 0;
    case "MON057":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 3 : 0;
    case "MON058":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 2 : 0;
    case "MON059":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 1 : 0;
    case "MON155":
      return GetClassState($mainPlayer, $CS_NumPlayedFromBanish) > 0 ? 2 : 0;
    case "MON171":
    case "MON172":
    case "MON173":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) > 0 ? 2 : 0;
    case "MON254":
    case "MON255":
    case "MON256":
      return GetClassState($mainPlayer, $CS_CardsBanished) > 0 ? 2 : 0;
    case "MON284":
    case "MON285":
    case "MON286":
      return NumCardsNonEquipBlocking() < 2 ? 1 : 0;
    case "MON287":
    case "MON288":
    case "MON289":
      return NumCardsNonEquipBlocking();
    case "MON290":
    case "MON291":
    case "MON292":
      return count($mainAuras) >= 1 ? 1 : 0;
    case "ELE082":
    case "ELE083":
    case "ELE084":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) >= 1 ? 2 : 0;
    case "ELE134":
    case "ELE135":
    case "ELE136":
      return $from == "ARS" ? 1 : 0;
    case "EVR038":
      return (ComboActive() ? 3 : 0);
    case "EVR040":
      return (ComboActive() ? 2 : 0);
    case "EVR041":
    case "EVR042":
    case "EVR043":
      return (ComboActive() ? NumChainLinksWithName("Hundred Winds") - 1 : 0);
    case "EVR063":
      return 3;
    case "EVR064":
      return 2;
    case "EVR065":
      return 1;
    case "EVR105":
      return (GetClassState($mainPlayer, $CS_NumAuras) >= 2 ? 1 : 0);
    case "EVR116":
    case "EVR117":
    case "EVR118":
      return (GetClassState($mainPlayer, $CS_NumAuras) > 0 ? 3 : 0);
    case "DVR002":
      return GetClassState($mainPlayer, $CS_AtksWWeapon) >= 1 ? 1 : 0;
    case "RVD009":
      return GetClassState($mainPlayer, $CS_HaveIntimidated) > 0 ? 2 : 0;
    case "UPR048":
      return (NumChainLinksWithName("Phoenix Flame") >= 2 ? 2 : 0);
    case "UPR050":
      return 1;
    case "UPR098":
      return (RuptureActive() ? 3 : 0);
    case "UPR101":
      return (NumDraconicChainLinks() >= 2 ? 1 : 0);
    case "UPR162":
      return 3;
    case "UPR163":
      return 2;
    case "UPR164":
      return 1;
    case "DYN047":
      return (ComboActive() ? 2 : 0);
    case "DYN056":
    case "DYN057":
    case "DYN058":
      return (ComboActive() ? 1 : 0);
    case "DYN059":
    case "DYN060":
    case "DYN061":
      return (ComboActive() ? 4 : 0);
    case "OUT018":
    case "OUT019":
    case "OUT020":
      return (NumAttackReactionsPlayed() > 0 ? 4 : 0);
    case "OUT051":
      return (ComboActive() ? 2 : 0);
    case "OUT054":
      return 1;
    case "OUT062":
    case "OUT063":
    case "OUT064":
      return (ComboActive() ? 1 : 0);
    case "OUT074":
    case "OUT075":
    case "OUT076":
      return (ComboActive() ? 2 : 0);
    case "OUT133":
    case "OUT134":
    case "OUT135":
      return NumCardsDefended() < 2 ? 3 : 0;
    case "OUT181":
      return 1;
    case "OUT207":
    case "OUT208":
    case "OUT209":
      return (CachedNumActionBlocked() > 0 ? 2 : 0);
    case "OUT210":
    case "OUT211":
    case "OUT212":
      return (CachedNumActionBlocked() > 0 ? -2 : 0);
    case "DTD046":
      return GetClassState($mainPlayer, $CS_NumYellowPutSoul) > 0 ? 5 : 0;
    case "DTD097":
    case "DTD098":
    case "DTD099":
      return (SearchPitchForColor($mainPlayer, 2) > 0 ? 2 : 0);
    case "DTD121":
    case "DTD122":
    case "DTD123":
      return GetClassState($mainPlayer, $CS_Num6PowBan) > 0 ? 1 : 0;
    case "DTD181":
    case "DTD182":
    case "DTD183":
      $theirSoul = &GetSoul($defPlayer);
      return (count($theirSoul) > 0 ? 2 : 0);
    case "TCC013":
    case "TCC024":
      return EvoUpgradeAmount($mainPlayer);
    case "EVO009":
      return EvoUpgradeAmount($mainPlayer) >= 4;
    case "EVO054":
    case "EVO055":
    case "EVO056":
      return EvoUpgradeAmount($mainPlayer) >= 4 ? 3 : 0;
    case "EVO067":
    case "EVO068":
    case "EVO069":
      return EvoUpgradeAmount($mainPlayer);
    case "EVO210":
    case "EVO211":
    case "EVO212":
    case "EVO213":
    case "EVO214":
    case "EVO215":
      return NumEquipBlock();
    case "HVY013":
      $hand = &GetHand($defPlayer);
      return $combatChain[0] == "HVY013" && count($hand) == 0 ? 3 : 0;
    case "HVY017":
    case "HVY018":
    case "HVY019":
      return GetClassState($mainPlayer, $CS_HaveIntimidated) > 0 ? 2 : 0;
    case "HVY112":
      return 3;
    case "HVY113":
      return 2;
    case "HVY114":
      return 1;
    case "HVY146":
    case "HVY147":
    case "HVY148":
      return GetClassState($mainPlayer, $CS_NumCardsDrawn) >= 1 ? 1 : 0;
    case "MST082":
      return GetClassState($mainPlayer, $CS_Transcended) > 0 ? 2 : 0;
    case "MST087":
    case "MST088":
    case "MST089":
    case "MST090":
      return GetClassState($mainPlayer, $CS_NumBluePlayed) > 1 ? 2 : 0;
    case "MST103":
      return NumAttackReactionsPlayed() > 2 ? 3 : 0;
    case "MST112":
    case "MST113":
    case "MST114":
      return NumAttackReactionsPlayed() > 1 ? 2 : 0;
    case "MST127":
    case "MST128":
    case "MST129":
      return NumAttackReactionsPlayed() > 0 ? 1 : 0;
    case "MST191":
      return CachedNumActionBlocked() > 0 ? 2 : 0;
    case "AUR007":
    case "AUR015":
    case "ROS009":
      return GetClassState($mainPlayer, $CS_NumLightningPlayed) > 0;
    case "ROS031":
      return SearchCount(SearchMultiZone($mainPlayer, "MYBANISH:talent=EARTH")) >= 4 ? 4 : 0;
    case "ROS032":
      return SearchCount(SearchMultiZone($mainPlayer, "MYBANISH:talent=EARTH")) >= 4 ? 4 : 0;
    case "ROS058":
    case "ROS059":
    case "ROS060":
      return SearchCount(SearchMultiZone($mainPlayer, "MYBANISH:talent=EARTH")) >= 4 ? 4 : 0;
    case "ROS101":
    case "ROS102":
    case "ROS103":
      return (GetClassState($mainPlayer, $CS_DamageDealt) + GetClassState($mainPlayer, $CS_ArcaneDamageDealt)) > 0 ? 1 : 0;
    case "ROS134":
    case "ROS135":
    case "ROS136":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) > 0 ? 2 : 0;
    case "ROS140":
    case "ROS141":
    case "ROS142":
      return (GetClassState($mainPlayer, $CS_NumAuras) > 0 ? 2 : 0);
    case "AIO009":
      return (GetClassState($mainPlayer, $CS_NumCranked)) > 0 ? 1 : 0;
    case "AJV002":
      return (CheckHeavy($mainPlayer)) ? 2 : 0;
    case "HNT041":
    case "HNT042":
    case "HNT043":
      return (IsHeroAttackTarget() && CheckMarked($defPlayer)) ? 1 : 0;
    case "HNT086":
    case "HNT087":
    case "HNT088":
      return isPreviousLinkDraconic() ? 1 : 0;
    case "HNT101":
      return 4;
    case "HNT116":
      return 3;
    case "HNT119":
    case "HNT120":
    case "HNT121":
      return 1;
    case "HNT146":
      return 1;
    case "HNT152":
      return CheckMarked($defPlayer) ? 2 : 0;
    case "HNT176":
    case "HNT177":
    case "HNT178":
      $numDaggerHits = 0;
        for($i=0; $i<count($chainLinks); ++$i)
        {
          if(CardSubType($chainLinks[$i][0]) == "Dagger" && $chainLinkSummary[$i*ChainLinkSummaryPieces()] > 0) ++$numDaggerHits;
        }
        $numDaggerHits += $combatChainState[$CCS_FlickedDamage];
      return $numDaggerHits > 0 ? 1 : 0;
    case "HNT199":
      return CheckMarked($defPlayer) ? 4 : 3;
    case "HNT200":
      return CheckMarked($defPlayer) ? 3 : 2;
    case "HNT201":
      return CheckMarked($defPlayer) ? 2 : 1;
    case "HNT205": return 3;
    case "HNT206": return 2;
    case "HNT207": return 1;
    case "HNT235": return CheckMarked($defPlayer) ? 1 : 0;
    case "HNT249":
      return (SearchCurrentTurnEffectsForIndex("HNT249", $mainPlayer) != -1 ? 2 : 0);
    default:
      break;
  }
  switch(CardIdentifier($cardID)) {
    case "skyzyk-1":
      return DoesAttackHaveGoAgain() ? 1 : 0;
    default:
      return 0;
  }
}

function BlockModifier($cardID, $from, $resourcesPaid)
{
  global $defPlayer, $CS_CardsBanished, $mainPlayer, $CS_ArcaneDamageTaken, $CombatChain, $chainLinks, $CS_NumClashesWon, $CS_Num6PowBan, $CS_NumCrouchingTigerCreatedThisTurn;
  global $CS_NumBluePlayed;
  $blockModifier = 0;
  $cardType = CardType($cardID);
  if ($cardType == "AA") $blockModifier += CountCurrentTurnEffects("ARC160-1", $defPlayer);
  if ($cardType == "AA") $blockModifier += CountCurrentTurnEffects("EVR186", $defPlayer);
  if ($cardType == "AA") $blockModifier += CountCurrentTurnEffects("ROGUE802", $defPlayer);
  if ((DelimStringContains($cardType, "E") || SubtypeContains($cardID, "Evo")) && (SearchCurrentTurnEffects("DYN095", $mainPlayer) || SearchCurrentTurnEffects("DYN096", $mainPlayer) || SearchCurrentTurnEffects("DYN097", $mainPlayer))) {
    $countScramblePulse = 0 + CountCurrentTurnEffects("DYN095", $mainPlayer);
    $countScramblePulse += CountCurrentTurnEffects("DYN096", $mainPlayer);
    $countScramblePulse += CountCurrentTurnEffects("DYN097", $mainPlayer);
    $blockModifier -= 1 * $countScramblePulse;
  }
  if (SearchCurrentTurnEffects("ELE114", $defPlayer) && ($cardType == "AA" || DelimStringContains($cardType, "A")) && (TalentContains($cardID, "ICE", $defPlayer) || TalentContains($cardID, "EARTH", $defPlayer) || TalentContains($cardID, "ELEMENTAL", $defPlayer))) $blockModifier += 1;
  if (SearchCurrentTurnEffects("EVO146", $defPlayer) && SubtypeContains($cardID, "Evo", $defPlayer) && ($from == "EQUIP" || $from == "CC")) $blockModifier += CountCurrentTurnEffects("EVO146", $defPlayer);
  $defAuras = &GetAuras($defPlayer);
  $attackID = $CombatChain->AttackCard()->ID();
  for ($i = 0; $i < count($defAuras); $i += AuraPieces()) {
    if ($defAuras[$i] == "WTR072" && CardCost($cardID, $from) >= 3) $blockModifier += 4;
    if ($defAuras[$i] == "WTR073" && CardCost($cardID, $from) >= 3) $blockModifier += 3;
    if ($defAuras[$i] == "WTR074" && CardCost($cardID, $from) >= 3) $blockModifier += 2;
    if ($defAuras[$i] == "WTR046" && $cardType == "E") $blockModifier += 1;
    if ($defAuras[$i] == "ELE109" && DelimStringContains($cardType, "A")) $blockModifier += 1;
    if ($defAuras[$i] == "HVY068" && $cardType == "AA") $blockModifier += 3;
    if ($defAuras[$i] == "HVY069" && $cardType == "AA") $blockModifier += 2;
    if ($defAuras[$i] == "HVY070" && $cardType == "AA") $blockModifier += 1;
  }
  $blockModifier += ItemBlockModifier($cardID);
  switch ($cardID) {
    case "WTR212":
    case "WTR213":
    case "WTR214":
      $blockModifier += $from == "ARS" ? 1 : 0;
      break;
    case "WTR051":
    case "WTR052":
    case "WTR053":
      $blockModifier += ($resourcesPaid >= 6 ? 3 : 0);
      break;
    case "ARC150":
      $blockModifier += (PlayerHasLessHealth($defPlayer) ? 1 : 0);
      break;
    case "CRU187":
      $blockModifier += ($from == "ARS" ? 2 : 0);
      break;
    case "MON075":
    case "MON076":
    case "MON077":
      return GetClassState($mainPlayer, $CS_CardsBanished) >= 3 ? 2 : 0;
    case "MON290":
    case "MON291":
    case "MON292":
      return count($defAuras) >= 1 ? 1 : 0;
    case "ELE227":
    case "ELE228":
    case "ELE229":
      return GetClassState($mainPlayer, $CS_ArcaneDamageTaken) > 0 ? 1 : 0;
    case "EVR050":
    case "EVR051":
    case "EVR052":
      return (CardCost($attackID) == 0 && CardType($attackID) == "AA" ? 2 : 0);
    case "DYN045":
      $blockModifier += (count($chainLinks) >= 3 ? 4 : 0);
      break;
    case "DYN036":
    case "DYN037":
    case "DYN038":
      $blockModifier += SearchCharacter($defPlayer, subtype: "Off-Hand", class: "GUARDIAN") != "" ? 4 : 0;
      break;
    case "DTD107":
      $blockModifier += GetClassState($defPlayer, $CS_Num6PowBan) > 0 ? 6 : 0;
      break;
    case "DTD206":
      $blockModifier += CountCurrentTurnEffects($cardID, $defPlayer);
      break;
    case "EVO060":
      $blockModifier += EvoUpgradeAmount($defPlayer);
      break;
    case "EVO231":
    case "EVO232":
    case "EVO233":
      if (CachedOverpowerActive()) $blockModifier += 2;
      break;
    case "HVY011":
      CountAura("HVY240", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Agility
      CountAura("HVY241", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Might
      break;
    case "HVY052":
      $blockModifier += SearchCurrentTurnEffects($cardID, $defPlayer);
      break;
    case "HVY056":
      CountAura("HVY241", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Might
      CountAura("HVY242", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Vigor
      break;
    case "HVY060":
      $blockModifier += (2 * GetClassState($defPlayer, $CS_NumClashesWon));
      break;
    case "HVY096":
      if (IsWeaponAttack()) $blockModifier += 2;
      break;    
    case "HVY100":
      CountAura("HVY240", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Agility
      CountAura("HVY242", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Vigor
      break;
    case "HVY210":
      if (SearchCurrentTurnEffects($cardID, $defPlayer, true)) $blockModifier += 2;
      break;
    case "MST086":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += SearchPitchForColor($defPlayer, 3);
      break;
    case "MST091":
      if (GetClassState($defPlayer, $CS_NumBluePlayed) > 1) $blockModifier += 2;
      break;
    case "MST163":
      if (GetClassState($defPlayer, $CS_NumCrouchingTigerCreatedThisTurn) > 0) $blockModifier += 3;
      break;
    case "ROS029": //helm of lignum vitae
      if (SearchCount(SearchBanish($defPlayer, talent: "EARTH")) >= 4) $blockModifier += 1;
      break;
    case "AIO003":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += CountCurrentTurnEffects($cardID, $defPlayer);
        break;
    case "AIO005":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += CountCurrentTurnEffects($cardID, $defPlayer);
        break;
    case "HNT192":
    case "HNT193":
    case "HNT194":
    case "HNT195":
      if (NumAttackReactionsPlayed() > 0) $blockModifier += 1;
      break;
    case "HNT216":
    case "HNT217":
    case "HNT218":
    case "HNT219":
      if (IsWeaponAttack()) $blockModifier += 1;
      break;
    case "HNT215":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += 2;
      break;
    default:
      break;
  }
  return $blockModifier;
}

function PlayBlockModifier($cardID)
{
  switch ($cardID) {
    case "CRU189":
      return 4;
    case "CRU190":
      return 3;
    case "CRU191":
      return 2;
    case "ELE125":
      return 4;
    case "ELE126":
      return 3;
    case "ELE127":
      return 2;
    case "DTD041":
      return 5;
    case "DTD042":
      return 4;
    case "DTD043":
      return 3;
    default:
      return 0;
  }
}

function OnDefenseReactionResolveEffects($from, $cardID)
{
  global $currentTurnEffects, $mainPlayer, $defPlayer, $combatChain, $CS_NumBlueDefended;
  $blockedFromHand = 0;
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if(DelimStringContains(CardType($combatChain[$i]), "DR")) {
      if (ColorContains($combatChain[$i], 3, $defPlayer)) IncrementClassState($defPlayer, $CS_NumBlueDefended);
      if ($combatChain[$i + 2] == "HAND") ++$blockedFromHand;
    }
  }
  switch ($combatChain[0]) {
    case "CRU051":
    case "CRU052":
      EvaluateCombatChain($totalAttack, $totalBlock);
      break;
    case "DTD205":
      if (!SearchCurrentTurnEffects("DTD205", $mainPlayer)) {
        $nonEquipBlockingCards = GetChainLinkCards($defPlayer, "", "E", exclCardSubTypes: "Evo");
        if ($nonEquipBlockingCards != "") {
          $options = GetChainLinkCards($defPlayer);
          AddCurrentTurnEffect("DTD205", $mainPlayer);
          AddDecisionQueue("CHOOSECOMBATCHAIN", $mainPlayer, $options);
          AddDecisionQueue("HALVEBASEDEFENSE", $defPlayer, "-", 1);
        }
      }
      break;
    default:
      break;
  }
  switch ($cardID) {
    case "CRU126":
      if (!IsAllyAttacking()) AddLayer("TRIGGER", $defPlayer, $cardID);
      AddDecisionQueue("TRIPWIRETRAP", $defPlayer, "-", 1);
      break;
    case "CRU127":
      if (!IsAllyAttacking()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "CRU128":
      AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT102":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT103":
      if (!IsAllyAttacking() && DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT104":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT106":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT107":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT108":
      if (DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT171":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT172":
      if (!IsAllyAttacking() && DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "OUT173":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "HNT052":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "HNT162":
      if (ColorContains($combatChain[0], 1, $mainPlayer)) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "HNT191":
      if (!IsAllyAttacking() && DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "HNT214":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "HNT253":
      if (DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
  }
  if ($blockedFromHand > 0 && SearchCharacterActive($mainPlayer, "ELE174", true) && (TalentContains($combatChain[0], "LIGHTNING", $mainPlayer) || TalentContains($combatChain[0], "ELEMENTAL", $mainPlayer))) {
    AddLayer("TRIGGER", $mainPlayer, "ELE174");
  }
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $defPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "OUT005":
        case "OUT006":
          $count = ModifyBlockForType("DR", -1); //AR is handled in OnBlockResolveEffects
          $remove = $count > 0;
          break;
        default:
          break;
      }
    } else {
      switch ($currentTurnEffects[$i]) {
        case "DTD198":
          if ($from == "HAND") CallDownLightning();
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  ProcessMirageOnBlock(count($combatChain) - CombatChainPieces());
}

function OnBlockResolveEffects($cardID = "")
{
  global $combatChain, $defPlayer, $mainPlayer, $currentTurnEffects, $combatChainState, $CCS_WeaponIndex, $CombatChain, $CS_NumBlueDefended;
  //This is when blocking fully resolves, so everything on the chain from here is a blocking card except the first
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (SearchCurrentTurnEffects("ROGUE802", $defPlayer) && CardType($combatChain[$i]) == "AA") CombatChainPowerModifier($i, 1);
    $effectAttackModifier = EffectsAttackYouControlModifiers($combatChain[$i], $defPlayer);
    if ($effectAttackModifier != 0) CombatChainPowerModifier($i, $effectAttackModifier);
    $auraAttackModifier = AurasAttackYouControlModifiers($combatChain[$i], $defPlayer);
    if ($auraAttackModifier != 0) CombatChainPowerModifier($i, $auraAttackModifier);
    else ProcessPhantasmOnBlock($i);
    ProcessMirageOnBlock($i);
  }
  if ($CombatChain->HasCurrentLink()) {
    switch ($combatChain[0]) {
      case "CRU051":
      case "CRU052":
        EvaluateCombatChain($totalAttack, $totalBlock);
        break;
      case "ELE004":
        if (SearchCurrentTurnEffects($combatChain[0], $defPlayer)) {
          AddLayer("TRIGGER", $defPlayer, $combatChain[0]);
        }
        break;
      case "OUT185":
        $NumActionsBlocking = NumActionsBlocking();
        for ($i = 0; $i < $NumActionsBlocking; ++$i) {
          AddLayer("TRIGGER", $defPlayer, $combatChain[0]);
        }
        break;
      case "DTD205":
        $nonEquipBlockingCards = "";
        if (!SearchCurrentTurnEffects("DTD205", $mainPlayer)) {
          $nonEquipBlockingCards = GetChainLinkCards($defPlayer, "", exclCardTypes: "E", exclCardSubTypes: "Evo");
          if ($nonEquipBlockingCards != "") {
            $options = GetChainLinkCards($defPlayer);
            AddCurrentTurnEffect("DTD205", $mainPlayer);
            AddDecisionQueue("CHOOSECOMBATCHAIN", $mainPlayer, $options);
            AddDecisionQueue("HALVEBASEDEFENSE", $mainPlayer, "-", 1);
          }
        }
        break;
      case "HVY095":
        $character = &GetPlayerCharacter($mainPlayer);
        if (NumAttacksBlocking() > 0 && SearchCurrentTurnEffectsForUniqueID($character[$combatChainState[$CCS_WeaponIndex] + 11] == -1)) {
          AddCurrentTurnEffect($combatChain[0], $mainPlayer, "CC", $character[$combatChainState[$CCS_WeaponIndex] + 11]);
        }
        break;
      case "AUR022":
      case "AUR025":
        AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_pay_1_to_buff_".CardLink($cardID, $cardID), 0, 1);
        AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, 1, 1);
        AddDecisionQueue("PAYRESOURCES", $mainPlayer, "<-", 1);
        AddDecisionQueue("ADDTRIGGER", $mainPlayer, $combatChain[0], 1);
        break;
      default:
        break;
    }
  }
  $blockedFromHand = 0;
  $blockedWithIce = 0;
  $blockedWithEarth = 0;
  $blockedWithAura = 0;
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (ColorContains($combatChain[$i], 3, $defPlayer)) IncrementClassState($defPlayer, $CS_NumBlueDefended);
    if ($combatChain[$i + 2] == "HAND") ++$blockedFromHand;
    if (TalentContains($combatChain[$i], "ICE", $defPlayer)) ++$blockedWithIce;
    if (TalentContains($combatChain[$i], "EARTH", $defPlayer)) ++$blockedWithEarth;
    if (SubtypeContains($combatChain[$i], "Aura", $defPlayer)) ++$blockedWithAura;
  }
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (($blockedFromHand >= 2 && $combatChain[$i + 2] == "HAND") || ($blockedFromHand >= 1 && $combatChain[$i + 2] != "HAND")) UnityEffect($combatChain[$i]);
    if($cardID == "" && HasGalvanize($combatChain[$i])) AddLayer("TRIGGER", $defPlayer, $combatChain[$i], $i);
    elseif($cardID != "" && $combatChain[$i] == $cardID && HasGalvanize($combatChain[$i])) AddLayer("TRIGGER", $defPlayer, $cardID, $i);
    if ($cardID != "") { //Code for when a card is pulled as a defending card on the chain
      $defendingCard = $cardID;
      $i = count($combatChain) - CombatChainPieces();
    }
    if (SearchCurrentTurnEffects("HVY104", $mainPlayer) != "" && TypeContains($combatChain[$i], "AA", $defPlayer) && ClassContains($combatChain[0], "WARRIOR", $mainPlayer) && IsHeroAttackTarget() && SearchLayersForCardID("HVY104") == -1) AddLayer("TRIGGER", $mainPlayer, "HVY104", $defPlayer);
    $defendingCard = $combatChain[$i];
    switch ($defendingCard) {//code for Jarl's armor
      case "AJV004":
        $sub = TalentContains($defendingCard, "ICE", $defPlayer) ? 1 : 0; //necessary for a fringe case where the helm but not the other blocking card loses its talent
        if ($blockedWithIce - $sub > 0) AddLayer("TRIGGER", $mainPlayer, $defendingCard, $i);
        break;
      case "AJV005":
        $sub = TalentContains($defendingCard, "EARTH", $defPlayer) == true ? 1 : 0; //necessary for a fringe case where the chest but not the other blocking card loses its talent
        if ($blockedWithEarth - $sub > 0) AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      case "AJV007":
        if ($blockedWithAura > 0) AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      default:
        break;
    }
    switch ($defendingCard) {
      case "EVR018":
        if (!IsAllyAttacking()) AddLayer("TRIGGER", $mainPlayer, $defendingCard);
        else WriteLog("<span style='color:red;'>No frostbite is created because there is no attacking hero when allies attack.</span>");
        break;
      case "MON241":
      case "MON242":
      case "MON243":
      case "MON244":
      case "RVD005":
      case "RVD006"://Ironhide
      case "RVD015"://Pack Call
      case "ELE203"://Rampart of the Ram's Head
      case "MON089"://Phantasmal Footsteps
      case "UPR095"://Flameborn Retribution
      case "UPR182"://Crown of Providence
      case "UPR191":
      case "UPR192":
      case "UPR193"://Flex
      case "UPR194":
      case "UPR195":
      case "UPR196"://Fyendal's Fighting Spirit
      case "UPR203":
      case "UPR204":
      case "UPR205"://Brothers in Arms
      case "DYN152"://Hornet's Sting
      case "OUT099"://Wayfinder's Crest
      case "OUT174"://Vambrace of Determination
      case "DTD047"://Soulbond Resolve
      case "DTD200":
      case "TCC019":
      case "TCC022":
      case "TCC026":
      case "TCC030":
      case "TCC031":
      case "TCC032":
      case "TCC033":
      case "TCC098":
      case "TCC102":
      case "TCC060":
      case "TCC063":
      case "TCC067": // Crowd Control
      case "HVY020":
      case "HVY021":
      case "HVY022":
      case "HVY052":
      case "HVY053":
      case "HVY054":
      case "HVY061":
      case "HVY162":
      case "HVY137":
      case "HVY138":
      case "HVY139":
      case "HVY141":
      case "HVY142":
      case "HVY157":
      case "HVY158":
      case "HVY159":
      case "HVY161":
      case "HVY177":
      case "HVY178":
      case "HVY179":
      case "HVY181":
      case "HVY182":
      case "HVY210":
      case "HVY239"://Clash blocks
      case "HVY648":
      case "MST050":
      case "MST066":
      case "MST160":
      case "MST190":
      case "ASB003":
      case "ASB005":
      case "ASB006":
      case "TER027":
      case "AIO003":
      case "AIO005":
      case "ROS028":
      case "ROS072"://flash of brilliance
      case "AJV013"://Unforgiving Unforgetting
      case "HNT011":
      case "HNT115"://Kabuto of Imperial Authority
      case "HNT246"://Thick Hide Hunter
        AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      case "HVY008":
        $num6Block = 0;
        for ($j = CombatChainPieces(); $j < count($combatChain); $j += CombatChainPieces()) {
          if (ModifiedAttackValue($combatChain[$j], $defPlayer, "CC", "HVY008") >= 6) ++$num6Block;
        }
        if ($num6Block) {
          AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        }
        break;
      case "DTD094":
      case "DTD095":
      case "DTD096":
        if (TalentContains($combatChain[0], "SHADOW", $mainPlayer)) AddCurrentTurnEffect($defendingCard, $defPlayer);
        break;
      case "MST075":
        if (!IsAllyAttacking()) {
          $deck = new Deck($mainPlayer);
          if ($deck->Reveal(1) && PitchValue($deck->Top()) == 3) {
            AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
          }
        }
        break;
      case "AKO019": // Battlefront Bastion
      case "MST203":
      case "MST204":
      case "MST205":
        if (NumCardsBlocking() <= 1) AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      case "ROS114":
        $conditionsMet = 0;
        for ($j = CombatChainPieces(); $j < count($combatChain); $j += CombatChainPieces()) {
          if (CardType($combatChain[$j]) == "AA") {
            ++$conditionsMet; 
            break;
          }
        }
        for ($k = CombatChainPieces(); $k < count($combatChain); $k += CombatChainPieces()) {
          if (DelimStringContains(CardType($combatChain[$k]), "A")) {
            ++$conditionsMet; 
            break;
          }
        }
        if ($conditionsMet == 2 && !IsAllyAttacking()) {
          AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        }
        break;
      case "ROS217":
        AddNextTurnEffect($defendingCard, $defPlayer);
        break;
      case "HNT215":
        $char = &GetPlayerCharacter($defPlayer);
        for ($j = 0; $j < count($char); $j += CharacterPieces()) {
          if ($char[$j] == $defendingCard) $char[$j+7] = "1";
        }
        break;
      default:
        break;
    }
  }
  if ($blockedFromHand > 0 && SearchCharacterActive($mainPlayer, "ELE174", true) && (TalentContains($combatChain[0], "LIGHTNING", $mainPlayer) || TalentContains($combatChain[0], "ELEMENTAL", $mainPlayer))) {
    AddLayer("TRIGGER", $mainPlayer, "ELE174");
  }
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $defPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "DYN115":
        case "DYN116":
          $count = ModifyBlockForType("AA", 0);
          $remove = $count > 0;
          break;
        case "OUT005":
        case "OUT006":
          $count = ModifyBlockForType("AR", 0); //DR could not possibly be blocking at this time, see OnDefenseReactionResolveEffects
          $remove = $count > 0;
          break;
        case "OUT007":
        case "OUT008":
          $count = ModifyBlockForType("A", 0);
          $remove = $count > 0;
          break;
        case "OUT009":
        case "OUT010":
          $count = ModifyBlockForType("E", 0);
          $remove = $count > 0;
          break;
        default:
          break;
      }
    }
    if ($currentTurnEffects[$i + 1] == $mainPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "DTD198":
          if ($blockedFromHand >= 1) CallDownLightning();
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
}

function GetDefendingCardsFromCombatChainLink($chainLink, $defPlayer)
{
  // returns array of equipments played by the defending hero which is still on the chain
  $defendingCards = array();
  for ($i = 0; $i < count($chainLink); $i += ChainLinksPieces()) {
    if ($chainLink[$i + 2] == 1 && $chainLink[$i + 1] == $defPlayer) {
      array_push($defendingCards, $chainLink[$i]);
    }
  }
  return $defendingCards;
}

function BeginningReactionStepEffects()
{
  global $combatChain, $mainPlayer, $CombatChain;
  if (!$CombatChain->HasCurrentLink()) return "";
  switch ($combatChain[0]) {
    case "OUT050":
      if (ComboActive()) {
        AddLayer("TRIGGER", $mainPlayer, "OUT050");
      }
  }
}

function ModifyBlockForType($type, $amount)
{
  global $combatChain, $defPlayer;
  $count = 0;
  for ($i = count($combatChain) - CombatChainPieces(); $i > 0; $i -= CombatChainPieces()) {
    WriteLog($combatChain[$i] . "-" . CardTypeExtended($combatChain[$i]));
    if ($combatChain[$i + 1] != $defPlayer) continue;
    if (!DelimStringContains(CardTypeExtended($combatChain[$i]), $type)) continue;
    ++$count;
    $combatChain[$i + 6] += $amount;
    if ($type == "DR") return $count;
  }
  return $count;
}

function OnBlockEffects($index, $from)
{
  global $currentTurnEffects, $CombatChain, $currentPlayer, $combatChainState, $CCS_WeaponIndex, $defPlayer;
  global $Card_BlockBanner;
  $chainCard = $CombatChain->Card($index);
  $cardType = CardType($chainCard->ID());
  $cardSubtype = CardSubType($chainCard->ID());
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "WTR092":
        case "WTR093":
        case "WTR094":
          if (HasCombo($chainCard->ID())) $chainCard->ModifyDefense(2);
          $remove = true;
          break;
        case "ELE004":
          if ($cardType == "DR") PlayAura("ELE111", $currentPlayer, effectController: $otherPlayer);
          break;
        case "DYN042":
        case "DYN043":
        case "DYN044":
          if (ClassContains($chainCard->ID(), "GUARDIAN", $currentPlayer) && CardSubType($chainCard->ID()) == "Off-Hand") {
            if ($currentTurnEffects[$i] == "DYN042") $amount = 6;
            else if ($currentTurnEffects[$i] == "DYN043") $amount = 5;
            else $amount = 4;
            $chainCard->ModifyDefense($amount);
            $remove = true;
          }
          break;
        case "DYN115":
        case "DYN116":
          if ($cardType == "AA") $chainCard->ModifyDefense(-1);
          break;
        case "OUT005":
        case "OUT006":
          if ($cardType == "AR") $chainCard->ModifyDefense(-1);
          break;
        case "OUT007":
        case "OUT008":
          if (DelimStringContains($cardType, "A")) $chainCard->ModifyDefense(-1);
          if (intval(substr($chainCard->ID(), 3, 3)) > 400 && $chainCard->ID() != "EVO410b") {
            $set = substr($chainCard->ID(), 0, 3);
            $number = intval(substr($chainCard->ID(), 3, 3)) - 400;
            $id = $number;
            if ($number < 100) $id = "0" . $id;
            if ($number < 10) $id = "0" . $id;
            $id = $set . $id;
            if (CardType($id) != $cardType) $chainCard->ModifyDefense(-1);
          }
          break;
        case "OUT009":
        case "OUT010":
          if ($cardType == "E" || DelimStringContains($cardSubtype, "Evo")) $chainCard->ModifyDefense(-1);
          break;
        case $Card_BlockBanner:
          if (DelimStringContains($cardType, "A") || $cardType == "AA") {
            $chainCard->ModifyDefense(1);
            $remove = true;
          }
          break;
        default:
          break;
      }
    } else if ($currentTurnEffects[$i + 1] == $otherPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "MON113":
        case "MON114":
        case "MON115":
          if ($cardType == "AA" && NumAttacksBlocking() == 1) {
            AddCharacterEffect($otherPlayer, $combatChainState[$CCS_WeaponIndex], $currentTurnEffects[$i]);
            WriteLog(CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]) . " gives your weapon +1 for the rest of the turn");
          }
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  $currentTurnEffects = array_values($currentTurnEffects);
  switch ($CombatChain->AttackCard()->ID()) {
    case "CRU079":
    case "CRU080":
      if ($cardType == "AA" && NumAttacksBlocking() == 1) {
        AddCharacterEffect($otherPlayer, $combatChainState[$CCS_WeaponIndex], $CombatChain->AttackCard()->ID());
        WriteLog(CardLink($CombatChain->AttackCard()->ID(), $CombatChain->AttackCard()->ID()) . " got +1 for the rest of the turn.");
      }
      break;
    default:
      break;
  }
  switch ($CombatChain->Card($index)->ID()) {
    case "HVY202":
    case "HVY203":
    case "HVY204":
    case "HVY205":
    case "HVY206":
      AddCurrentTurnEffect($CombatChain->Card($index)->ID(), $defPlayer);
      break;
    default:
      break;
  }
}

function CombatChainCloseAbilities($player, $cardID, $chainLink)
{
  global $chainLinkSummary, $mainPlayer, $defPlayer, $chainLinks;
  switch ($cardID) {
    case "EVR002":
      if (SearchCurrentTurnEffects($cardID, $mainPlayer, true) && $chainLinkSummary[$chainLink * ChainLinkSummaryPieces()] == 0 && $chainLinks[$chainLink][0] == $cardID && $chainLinks[$chainLink][1] == $player) {
        PlayAura("WTR225", $defPlayer);
      }
      break;
    case "UPR189":
      $AttackPowerValue = $chainLinkSummary[$chainLink * ChainLinkSummaryPieces() + 1];
      if ($AttackPowerValue <= 2) {
        Draw($player);
        WriteLog(CardLink($cardID, $cardID) . " drew a card");
      }
      break;
    case "DYN121":
      if ($player == $mainPlayer) PlayerLoseHealth($mainPlayer, GetHealth($mainPlayer));
      break;
    default:
      break;
  }
}

function NumNonEquipmentDefended()
{
  global $combatChain, $defPlayer;
  $number = 0;
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    $cardType = CardType($combatChain[$i]);
    if ($combatChain[$i + 1] == $defPlayer && $cardType != "E" && $cardType != "C" && !DelimStringContains(CardSubType($combatChain[$i]), "Evo")) ++$number;
  }
  return $number;
}

function NumCardsDefended()
{
  global $combatChain, $defPlayer;
  $number = 0;
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    if ($combatChain[$i + 1] == $defPlayer) ++$number;
  }
  return $number;
}

function CombatChainPlayAbility($cardID)
{
  global $combatChain, $defPlayer;
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    switch ($combatChain[$i]) {
      case "EVR122":
        if (ClassContains($cardID, "WIZARD", $defPlayer)) {
          $combatChain[$i + 6] += 2;
          WriteLog(CardLink($combatChain[$i], $combatChain[$i]) . " gets +2 defense");
        }
        break;
      default:
        break;
    }
  }
}

function IsDominateActive()
{
  global $currentTurnEffects, $mainPlayer, $CCS_WeaponIndex, $combatChain, $combatChainState;
  global $CS_NumAuras, $CCS_NumBoosted, $chainLinks, $chainLinkSummary;
  if (count($combatChain) == 0) return false;
  if (SearchCurrentTurnEffectsForCycle("EVR097", "EVR098", "EVR099", $mainPlayer)) return false;
  $characterEffects = GetCharacterEffects($mainPlayer);
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] == $mainPlayer && IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i) && DoesEffectGrantDominate($currentTurnEffects[$i])) return true;
  }
  for ($i = 0; $i < count($characterEffects); $i += CharacterEffectPieces()) {
    if ($characterEffects[$i] == $combatChainState[$CCS_WeaponIndex]) {
      switch ($characterEffects[$i + 1]) {
        case "WTR122":
          return true;
        default:
          break;
      }
    }
  }
  switch ($combatChain[0]) {
    case "WTR095":
    case "WTR096":
    case "WTR097":
      return (ComboActive() ? true : false);
    case "WTR179":
    case "WTR180":
    case "WTR181":
      return true;
    case "ARC080":
      return true;
    case "MON004":
      return true;
    case "MON023":
    case "MON024":
    case "MON025":
      return true;
    case "MON246":
      return SearchDiscard($mainPlayer, "AA") == "";
    case "MON275":
    case "MON276":
    case "MON277":
      return true;
    case "ELE209":
    case "ELE210":
    case "ELE211":
      return HasIncreasedAttack();
    case "EVR027":
    case "EVR028":
    case "EVR029":
      return true;
    case "EVR038":
      return (ComboActive() ? true : false);
    case "EVR076":
    case "EVR077":
    case "EVR078":
      return $combatChainState[$CCS_NumBoosted] > 0;
    case "EVR110":
    case "EVR111":
    case "EVR112":
      return GetClassState($mainPlayer, $CS_NumAuras) > 0;
    case "EVR138":
      $hasDominate = false;
      for ($i = 0; $i < count($chainLinks); ++$i) {
        for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
          $isIllusionist = ClassContains($chainLinks[$i][$j], "ILLUSIONIST", $mainPlayer) || ($j == 0 && DelimStringContains($chainLinkSummary[$i * ChainLinkSummaryPieces() + 3], "ILLUSIONIST"));
          if ($chainLinks[$i][$j + 2] == "1" && $chainLinks[$i][$j] != "EVR138" && $isIllusionist && CardType($chainLinks[$i][$j]) == "AA") {
            if (!$hasDominate) $hasDominate = HasDominate($chainLinks[$i][$j]);
          }
        }
      }
      return $hasDominate;
    case "OUT027":
    case "OUT028":
    case "OUT029":
      return true;
    default:
      break;
  }
  return false;
}

function IsOverpowerActive()
{
  global $combatChain, $mainPlayer, $defPlayer, $currentTurnEffects, $CS_Num6PowBan, $CS_NumItemsDestroyed, $CS_NumAuras;
  if (count($combatChain) == 0) return false;
  if (SearchItemsForCard("EVO096", $mainPlayer) != "" && CardType($combatChain[0]) == "AA" && ClassContains($combatChain[0], "MECHANOLOGIST", $mainPlayer)) {
    return true;
  }
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] == $mainPlayer && IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i) && DoesEffectGrantOverpower($currentTurnEffects[$i])) return true;
    if ($currentTurnEffects[$i + 1] == $mainPlayer && $currentTurnEffects[$i] == "HVY176" && CachedWagerActive()) return true;
  }
  switch ($combatChain[0]) {
    case "DYN068":
      return SearchCurrentTurnEffects("DYN068", $mainPlayer);
    case "DYN088":
      return true;
    case "DYN227":
    case "DYN228":
    case "DYN229":
      return SearchCurrentTurnEffects("DYN227", $mainPlayer);
    case "DYN492a":
      return true;
    case "DTD063":
    case "DTD064":
    case "DTD065":
      return SearchCurrentTurnEffects($combatChain[0], $mainPlayer);
    case "DTD115":
    case "DTD116":
    case "DTD117":
      return GetClassState($mainPlayer, $CS_Num6PowBan) > 0;
    case "EVO054":
    case "EVO055":
    case "EVO056":
      return EvoUpgradeAmount($mainPlayer) >= 3;
    case "EVO102":
    case "EVO103":
    case "EVO104":
      return SearchCurrentTurnEffects($combatChain[0], $mainPlayer);
    case "EVO140":
      return CachedTotalAttack() >= 10;
    case "EVO114":
    case "EVO115":
    case "EVO116":
      return GetClassState($mainPlayer, $CS_NumItemsDestroyed) > 0;
    case "EVO147":
    case "EVO148":
    case "EVO149":
      return SearchItemsByName($mainPlayer, "Hyper Driver") != "";
    case "HVY065":
    case "HVY066":
    case "HVY067":
      return HasIncreasedAttack();
    case "HVY208":
      return CountItem("DYN243", $defPlayer) > 0;
    case "ROS124":
    case "ROS125":
    case "ROS126":
      return GetClassState($mainPlayer, $CS_NumAuras) > 0;
    default:
      break;
  }
  return false;
}

function CanBlock($cardID)//checks if a block is legal, used for cards that force blocks
{
  // TODO;
  return true;
}

function IsWagerActive()
{
  global $combatChainState, $CCS_WagersThisLink;
  return intval($combatChainState[$CCS_WagersThisLink]) > 0;
}

function IsFusionActive()
{
  global $combatChainState, $CCS_AttackFused;
  return intval($combatChainState[$CCS_AttackFused]) > 0;
}

function CombatChainClosedTriggers()
{
  global $chainLinks, $mainPlayer, $defPlayer, $CS_HealthLost, $currentTurnEffects;
  for ($i = 0; $i < count($chainLinks); ++$i) {
    for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
      if ($chainLinks[$i][$j + 1] != $mainPlayer) continue;
      switch ($chainLinks[$i][$j]) {
        case "DTD105":
          $index = FindCharacterIndex($mainPlayer, "DTD105");
          if ($index > -1 && SearchCurrentTurnEffects("DTD105", $mainPlayer, true)) {
            BanishCardForPlayer("DTD105", $mainPlayer, "CC");
            DestroyCharacter($mainPlayer, $index, true);
          }
          break;
        case "DTD137":
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($mainPlayer, "MYHAND", "ARS,-");
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($defPlayer, "MYHAND", "ARS,-");
          break;
        case "DTD138":
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($mainPlayer, "MYARS", "ARS,-");
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($defPlayer, "MYARS", "ARS,-");
          break;
        case "DTD139":
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) {
            $deck = new Deck($mainPlayer);
            $deck->BanishTop();
          }
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) {
            $deck = new Deck($defPlayer);
            $deck->BanishTop();
          }
          break;
        case "DTD146":
        case "DTD147":
        case "DTD148":
          $numRunechant = 0;
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) ++$numRunechant;
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) ++$numRunechant;
          if ($numRunechant > 0) PlayAura("ARC112", $mainPlayer, $numRunechant, effectSource: $chainLinks[$i][$j]);
          break;
        case "DTD143":
        case "DTD144":
        case "DTD145":
          $numLife = 0;
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) ++$numLife;
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) ++$numLife;
          if ($numLife > 0) GainHealth($numLife, $mainPlayer);
          break;
        case "MST237":
          $numEloquence = 0;
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) ++$numEloquence;
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) ++$numEloquence;
          if ($numEloquence > 0) PlayAura("DTD233", $mainPlayer);
          break;
        default:
          break;
      }
    }
  }
  for ($i = count($currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0; $i -= CurrentTurnEffectPieces()) {
    if (!isset($currentTurnEffects[$i + 1])) continue;
    if (explode("-", $currentTurnEffects[$i])[0] == "HNT056" && $currentTurnEffects[$i + 1] == $mainPlayer) {
      $uniqueID = explode("-", $currentTurnEffects[$i])[1];
      $index = FindCharacterIndexUniqueID($mainPlayer, $uniqueID);
      if ($index != -1) DestroyCharacter($mainPlayer, $index);
    }
  }
}


function CacheCombatResult()
{
  global $combatChain, $combatChainState, $CCS_CachedTotalAttack, $CCS_CachedTotalBlock, $CCS_CachedDominateActive, $CCS_CachedOverpowerActive;
  global $CSS_CachedNumActionBlocked, $CCS_CachedNumDefendedFromHand, $CCS_PhantasmThisLink, $CCS_AttackFused, $CCS_WagersThisLink;
  if (count($combatChain) == 0) return;
  $combatChainState[$CCS_CachedTotalAttack] = 0;
  $combatChainState[$CCS_CachedTotalBlock] = 0;
  EvaluateCombatChain($combatChainState[$CCS_CachedTotalAttack], $combatChainState[$CCS_CachedTotalBlock], secondNeedleCheck:true);
  $combatChainState[$CCS_CachedDominateActive] = (IsDominateActive() ? "1" : "0");
  $combatChainState[$CCS_CachedOverpowerActive] = (IsOverpowerActive() ? "1" : "0");
  $combatChainState[$CSS_CachedNumActionBlocked] = NumActionsBlocking();
  if ($combatChainState[$CCS_CachedNumDefendedFromHand] == 0) $combatChainState[$CCS_CachedNumDefendedFromHand] = NumDefendedFromHand();
  $combatChainState[$CCS_WagersThisLink] = (IsWagerActive() ? intval($combatChainState[$CCS_WagersThisLink]) : "0");
  $combatChainState[$CCS_PhantasmThisLink] = (IsPhantasmActive() ? "1" : "0");
  $combatChainState[$CCS_AttackFused] = (IsFusionActive() ? "1" : "0");
}

function CachedTotalAttack()
{
  global $combatChainState, $CCS_CachedTotalAttack;
  return $combatChainState[$CCS_CachedTotalAttack];
}

function CachedTotalBlock()
{
  global $combatChainState, $CCS_CachedTotalBlock;
  return $combatChainState[$CCS_CachedTotalBlock];
}

function CachedDominateActive()
{
  global $combatChainState, $CCS_CachedDominateActive;
  return ($combatChainState[$CCS_CachedDominateActive] == "1" ? true : false);
}

function CachedOverpowerActive()
{
  global $combatChainState, $CCS_CachedOverpowerActive;
  return ($combatChainState[$CCS_CachedOverpowerActive] == "1" ? true : false);
}

function CachedWagerActive()
{
  global $combatChainState, $CCS_WagersThisLink;
  if (isset($combatChainState[$CCS_WagersThisLink])) {
    return ($combatChainState[$CCS_WagersThisLink] >= "1" ? true : false);
  } else return false;
}

function CachedFusionActive()
{
  global $combatChainState, $CCS_AttackFused;
  if (isset($combatChainState[$CCS_AttackFused])) {
    return ($combatChainState[$CCS_AttackFused] == "1" ? true : false);
  } else return false;
}

function CachedPhantasmActive()
{
  global $combatChainState, $CCS_PhantasmThisLink;
  if (isset($combatChainState[$CCS_PhantasmThisLink])) {
    return ($combatChainState[$CCS_PhantasmThisLink] == "1" ? true : false);
  } else return false;
}

function CachedNumDefendedFromHand() //Reprise
{
  global $combatChainState, $CCS_CachedNumDefendedFromHand;
  return $combatChainState[$CCS_CachedNumDefendedFromHand];
}

function CachedNumActionBlocked()
{
  global $combatChainState, $CSS_CachedNumActionBlocked;
  return $combatChainState[$CSS_CachedNumActionBlocked];
}

function IsPiercingActive($cardID)
{
  global $CombatChain, $currentTurnEffects, $mainPlayer;
  if ($CombatChain->HasCurrentLink()) {
    if (HasPiercing($cardID)) return true;
    for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
      if (!isset($currentTurnEffects[$i + 1])) continue;
      if ($currentTurnEffects[$i + 1] == $mainPlayer && HasPiercing($currentTurnEffects[$i])) return true;
    }
  }
  return false;
}

function IsTowerActive()
{
  global $combatChain;
  return (CachedTotalAttack() >= 13 && HasTower($combatChain[0]));
}
