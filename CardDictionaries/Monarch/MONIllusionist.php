<?php

  function MONIllusionistPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer, $defPlayer;
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID)
    {
      case "prism_sculptor_of_arc_light": case "prism":
        PlayAura("spectral_shield", $currentPlayer);
        return "";
      case "herald_of_triumph_red": case "herald_of_triumph_yellow": case "herald_of_triumph_blue":
        AddCurrentTurnEffect($cardID, $defPlayer);
        return "";
      case "dream_weavers":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "phantasmaclasm_red":
        if(!IsAllyAttackTarget()) {
          AddDecisionQueue("SHOWHANDWRITELOG", $otherPlayer, "<-", 1);
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
          AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
          AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
          AddDecisionQueue("WRITELOG", $currentPlayer, "<0> was put on the bottom of the deck.", 1);
          AddDecisionQueue("ADDBOTDECK", $otherPlayer, "Skip", 1);
          AddDecisionQueue("DRAW", $otherPlayer, "-");
        }
        return "";
      case "prismatic_shield_red": PlayAura("spectral_shield", $currentPlayer, 3); return "";
      case "prismatic_shield_yellow": PlayAura("spectral_shield", $currentPlayer, 2); return "";
      case "prismatic_shield_blue": PlayAura("spectral_shield", $currentPlayer, 1); return "";
      case "phantasmify_red": case "phantasmify_yellow": case "phantasmify_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      default: return "";
    }
  }

  function MONIllusionistHitEffect($cardID)
  {
    global $combatChainState, $CCS_GoesWhereAfterLinkResolves, $mainPlayer, $defPlayer;
    switch($cardID)
    {
      case "herald_of_erudition_yellow":
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        Draw($mainPlayer);
        Draw($mainPlayer);
        break;
      case "herald_of_judgment_yellow":
        if(!IsAllyAttackTarget()) {
          AddCurrentTurnEffect($cardID, $defPlayer);
          AddNextTurnEffect($cardID, $defPlayer);
        }
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        break;
      case "herald_of_triumph_red": case "herald_of_triumph_yellow": case "herald_of_triumph_blue": 
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        break;
      case "herald_of_protection_red": case "herald_of_protection_yellow": case "herald_of_protection_blue":
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        PlayAura("spectral_shield", $mainPlayer);
        break;
      case "herald_of_ravages_red": case "herald_of_ravages_yellow": case "herald_of_ravages_blue":
        DealArcane(1, 0, "PLAYCARD", $cardID, false, $mainPlayer);
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        break;
      case "herald_of_rebirth_red": case "herald_of_rebirth_yellow": case "herald_of_rebirth_blue":
        AddDecisionQueue("FINDINDICES", $mainPlayer, $cardID);
        AddDecisionQueue("MAYCHOOSEDISCARD", $mainPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEDISCARD", $mainPlayer, "-", 1);
        AddDecisionQueue("MULTIADDTOPDECK", $mainPlayer, "-", 1);
        AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
        AddDecisionQueue("WRITELOG", $mainPlayer, "<0> was selected.", 1);
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        break;
      case "herald_of_tenacity_red": case "herald_of_tenacity_yellow": case "herald_of_tenacity_blue": 
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        break;
      case "wartune_herald_red": case "wartune_herald_yellow": case "wartune_herald_blue": 
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        AddSoul($cardID, $mainPlayer, "CC");
        break;
      default: break;
    }
  }

  function HasPhantasm($cardID)
  {
    switch($cardID)
    {
      case "herald_of_erudition_yellow": case "herald_of_judgment_yellow": case "herald_of_triumph_red": case "herald_of_triumph_yellow": case "herald_of_triumph_blue": case "herald_of_protection_red":
      case "herald_of_protection_yellow": case "herald_of_protection_blue": case "herald_of_ravages_red": case "herald_of_ravages_yellow": case "herald_of_ravages_blue": case "herald_of_rebirth_red":
      case "herald_of_rebirth_yellow": case "herald_of_rebirth_blue": case "herald_of_tenacity_red": case "herald_of_tenacity_yellow": case "herald_of_tenacity_blue": case "wartune_herald_red":
      case "wartune_herald_yellow": case "wartune_herald_blue": case "phantasmaclasm_red": case "enigma_chimera_red": case "enigma_chimera_yellow": case "enigma_chimera_blue":
      case "spears_of_surreality_red": case "spears_of_surreality_yellow": case "spears_of_surreality_blue": return true;
      case "fractal_replication_red": return FractalReplicationStats("HasPhantasm");
      case "miraging_metamorph_red": case "coalescence_mirage_red": case "coalescence_mirage_yellow": case "coalescence_mirage_blue": case "phantasmal_haze_red": case "phantasmal_haze_yellow": case "phantasmal_haze_blue": return true;
      case "dunebreaker_cenipai_red": case "dunebreaker_cenipai_yellow": case "dunebreaker_cenipai_blue": case "embermaw_cenipai_red": case "embermaw_cenipai_yellow": case "embermaw_cenipai_blue": case "frightmare_red": case "UPR551": return true;
      case "phantasmal_symbiosis_yellow": case "spectral_procession_red": case "spectral_prowler_red": case "spectral_prowler_yellow": case "spectral_prowler_blue": case "spectral_rider_red": case "spectral_rider_yellow": case "spectral_rider_blue":
      case "phantom_tidemaw_blue":
        return true;
      default: return false;
    }
  }

  function IsPhantasmActive()
  {
    global $CombatChain, $mainPlayer, $combatChainState, $CCS_WeaponIndex;
    if(!$CombatChain->HasCurrentLink()) return false;
    $attackID = $CombatChain->AttackCard()->ID();
    if((SearchCurrentTurnEffects("dream_weavers", $mainPlayer) && CardType($attackID) == "AA") || SearchCurrentTurnEffects("passing_mirage_blue", $mainPlayer) || SearchCurrentTurnEffects("semblance_blue", $mainPlayer) || SearchCurrentTurnEffects("miragai", $mainPlayer)) { return false; }
    if(SearchCurrentTurnEffectsForCycle("veiled_intentions_red", "veiled_intentions_yellow", "veiled_intentions_blue", $mainPlayer)) return true;
    if(SearchCurrentTurnEffectsForCycle("phantasmify_red", "phantasmify_yellow", "phantasmify_blue", $mainPlayer)) return true;
    if(SearchCurrentTurnEffectsForCycle("transmogrify_red", "transmogrify_yellow", "transmogrify_blue", $mainPlayer)) return true;
    if($combatChainState[$CCS_WeaponIndex] != "-1" && DelimStringContains(CardSubType($attackID), "Ally"))
    {
      $allies = &GetAllies($mainPlayer);
      if(isset($allies[$combatChainState[$CCS_WeaponIndex] + 4])){
        if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "ash")) return true;
        else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "dust_from_the_golden_plains_red") && !CardNameContains($allies[$combatChainState[$CCS_WeaponIndex]], "Themai")) return true;
        else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "dust_from_the_red_desert_red") && !CardNameContains($allies[$combatChainState[$CCS_WeaponIndex]], "Vynserakai")) return true;
        else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "dust_from_the_shadow_crypts_red") && !CardNameContains($allies[$combatChainState[$CCS_WeaponIndex]], "Nekria")) return true;
        else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "dust_from_the_chrome_caverns_red") && !CardNameContains($allies[$combatChainState[$CCS_WeaponIndex]], "Cromai")) return true;
        else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "dust_from_stillwater_shrine_red") && !CardNameContains($allies[$combatChainState[$CCS_WeaponIndex]], "Miragai")) return true;  
        else if(DelimStringContains($allies[$combatChainState[$CCS_WeaponIndex] + 4], "dust_from_the_fertile_fields_red") && !CardNameContains($allies[$combatChainState[$CCS_WeaponIndex]], "Ouvia")) return true;
      }
    }
    return HasPhantasm($attackID);
  }

  function ProcessPhantasmOnBlock($index)
  {
    global $mainPlayer;
    if(IsPhantasmActive() && DoesBlockTriggerPhantasm($index) && SearchLayersForCardID("PHANTASM") == -1) AddLayer("LAYER", $mainPlayer, "PHANTASM");
  }

  function DoesBlockTriggerPhantasm($index)
  {
    global $CombatChain, $mainPlayer, $defPlayer, $attackModifiers;
    $attackModifiers = [];
    $card = $CombatChain->Card($index);
    $defendingCardType = CardType($card->ID());
    if($defendingCardType != "AA") return false;
    if(ClassContains($card->ID(), "ILLUSIONIST", $defPlayer)) return false;
    if(PowerCantBeModified($card->ID())) return AttackValue($card->ID()) >= 6;
    $attackValue = ModifiedAttackValue($card->ID(), $defPlayer, "CC", source:$card->ID());
    $attackValue += AuraAttackModifiers($index, $attackModifiers, onBlock: true);
    $attackValue += $card->AttackValue();//Combat chain attack modifier
    return $attackValue >= 6;
  }

  function IsPhantasmStillActive()
  {
    global $combatChain, $CombatChain, $mainPlayer, $combatChainState, $CCS_WeaponIndex;
    if(count($combatChain) == 0) return false;
    $blockGreaterThan6 = false;
    for($i=CombatChainPieces(); $i<count($combatChain); $i+=CombatChainPieces())
    {
      if(DoesBlockTriggerPhantasm($i)) $blockGreaterThan6 = true;
    }
    if(!$blockGreaterThan6) return false;
    if((SearchCurrentTurnEffects("dream_weavers", $mainPlayer) && CardType($CombatChain->AttackCard()->ID()) == "AA") || SearchCurrentTurnEffects("passing_mirage_blue", $mainPlayer) || SearchCurrentTurnEffects("semblance_blue", $mainPlayer) || SearchCurrentTurnEffects("miragai", $mainPlayer)) { return false; }
    return true;
  }

  function PhantasmLayer()
  {
    global $CombatChain, $mainPlayer, $combatChainState, $CCS_WeaponIndex, $CS_NumPhantasmAADestroyed, $defPlayer, $turn, $layers;
    if(IsPhantasmStillActive())
    {
      $attackID = $CombatChain->AttackCard()->ID();
      if($combatChainState[$CCS_WeaponIndex] != "-1" && DelimStringContains(CardSubType($attackID), "Ally")) DestroyAlly($mainPlayer, $combatChainState[$CCS_WeaponIndex]);
      if(ClassContains($attackID, "ILLUSIONIST", $mainPlayer)) {
        GhostlyTouchPhantasmDestroy();
        if(!SubtypeContains($attackID, "Aura", $mainPlayer)) PhantomTidemawDestroy($mainPlayer);//Aura destroy is handled elsewhere
      }
      if($attackID != "phantom_tidemaw_blue") AttackDestroyed($attackID); //Need to skip PhantomTidemaw Phatasm once or it triggers twice. Here and in DestroyAura
      if(CardType($attackID) == "AA") {
        IncrementClassState($mainPlayer, $CS_NumPhantasmAADestroyed);
      } else if(SubtypeContains($attackID, "Aura", $mainPlayer)) {
        DestroyAura($mainPlayer, $combatChainState[$CCS_WeaponIndex]);
      }
      CloseCombatChain();
      ProcessDecisionQueue();
    }
    else {
      $turn[0] = "A";
      $currentPlayer = $mainPlayer;
      for($i=count($layers)-LayerPieces(); $i >= 0; $i-=LayerPieces())
      {
        if($layers[$i] == "DEFENDSTEP" || ($layers[$i] == "LAYER" && $layers[$i+2] == "PHANTASM"))
        {
          for($j=$i; $j<($i+LayerPieces()); ++$j) unset($layers[$j]);
        }
      }
      $layers = array_values($layers);
    }
  }

  function HasSpectra($cardID)
  {
    switch($cardID)
    {
      case "arc_light_sentinel_yellow": case "genesis_yellow": case "parable_of_humility_yellow": case "merciful_retribution_yellow": case "ode_to_wrath_yellow": return true;
      case "shimmers_of_silver_blue": case "haze_bending_blue": case "passing_mirage_blue": case "pierce_reality_blue": return true;
      default: return false;
    }
  }

  function TheLibrarianEffect($player, $index)
  {
    $arsenal = &GetArsenal($player);
    --$arsenal[$index+2];
    ++$arsenal[$index+3];
    Draw($player);
    if($arsenal[$index+3] == 3) MentorTrigger($player, $index);
  }

?>
