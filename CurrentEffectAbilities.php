<?php


//Return 1 if the effect should be removed
function EffectHitEffect($cardID, $from, $source = "-")
{
  global $combatChainState, $CCS_GoesWhereAfterLinkResolves, $defPlayer, $mainPlayer, $CCS_WeaponIndex, $CombatChain, $CCS_DamageDealt;
  global $CID_BloodRotPox, $CID_Frailty, $CID_Inertia, $Card_LifeBanner, $Card_ResourceBanner, $layers;
  $attackID = $CombatChain->AttackCard()->ID();
  if ($source == "-") {
    if (CardType($attackID) == "AA" && SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, count($layers) < LayerPieces())) {
      WriteLog("Hit effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
      return true;
    }
  }
  else if (CardType($source) == "AA" && SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, count($layers) < LayerPieces())) {
    WriteLog("Hit effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
    return true;
  }
  $effectArr = explode(",", $cardID);
  $cardID = $effectArr[0];
  switch ($cardID) {
    case "warriors_valor_red":
    case "warriors_valor_yellow":
    case "warriors_valor_blue":
      GiveAttackGoAgain();
      break;
    case "natures_path_pilgrimage_red":
    case "natures_path_pilgrimage_yellow":
    case "natures_path_pilgrimage_blue":
      NaturesPathPilgrimageHit();
      break;
    case "pummel_red":
    case "pummel_yellow":
    case "pummel_blue":
      if (IsHeroAttackTarget() && CardType($attackID) == "AA") PummelHit();
      break;
    case "razor_reflex_red":
    case "razor_reflex_yellow":
    case "razor_reflex_blue":
      if (CardType($attackID) == "AA") GiveAttackGoAgain();
      break;
    case "plunder_run_red-1":
    case "plunder_run_yellow-1":
    case "plunder_run_blue-1":
      Draw($mainPlayer);
      return 1;
    case "poison_the_tips_yellow":
      if (IsHeroAttackTarget()) PummelHit();
      break;
    case "mauvrion_skies_red":
    case "mauvrion_skies_yellow":
    case "mauvrion_skies_blue":
      if (ClassContains($attackID, "RUNEBLADE", $mainPlayer)) {
        if ($cardID == "mauvrion_skies_red") $amount = 3;
        else if ($cardID == "mauvrion_skies_yellow") $amount = 2;
        else $amount = 1;
        PlayAura("runechant", $mainPlayer, $amount);
      }
      break;
    case "succumb_to_temptation_yellow":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want your opponent to discard", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZDISCARD", $mainPlayer, "HAND," . $mainPlayer, 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      return 1;
    case "spoils_of_war_red-2":
      PutItemIntoPlayForPlayer("copper", $mainPlayer, 0, 2);
      break;
    case "lumina_ascension_yellow":
      $deck = new Deck($mainPlayer);
      if (!$deck->Reveal()) return;
      $top = $deck->Top(remove: true);
      if (TalentContains($top, "LIGHT", $mainPlayer)) {
        AddSoul($top, $mainPlayer, "DECK");
        GainHealth(1, $mainPlayer);
      } else $deck->AddBottom($top, "DECK");
      break;
    case "seek_enlightenment_red":
    case "seek_enlightenment_yellow":
    case "seek_enlightenment_blue":
      $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL";
      break;
    case "dusk_path_pilgrimage_red":
    case "dusk_path_pilgrimage_yellow":
    case "dusk_path_pilgrimage_blue":
      DuskPathPilgrimageHit();
      break;
    case "shadow_puppetry_red":
      ShadowPuppetryHitEffect();
      break;
    case "eclipse_existence_blue":
      if (count(GetSoul($defPlayer)) > 0) {
        BanishFromSoul($defPlayer);
        LoseHealth(1, $defPlayer);
      }
      break;
    case "warmongers_recital_red":
    case "warmongers_recital_yellow":
    case "warmongers_recital_blue":
      if (substr($from, 0, 5) != "THEIR") $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BOTDECK";
      else $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "THEIRBOTDECK";
      break;
    case "oaken_old_red":
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
    case "mulch_red":
    case "mulch_yellow":
    case "mulch_blue":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to put on the bottom of the deck", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZADDZONE", $mainPlayer, "THEIRBOTDECK", 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    case "snow_under_red":
    case "snow_under_yellow":
    case "snow_under_blue":
      if (IsHeroAttackTarget()) PlayAura("frostbite", $defPlayer, effectController: $mainPlayer);
      break;
    case "frost_lock_blue-2":
      if (IsHeroAttackTarget()) {
        AddCurrentTurnEffect("frost_lock_blue-3", $defPlayer);
        AddNextTurnEffect("frost_lock_blue-3", $defPlayer);
      }
      break;
    case "ice_storm_red-2":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", "ice_storm_red");
      break;
    case "buzz_bolt_red":
    case "buzz_bolt_yellow":
    case "buzz_bolt_blue":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", $cardID);
      break;
    case "force_of_nature_blue-TRIGGER":
      if (HasIncreasedAttack()) Draw($mainPlayer);
      break;
    case "flashfreeze_red-BUFF":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 3, "ATTACKHIT", $cardID);
      break;
    case "ice_quake_red-HIT":
    case "ice_quake_yellow-HIT":
    case "ice_quake_blue-HIT":
      if (IsHeroAttackTarget()) PlayAura("frostbite", $defPlayer, effectController: $mainPlayer);
      break;
    case "chill_to_the_bone_red":
      if (IsHeroAttackTarget()) PlayAura("frostbite", $defPlayer, 3, effectController: $mainPlayer);
      break;
    case "chill_to_the_bone_yellow":
      if (IsHeroAttackTarget()) PlayAura("frostbite", $defPlayer, 2, effectController: $mainPlayer);
      break;
    case "chill_to_the_bone_blue":
      if (IsHeroAttackTarget()) PlayAura("frostbite", $defPlayer, effectController: $mainPlayer);
      break;
    case "shock_charmers":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", $cardID);
      return 1;
    case "shock_striker_red":
    case "shock_striker_yellow":
    case "shock_striker_blue":
      if (IsHeroAttackTarget()) DamageTrigger($defPlayer, 1, "ATTACKHIT", $cardID);
      break;
    case "electrify_red":
    case "electrify_yellow":
    case "electrify_blue":
      if (IsHeroAttackTarget()) {
        if ($cardID == "electrify_red") $damage = 3;
        else if ($cardID == "electrify_yellow") $damage = 2;
        else $damage = 1;
        DamageTrigger($defPlayer, $damage, "ATTACKHIT", $cardID);
        return 1;
      }
      break;
    case "tear_asunder_blue":
      if (IsHeroAttackTarget()) {
        PummelHit();
        PummelHit();
      }
      break;
    case "seek_and_destroy_red":
      if (IsHeroAttackTarget()) {
        AddNextTurnEffect($cardID . "-1", $defPlayer);
      }
      break;
    case "twin_twisters_red-1":
    case "twin_twisters_yellow-1":
    case "twin_twisters_blue-1":
      $idArr = explode("-", $cardID);
      AddCurrentTurnEffectFromCombat($idArr[0] . "-2", $mainPlayer);
      break;
    case "outland_skirmish_red-1":
    case "outland_skirmish_yellow-1":
    case "outland_skirmish_blue-1":
      PutItemIntoPlayForPlayer("copper", $mainPlayer);
      return 1;
    case "life_of_the_party_red-1":
    case "life_of_the_party_yellow-1":
    case "life_of_the_party_blue-1":
      GainHealth(2, $mainPlayer);
      break;
    case "high_striker_red":
    case "high_striker_yellow":
    case "high_striker_blue":
      if ($cardID == "high_striker_red") $amount = 6;
      else if ($cardID == "high_striker_yellow") $amount = 4;
      else $amount = 2;
      PutItemIntoPlayForPlayer("copper", $mainPlayer, 0, $amount);
      return 1;
    case "smashing_good_time_red-1":
    case "smashing_good_time_yellow-1":
    case "smashing_good_time_blue-1":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS:minCost=0;maxCost=2");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
        return 1;
      }
      break;
    case "glistening_steelblade_yellow-1":
      $char = &GetPlayerCharacter($mainPlayer);
      if (IsHeroAttackTarget()) {
        ++$char[$combatChainState[$CCS_WeaponIndex] + 3];
      }
      break;
    case "buckle_blue":
      if(IsHeroAttackTarget()) Mangle();
      break;
    case "cleave_red":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRALLY", 1);
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a target to deal " . $combatChainState[$CCS_DamageDealt] . " damage.");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZDAMAGE", $mainPlayer, $combatChainState[$CCS_DamageDealt] . ",DAMAGE," . $cardID, 1);
      break;
    case "dead_eye_yellow":
      if (IsHeroAttackTarget() && HasAimCounter()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want your opponent to discard", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDISCARD", $mainPlayer, "HAND," . $mainPlayer, 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    case "runic_reaping_red-HIT":
    case "runic_reaping_yellow-HIT":
    case "runic_reaping_blue-HIT":
      if (ClassContains($attackID, "RUNEBLADE", $mainPlayer)) {
        if ($cardID == "runic_reaping_red-HIT") $amount = 3;
        else if ($cardID == "runic_reaping_yellow-HIT") $amount = 2;
        else $amount = 1;
        PlayAura("runechant", $mainPlayer, $amount, true);
      }
      break;
    case "spike_with_bloodrot_red":
      if (IsHeroAttackTarget()) PlayAura($CID_BloodRotPox, $defPlayer, effectController: $mainPlayer);
      break;
    case "spike_with_frailty_red":
      if (IsHeroAttackTarget()) PlayAura($CID_Frailty, $defPlayer, effectController: $mainPlayer);
      break;
    case "spike_with_inertia_red":
      if (IsHeroAttackTarget()) PlayAura($CID_Inertia, $defPlayer, effectController: $mainPlayer);
      break;
    case "melting_point_red":
      if (IsHeroAttackTarget() && HasAimCounter()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRCHAR:minAttack=1;maxAttack=1;type=W;is1h=true");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a weapon to destroy");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
      }
      break;
    case "lace_with_bloodrot_red":
      if (IsHeroAttackTarget()) PlayAura($CID_BloodRotPox, $defPlayer, effectController: $mainPlayer);
      break;
    case "lace_with_frailty_red":
      if (IsHeroAttackTarget()) PlayAura($CID_Frailty, $defPlayer, effectController: $mainPlayer);
      break;
    case "lace_with_inertia_red":
      if (IsHeroAttackTarget()) PlayAura($CID_Inertia, $defPlayer, effectController: $mainPlayer);
      break;
    case "mask_of_shifting_perspectives":
      WriteLog("Mask of Shifting Perspectives lets you sink a card");
      BottomDeck($mainPlayer, true, shouldDraw: true);
      break;
    case "concealed_blade_blue":
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
    case "toxic_tips":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("CHOOSECARD", $mainPlayer, $CID_BloodRotPox . "," . $CID_Frailty . "," . $CID_Inertia);
        AddDecisionQueue("PUTPLAY", $defPlayer, $mainPlayer, 1);
      }
      break;
    case "toxicity_red":
      LoseHealth(5, $defPlayer);
      break;
    case "toxicity_yellow":
      LoseHealth(4, $defPlayer);
      break;
    case "toxicity_blue":
      LoseHealth(3, $defPlayer);
      break;
    case "premeditate_red-1":
      if (IsHeroAttackTarget()) {
        PlayAura("ponder", $mainPlayer);
        return 1;
      }
      break;
    case "beckoning_light_red":
      MZMoveCard($mainPlayer, "MYDISCARD:type=AA", "MYTOPDECK", may: true);
      break;
    case "spirit_of_war_red":
      PlayAura("courage", $mainPlayer);
      break;
    case "light_the_way_red":
    case "light_the_way_yellow":
    case "light_the_way_blue":
      GiveAttackGoAgain();
      break;
    case "lumina_lance_yellow-2":
      Draw($mainPlayer);
      break;
    case "lumina_lance_yellow-3":
      GiveAttackGoAgain();
      break;
    case "ironsong_versus":
      if (IsHeroAttackTarget()) PlayAura("courage", $mainPlayer);
      break;
    case $Card_LifeBanner:
      GainHealth(1, $mainPlayer);
      return 1;
    case $Card_ResourceBanner:
      GainResources($mainPlayer, 1);
      return 1;
    case "hack_to_reality_yellow-HIT":
      if (IsHeroAttackTarget()) {
        MZChooseAndDestroy($mainPlayer, "THEIRAURAS:type=A;maxCost=" . $combatChainState[$CCS_DamageDealt] . "&THEIRAURAS:type=I;maxCost=" . $combatChainState[$CCS_DamageDealt]);
        return 1;
      }
      break;
    case "smash_and_grab_red":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to take");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "GAINCONTROL", 1);
      }
      break;
    case "evo_command_center_yellow_equip":
      Draw($mainPlayer);
      break;
    case "kassai_of_the_golden_sand":
    case "kassai":
      if (IsHeroAttackTarget()) PutItemIntoPlayForPlayer("gold", $mainPlayer, effectController: $mainPlayer);
      return 1;
    case "hood_of_red_sand":
      Draw($mainPlayer);
      break;
    case "talk_a_big_game_blue":
      if ($combatChainState[$CCS_DamageDealt] >= $effectArr[1]) {
        PlayAura("might", $mainPlayer, $effectArr[1]);
        return 1;
      }
      break;
    case "just_a_nick_red-HIT":
      if (IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        $deck->BanishTop("Source-" . $attackID, banishedBy: $attackID);
      }
      break;
    case "maul_yellow-HIT":
      BanishCardForPlayer("crouching_tiger", $mainPlayer, "-", "TT", $mainPlayer);
      BanishCardForPlayer("crouching_tiger", $mainPlayer, "-", "TT", $mainPlayer);
      break;
    case "target_totalizer":
      Draw($mainPlayer);
      break;
    case "burn_up__shock_red":
      if (IsHeroAttackTarget()) DealArcane(4, 1, "PLAYCARD", $cardID, false, $mainPlayer);
      return 1;
    case "arakni_black_widow-HIT":
      AddDecisionQueue("FINDINDICES", $defPlayer, "HAND");
      AddDecisionQueue("SETDQCONTEXT", $defPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("CHOOSEHAND", $defPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $defPlayer, "-", 1);
      AddDecisionQueue("BANISHCARD", $defPlayer, "HAND,-", 1);
      return 0;
    case "arakni_funnel_web-HIT":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      AddDecisionQueue("BANISHCARD", $defPlayer, "MYARS,-,$source", 1);
      return 0;
    case "two_sides_to_the_blade_red-ATTACK":
      if (IsHeroAttackTarget()) MarkHero($defPlayer);
      break;
    case "long_whisker_loyalty_red-MARK":
      $character = &GetPlayerCharacter($mainPlayer);
      if (IsHeroAttackTarget() && $character[$combatChainState[$CCS_WeaponIndex] + 11] == $effectArr[1]) {
        MarkHero($defPlayer);
        return 1;
      }
      break;
    case "searing_gaze_red":
    case "stabbing_pain_red":
      MarkHero($defPlayer);
      break;
    case "twist_and_turn_red":
    case "twist_and_turn_yellow":
    case "twist_and_turn_blue":
      $character = &GetPlayerCharacter($mainPlayer);
      $character[$combatChainState[$CCS_WeaponIndex] + 1] = 2;
      ++$character[$combatChainState[$CCS_WeaponIndex] + 5];
      return 0;
    case "hunt_a_killer_red":
    case "hunt_a_killer_yellow":
    case "hunt_a_killer_blue":
      if (IsHeroAttackTarget()) MarkHero($defPlayer);
      break;
    case "sworn_vengeance_red":
    case "sworn_vengeance_yellow":
    case "sworn_vengeance_blue":
      if (IsHeroAttackTarget()) MarkHero($defPlayer);
      break;
    case "poisoned_blade_red":
    case "poisoned_blade_yellow":
    case "poisoned_blade_blue":
      WriteLog("The " . CardLink($cardID, $cardID) . " drains 1 health");
      LoseHealth(1, $defPlayer);
      break;
    case "savor_bloodshed_red-HIT":
      Draw($mainPlayer, effectSource:"savor_bloodshed_red");
      return 1;
    case "scar_tissue_red":
    case "scar_tissue_yellow":
    case "scar_tissue_blue":
      MarkHero($defPlayer);
      return 1;
    case "take_a_stab_red":
    case "take_a_stab_yellow":
    case "take_a_stab_blue":
      $character = &GetPlayerCharacter($mainPlayer);
      $character[$combatChainState[$CCS_WeaponIndex] + 1] = 2;
      ++$character[$combatChainState[$CCS_WeaponIndex] + 5];
      return 1;
    case "imperial_seal_of_command_red-HIT":
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
    case "ira_scarlet_revenger":
      return 1;
    default:
      return 0;
  }
}

function EffectHasBlockModifier($cardID)
{
  switch ($cardID) {
    case "phantasmal_footsteps":
    case "korshem_crossroad_of_elements-2":
    case "amulet_of_earth_blue":
    case "rampart_of_the_rams_head":
    case "spiders_bite":
    case "spiders_bite":
    case "nerve_scalpel":
    case "nerve_scalpel":
    case "orbitoclast":
    case "orbitoclast":
    case "scale_peeler":
    case "scale_peeler":
    case "fletch_a_red_tail_red":
    case "fletch_a_yellow_tail_yellow":
    case "fletch_a_blue_tail_blue":
    case "defender_of_daybreak_red":
    case "defender_of_daybreak_yellow":
    case "defender_of_daybreak_blue":
    case "lay_down_the_law_red":
    case "lay_down_the_law_red":
    case "stonewall_impasse":
    case "headliner_helm":
    case "stadium_centerpiece":
    case "ticket_puncher":
    case "grandstand_legplates":
    case "bloodied_oval":
    case "graven_call":
      return true;
    default:
      return false;
  }
}

function EffectBlockModifier($cardID, $index, $from)
{
  global $CombatChain, $defPlayer, $mainPlayer;
  switch ($cardID) {
    case "phantasmal_footsteps":
      if ($CombatChain->Card($index)->ID() == $cardID) return 1;
      return 0;
    case "korshem_crossroad_of_elements-2":
      return 1;
    case "amulet_of_earth_blue":
      return 1;
    case "rampart_of_the_rams_head":
      return ($CombatChain->Card($index)->ID() == $cardID ? 1 : 0);
    case "fletch_a_red_tail_red":
      return (PitchValue($CombatChain->Card($index)->ID()) == 1 && HasAimCounter() ? -1 : 0);
    case "fletch_a_yellow_tail_yellow":
      return (PitchValue($CombatChain->Card($index)->ID()) == 2 && HasAimCounter() ? -1 : 0);
    case "fletch_a_blue_tail_blue":
      return (PitchValue($CombatChain->Card($index)->ID()) == 3 && HasAimCounter() ? -1 : 0);
    case "defender_of_daybreak_red":
    case "defender_of_daybreak_yellow":
    case "defender_of_daybreak_blue":
      return (CardType($CombatChain->Card($index)->ID()) != "E" && TalentContains($CombatChain->Card($index)->ID(), "LIGHT", $defPlayer) && TalentContains($CombatChain->AttackCard()->ID(), "SHADOW", $mainPlayer) ? 1 : 0);
    case "lay_down_the_law_red":
    case "lay_down_the_law_red":
      return (CachedTotalAttack() >= 13 && !TypeContains($CombatChain->Card($index)->ID(), "E") && !DelimStringContains(CardSubType($CombatChain->Card($index)->ID()), "Evo")) ? -1 : 0;
    case "ratchet_up_red":
    case "ratchet_up_yellow":
    case "ratchet_up_blue":
      return IsActionCard($CombatChain->Card($index)->ID()) ? -1 : 0;
    case "headliner_helm":
    case "stadium_centerpiece":
    case "ticket_puncher":
    case "grandstand_legplates":
    case "bloodied_oval":
      return $CombatChain->Card($index)->ID() == $cardID && PlayerHasLessHealth($defPlayer) ? 1 : 0;
    case "wide_blue_yonder_blue":
      return SearchPitchForColor($mainPlayer, 3);
    case "heavy_industry_surveillance":
    case "heavy_industry_ram_stop":
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
      case "mask_of_momentum":
      case "high_speed_impact_red":
      case "high_speed_impact_yellow":
      case "high_speed_impact_blue": //High Speed Impact
      case "combustible_courier_red":
      case "combustible_courier_yellow":
      case "combustible_courier_blue": // Combustible Courier
      case "v_of_the_vanguard_yellow": //V of the Vanguard
      case "exude_confidence_red": //Exude Confidence
      case "explosive_growth_red":
      case "explosive_growth_yellow":
      case "explosive_growth_blue": //Explosive Growth
      case "ball_lightning_red":
      case "ball_lightning_yellow":
      case "ball_lightning_blue": //Ball Lightning
      case "spreading_flames_red": //Spreading Flames
      case "brand_with_cinderclaw_red":
      case "brand_with_cinderclaw_yellow":
      case "brand_with_cinderclaw_blue": //Brand with Cinderclaw
      case "scramble_pulse_red":
      case "scramble_pulse_yellow":
      case "scramble_pulse_blue": //Scramble Pulse
      case "prowl_red":
      case "prowl_yellow":
      case "prowl_blue": //Prowl
      case "head_leads_the_tail_red": //Head Leads the Tail
      case "deadly_duo_red":
      case "deadly_duo_yellow":
      case "deadly_duo_blue": //Deadly Duo
      case "spirit_of_war_red"://Spirit of War
      case "growl_red":
      case "growl_yellow"://Growl
      case "coercive_tendency_blue"://Coercive Tendency
      case "tiger_taming_khakkara": //Tiger Taming
      case "chase_the_tail_red"://Chase the Tail
      case "untamed_red":
      case "untamed_yellow":
      case "untamed_blue": //Untamed
      case "stonewall_gauntlet": //Stonewall Gauntlet
      case "water_the_seeds_red":
      case "water_the_seeds_yellow":
      case "water_the_seeds_blue": //Water the Seeds
      case "wrath_of_retribution_red":
      case "fire_tenet_strike_first_red":
      case "poisoned_blade_red":
      case "poisoned_blade_yellow":
      case "poisoned_blade_blue":
      case "quickdodge_flexors":
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
        case "bramble_spark_red":
        case "bramble_spark_yellow":
        case "bramble_spark_blue":
          if ($attackType == "AA") {
            AddLayer("TRIGGER", $mainPlayer, $currentTurnEffects[$i], $attack);
            $remove = true;
          }
          break;
        case "flashfreeze_red-DOM":
          AddDecisionQueue("SETDQCONTEXT", $defPlayer, "Do you want to pay 2 to prevent this attack from getting dominate?", 1);
          AddDecisionQueue("BUTTONINPUT", $defPlayer, "0,2", 0, 1);
          AddDecisionQueue("PAYRESOURCES", $defPlayer, "<-", 1);
          AddDecisionQueue("GREATERTHANPASS", $defPlayer, "0", 1);
          AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, $currentTurnEffects[$i] . "ATK!PLAY", 1);
          break;
        case "warband_of_bellona":
          Charge(may: true, player: $mainPlayer);
          AddDecisionQueue("ALLCARDPITCHORPASS", $mainPlayer, "2", 1);
          AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
          $remove = true;
          break;
        case "good_time_chapeau-PAID":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(ExtractCardID($currentTurnEffects[$i]));
          }
          break;
        case "big_bop_red-BUFF":
        case "big_bop_yellow-BUFF":
        case "big_bop_blue-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(ExtractCardID($currentTurnEffects[$i]));
          }
          break;
        case "bigger_than_big_red-BUFF":
        case "bigger_than_big_yellow-BUFF":
        case "bigger_than_big_blue-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(ExtractCardID($currentTurnEffects[$i]));
          }
          break;
        case "edge_ahead_red-BUFF":
        case "edge_ahead_yellow-BUFF":
        case "edge_ahead_blue-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(ExtractCardID($currentTurnEffects[$i]));
          }
          break;
        case "hold_em_red-BUFF":
        case "hold_em_yellow-BUFF":
        case "hold_em_blue-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(ExtractCardID($currentTurnEffects[$i]));
          }
          break;
        case "money_where_ya_mouth_is_red-BUFF":
        case "money_where_ya_mouth_is_yellow-BUFF":
        case "money_where_ya_mouth_is_blue-BUFF":
          if (IsCombatEffectActive($currentTurnEffects[$i]) && IsHeroAttackTarget()) {
            AskWager(ExtractCardID($currentTurnEffects[$i]));
          }
          break;
        case "shifting_winds_of_the_mystic_beast_blue":
          if (CardNameContains($attack, "Crouching Tiger", $mainPlayer)) {
            AddDecisionQueue("INPUTCARDNAME", $mainPlayer, "-");
            AddDecisionQueue("SETDQVAR", $mainPlayer, "0");
            AddDecisionQueue("PREPENDLASTRESULT", $mainPlayer, "crouching_tiger-");
            AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, "<-");
            AddDecisionQueue("WRITELOG", $mainPlayer, "📣<b>{0}</b> was chosen");
          }
          break;
        case "first_tenet_of_chi_moon_blue":
          if (PitchValue($attack) == 3) {
            Draw($mainPlayer);
            $remove = true;
          }
        case "unsheathed_red":
          if (IsCombatEffectActive($currentTurnEffects[$i])){
            CacheCombatResult();
            if (IsWeaponGreaterThanTwiceBasePower()) GiveAttackGoAgain(); // borrowing ideas from merciless battleaxe (merciless_battleaxe) and shift the tide of battle (shift_the_tide_of_battle_yellow)
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
        case "transmogrify_red":
          if ($mod < 8) $mod = 8;
          break;
        case "transmogrify_yellow":
          if ($mod < 7) $mod = 7;
          break;
        case "transmogrify_blue":
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
      if (DelimStringContains($currentTurnEffects[$i], "art_of_the_dragon_blood_red", true)) {
        $cardType = CardType($cardID);
        if(TalentContains($cardID, "DRACONIC", $currentPlayer) && !IsStaticType($cardType, $from, $cardID)) {
          $costModifier -= 1;
          --$currentTurnEffects[$i + 3];
          if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
        }
      }
      switch ($currentTurnEffects[$i]) {
        case "cartilage_crush_red":
        case "cartilage_crush_yellow":
        case "cartilage_crush_blue":
          if (IsAction($cardID, $from)) {
            $costModifier += 1;
            $remove = true;
          }
          break;
        case "seismic_surge":
          if (ClassContains($cardID, "GUARDIAN", $currentPlayer) && CardType($cardID) == "AA") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "heartened_cross_strap":
          if (CardType($cardID) == "AA" && (GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "")) {
            $costModifier -= 2;
            $remove = true;
          }
          break;
        case "courage_of_bladehold":
          if (TypeContains($cardID, "W", $currentPlayer) && CardSubType($cardID) == "Sword") {
            $costModifier -= 1;
          }
          break;
        case "dauntless_red-2":
        case "dauntless_yellow-2":
        case "dauntless_blue-2":
          if (CardType($cardID) == "DR") {
            $costModifier += 1;
            $remove = true;
          }
          break;
        case "bloodsheath_skeleta-AA":
          if (CardType($cardID) == "AA") {
            $costModifier -= CountAura("runechant", $currentPlayer);
            $remove = true;
          }
          break;
        case "bloodsheath_skeleta-NAA":
          if (CardType($cardID) == "A") {
            $costModifier -= CountAura("runechant", $currentPlayer);
            $remove = true;
          }
          break;
        case "hamstring_shot_red":
        case "hamstring_shot_yellow":
        case "hamstring_shot_blue":
          if ((CardType($cardID) == "AA" || GetResolvedAbilityType($cardID, $from) == "AA") && (GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "")) {
            $costModifier += 1;
            $remove = true;
          }
          break;
        case "frost_lock_blue-1":
          $costModifier += 1;
          break;
        case "cold_wave_red":
        case "cold_wave_yellow":
        case "cold_wave_blue":
          $costModifier += 1;
          break;
        case "heart_of_ice":
          $costModifier += 1;
          break;
        case "amulet_of_ignition_yellow":
          if (IsStaticType(CardType($cardID), $from, $cardID)) {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "blood_of_the_dracai_red":
          if (TalentContains($cardID, "DRACONIC", $currentPlayer) && $from != "PLAY" && $from != "EQUIP") {
            $costModifier -= 1;
            --$currentTurnEffects[$i + 3];
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
          }
          break;
        case "rising_resentment_red":
        case "rising_resentment_yellow":
        case "rising_resentment_blue":
          if (GetClassState($currentPlayer, $CS_PlayUniqueID) == $currentTurnEffects[$i + 2]) {
            --$costModifier;
            $remove = true;
          }
          break;
        case "alluvion_constellas":
          if (IsStaticType(CardType($cardID), $from, $cardID) && DelimStringContains(CardSubType($cardID), "Staff")) {
            $costModifier -= 3;
            $remove = true;
          }
          break;
        case "redback_shroud":
          if (CardType($cardID) == "AR") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "silken_gi-1":
          if (CardType($cardID) == "AA") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "empyrean_rapture":
          if (CardType($cardID) == "C") {
            $costModifier -= 2;
            $remove = true;
          }
          break;
        case "bequest_the_vast_beyond_red":
          if (CardType($cardID) == "AA" && ClassContains($cardID, "RUNEBLADE", $currentPlayer)) {
            $costModifier -= CountAura("runechant", $currentPlayer);
            $remove = true;
          }
          break;
        case "earthlore_empowerment_red":
        case "earthlore_empowerment_yellow":
          if (ClassContains($cardID, "GUARDIAN", $currentPlayer) && CardType($cardID) == "AA") $costModifier -= 1;
          break;
        case "evo_engine_room_yellow_equip":
          if (TypeContains($cardID, "W", $currentPlayer)) {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "savage_sash":
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
        case "evo_heartdrive_blue":
          if (CardType($cardID) == "AA") {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "ignite_red":
          if (TalentContains($cardID, "DRACONIC", $currentPlayer)) {
            $costModifier -= 1;
            $remove = true;
            if ($cardID == "fealty") {
              WriteLog("Ignite is discounting your fealty, this is not a bug. You need to use the fealty in response to your ignite");
            }
          }
          break;
        case "wrath_of_retribution_red":
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
        case "solar_plexus":
          if (PitchValue($cardID) == 2) $costModifier -= 1;
          break;
        case "calming_cloak":
          if (SubtypeContains($cardID, "Aura") && $from != "PLAY") {
            $costModifier -= 2;
            $remove = true;
          }
          break;
        case "heart_of_vengeance":
          $otherChar = &GetPlayerCharacter(player: $otherPlayer);
          $isAttack = GetResolvedAbilityType($cardID, $from) == "AA" || (GetResolvedAbilityType($cardID, $from) == "" && CardType($cardID) == "AA");
          if (CardNameContains($otherChar[0], "Arakni") && $isAttack) {
            $costModifier -= 1;
            $remove = true;
          }
          break;
        case "perforate_yellow":
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
        case "essence_of_ancestry_body_red":
          if (PitchValue($source) == 1) {
            $preventedDamage += $damage;
            RemoveCurrentTurnEffect($i);
          }
          break;
        case "essence_of_ancestry_soul_yellow":
          if (PitchValue($source) == 2) {
            $preventedDamage += $damage;
            RemoveCurrentTurnEffect($i);
          }
          break;
        case "essence_of_ancestry_mind_blue":
          if (PitchValue($source) == 3) {
            $preventedDamage += $damage;
            RemoveCurrentTurnEffect($i);
          }
          break;
        case "shelter_from_the_storm_red":
        case "calming_breeze_red":
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
  if ($preventedDamage > 0 && SearchCurrentTurnEffects("vambrace_of_determination", $player) != "") {
    $preventedDamage -= 1;
    SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
  }
  $damage -= $preventedDamage;
  return $damage;
}

function CurrentEffectDamagePrevention($player, $type, $damage, $source, $preventable)
{
  global $currentTurnEffects;
  $otherPlayer = ($player == 1 ? 2 : 1);
  $vambraceAvailable = SearchCurrentTurnEffects("vambrace_of_determination", $player) != "";
  $vambraceRemove = false;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0 && $damage > 0; $i -= CurrentTurnEffectPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $player) {
      $preventedDamage = 0;
      $effects = explode("-", $currentTurnEffects[$i]);
      switch ($effects[0]) {
        case "dissipation_shield_yellow":
          if ($preventable) $preventedDamage += intval($effects[1]);
          $remove = true;
          break;
        case "blessing_of_serenity_red":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 3;
            $remove = true;
          }
          break;
        case "blessing_of_serenity_yellow":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 2;
            $remove = true;
          }
          break;
        case "blessing_of_serenity_blue":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 1;
            $remove = true;
          }
          break;
        case "steadfast_red":
        case "steadfast_yellow":
        case "steadfast_blue":
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
        case "amulet_of_intervention_blue":
          $remove = true;
          break;
        case "helios_mitre":
          if ($source == $currentTurnEffects[$i + 2]) {
            if ($preventable) {
              $sourceDamage = $damage;
              $preventedDamage += $currentTurnEffects[$i + 3];
              $currentTurnEffects[$i + 3] -= $sourceDamage;
            }
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
            if ($source == "runechant" || $source == "aether_ashwing") $remove = true; //To be removed when coded with Unique ID instead of cardID name as $source
          }
          break;
        case "oasis_respite_red":
        case "oasis_respite_yellow":
        case "oasis_respite_blue":
          if ($source == $currentTurnEffects[$i + 2]) {
            if ($preventable) {
              $sourceDamage = $damage;
              $preventedDamage += $currentTurnEffects[$i + 3];
              $currentTurnEffects[$i + 3] -= $sourceDamage;
            }
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
            if (TypeContains($source, "AA") || $source == "runechant" || $source == "aether_ashwing") $remove = true; //To be removed when coded with Unique ID instead of cardID name as $source
          }
          break;
        case "seekers_hood":
        case "seekers_gilet":
        case "seekers_mitts":
        case "seekers_leggings":
          if ($preventable) {
            $preventedDamage += 1;
          }
          $remove = true;
          break;
        case "brush_off_red":
          if ($preventable && $damage <= 3) {
            $preventedDamage = $damage;
            $remove = true;
          }
          break;
        case "brush_off_yellow":
          if ($preventable && $damage <= 2) {
            $preventedDamage = $damage;
            $remove = true;
          }
          break;
        case "brush_off_blue":
          if ($preventable && $damage == 1) {
            $preventedDamage = $damage;
            $remove = true;
          }
          break;
        case "peace_of_mind_red":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 4;
            $remove = true;
          }
          break;
        case "peace_of_mind_yellow":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 3;
            $remove = true;
          }
          break;
        case "peace_of_mind_blue":
          if ($type == "COMBAT") {
            if ($preventable) $preventedDamage += 2;
            $remove = true;
          }
          break;
        case "break_of_dawn_red":
        case "break_of_dawn_yellow":
        case "break_of_dawn_blue":
          if ($effects[0] == "break_of_dawn_red") $prevention = 4;
          else if ($effects[0] == "break_of_dawn_yellow") $prevention = 3;
          else if ($effects[0] == "break_of_dawn_blue") $prevention = 2;
          if (TalentContains($source, "SHADOW", $otherPlayer)) {
            if ($preventable) $preventedDamage += $prevention;
            $remove = true;
          }
          break;
        case "interlude_red":
          if ($preventable) $preventedDamage += 3;
          $remove = true;
          break;
        case "interlude_yellow":
          if ($preventable) $preventedDamage += 2;
          $remove = true;
          break;
        case "interlude_blue":
          if ($preventable) $preventedDamage += 1;
          $remove = true;
          break;
        case "dissolving_shield_red":
        case "dissolving_shield_yellow":
        case "dissolving_shield_blue":
          $remove = true;
          break;
        case "evo_circuit_breaker_red":
        case "evo_atom_breaker_red":
        case "evo_face_breaker_red":
        case "evo_mach_breaker_red": //Card
        case "evo_circuit_breaker_red_equip":
        case "evo_atom_breaker_red_equip":
        case "evo_face_breaker_red_equip":
        case "evo_mach_breaker_red_equip": //Equipment
          if ($preventable) $preventedDamage += intval($effects[1]);
          $remove = true;
          break;
        case "no_fear_red":
          if ($preventable) $preventedDamage += 2 + intval($effects[1]);
          $remove = true;
          break;
        case "battered_not_broken_red":
          if ($preventable) {
            $preventedDamage += 2;
            PlayAura("might", $player); //Might
          }
          $remove = true;
          break;
        case "take_it_on_the_chin_red":
          if ($preventable) {
            $preventedDamage += 2;
            PlayAura("agility", $player); //Agility
          }
          $remove = true;
          break;
        case "slap_happy_red":
          if ($preventable) {
            $preventedDamage += 2;
            PlayAura("vigor", $player); //Vigor
          }
          $remove = true;
          break;
        case "sheltered_cove":
          if ($preventable) $preventedDamage += 2;
          $remove = true;
          break;
        case "battlefront_bastion_blue":
        case "battlefront_bastion_red":
        case "battlefront_bastion_yellow":
        case "battlefront_bastion_blue":
          $remove = true;
          break;
        case "moon_chakra_red":
          if ($preventable) {
            if ($currentTurnEffects[$i] == "moon_chakra_red-1") $preventedDamage += 3;
            else $preventedDamage += 5;
          }
          $remove = true;
          break;
        case "moon_chakra_yellow":
          if ($preventable) {
            if ($currentTurnEffects[$i] == "moon_chakra_yellow-1") $preventedDamage += 2;
            else $preventedDamage += 4;
          }
          $remove = true;
          break;
        case "moon_chakra_blue":
          if ($preventable) {
            if ($currentTurnEffects[$i] == "moon_chakra_blue-1") $preventedDamage += 1;
            else $preventedDamage += 3;
          }
          $remove = true;
          break;
        case "cloud_cover_yellow":
        case "sigil_of_shelter_yellow":
          if ($preventable) {
            $preventedDamage += 2;
          }
          $remove = true;
          break;
        case "sigil_of_shelter_blue":
          if ($preventable) {
            $preventedDamage += 1;
          }
          $remove = true;
          break;
        case "sanctuary_of_aria":
          if ($source == $currentTurnEffects[$i + 2]) {
            if ($preventable) {
              $sourceDamage = $damage;
              $preventedDamage += $currentTurnEffects[$i + 3];
              $currentTurnEffects[$i + 3] -= $sourceDamage;
            }
            if ($currentTurnEffects[$i + 3] <= 0) $remove = true;
          }
          break;
        case "misfire_dampener":
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
  if ($vambraceRemove) SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
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
        case "oath_of_steel_red":
          if ($attackType == "W") {
            $character = &GetPlayerCharacter($mainPlayer);
            ++$character[GetClassState($mainPlayer, $CS_PlayIndex) + 3];
          }
          break;
        case "seeds_of_agony_red":
        case "seeds_of_agony_yellow":
        case "seeds_of_agony_blue":
          if ($currentTurnEffects[$i] == "seeds_of_agony_red") $maxCost = 2;
          else if ($currentTurnEffects[$i] == "seeds_of_agony_yellow") $maxCost = 1;
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
        case "lead_the_charge_red":
          $cardType = CardType($cardID);
          if ((DelimStringContains($cardType, "A") || $cardType == "AA") && $cost >= 0) {
            ++$actionPoints;
            $remove = true;
          }
          break;
        case "lead_the_charge_yellow":
          $cardType = CardType($cardID);
          if ((DelimStringContains($cardType, "A") || $cardType == "AA") && $cost >= 1) {
            ++$actionPoints;
            $remove = true;
          }
          break;
        case "lead_the_charge_blue":
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
        case "chane_bound_by_shadow":
        case "chane":
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
        case "gauntlets_of_iron_will":
          if ($cache) CacheCombatResult();
          if ($effectArr[1] != "ACTIVE" && CachedTotalAttack() > intval($effectArr[1])) $currentTurnEffects[$i] = "gauntlets_of_iron_will,ACTIVE";
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
        case "lightning_greaves": 
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
        case "bloodrush_bellow_yellow-GOAGAIN":
          $hasGoAgain = true;
          $remove = true;
          break;
        case "chane_bound_by_shadow":
        case "chane":
          if (ClassContains($cardID, "RUNEBLADE", $currentPlayer) || TalentContains($cardID, "SHADOW", $currentPlayer)) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "flash_red":
          if (CardCost($cardID) >= 0) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "flash_yellow":
          if (CardCost($cardID) >= 1) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "flash_blue":
          if (CardCost($cardID) >= 2) {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "amulet_of_lightning_blue":
          $hasGoAgain = true;
          $remove = true;
          break;
        case "moon_wish_red-GA":
          $hasGoAgain = ($cardID == "sun_kiss_red" || $cardID == "sun_kiss_yellow" || $cardID == "sun_kiss_blue");
          break;
        case "tear_through_the_portal_red":
        case "tear_through_the_portal_yellow":
        case "tear_through_the_portal_blue":
          if (SearchCurrentTurnEffects($turnEffects[0] . "," . $cardID, $currentPlayer) && $from == "BANISH") {
            $hasGoAgain = true;
            $remove = true;
          }
          break;
        case "first_tenet_of_chi_wind_blue":
          if (ColorContains($cardID, 3, $currentPlayer)) {
            $hasGoAgain = true;
            if ($cardID != $turnEffects[0]) $remove = true;
          }
          break;
        case "arc_lightning_yellow-GOAGAIN":
          if(SearchCurrentTurnEffects("arc_lightning_yellow", $currentPlayer) && !IsMeldInstantName(GetClassState($currentPlayer, $CS_AdditionalCosts)) && (GetClassState($currentPlayer, $CS_AdditionalCosts) != "Both" || $from == "MELD")) {
            $hasGoAgain = true;
            if ($cardID != "arc_lightning_yellow") $remove = true;
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
        case "driving_blade_red":
        case "driving_blade_yellow":
        case "driving_blade_blue":
          return true;
        case "snapdragon_scalers":
          return true;
        case "rapid_fire_yellow":
          return true;
        case "art_of_war_yellow-3":
          return true;
        case "breeze_rider_boots":
          return true;
        case "flood_of_force_yellow":
          return true;
        case "spoils_of_war_red":
          return true;
        case "hit_and_run_red-1":
        case "hit_and_run_yellow-1":
        case "hit_and_run_blue-1":
          return true;
        case "perch_grapplers":
          return true;
        case "mauvrion_skies_red":
        case "mauvrion_skies_yellow":
        case "mauvrion_skies_blue":
          return true;
        case "dread_screamer_red":
        case "dread_screamer_yellow":
        case "dread_screamer_blue":
          return true;
        case "seeping_shadows_red":
        case "seeping_shadows_yellow":
        case "seeping_shadows_blue":
          return true;
        case "shadow_puppetry_red":
          return true;
        case "rouse_the_ancients_blue":
          return true;
        case "captains_call_red-2":
        case "captains_call_yellow-2":
        case "captains_call_blue-2":
          return true;
        case "lexi_livewire-1":
          return true;
        case "voltaire_strike_twice-2":
          return true;
        case "fulminate_yellow-GA":
          return true;
        case "flash_red":
        case "flash_yellow":
        case "flash_blue":
          return true;
        case "weave_lightning_red":
        case "weave_lightning_yellow":
        case "weave_lightning_blue":
          if ($combatChainState[$CCS_AttackFused] == 1) return true;
          else break;
        case "amulet_of_lightning_blue":
          return true;
        case "bravo_star_of_the_show":
          return true;
        case "ride_the_tailwind_red":
        case "ride_the_tailwind_yellow":
        case "ride_the_tailwind_blue":
          return true;
        case "life_of_the_party_red-3":
        case "life_of_the_party_yellow-3":
        case "life_of_the_party_blue-3":
          return true;
        case "glistening_steelblade_yellow":
          return true;
        case "on_a_knife_edge_yellow":
          return true;
        case "soaring_strike_red":
        case "soaring_strike_yellow":
        case "soaring_strike_blue":
          return true;
        case "burn_away_red":
          return true;
        case "precision_press_red":
        case "precision_press_yellow":
        case "precision_press_blue":
          return true;
        case "tear_through_the_portal_red":
        case "tear_through_the_portal_yellow":
        case "tear_through_the_portal_blue":
          return true;
        case "agility":
          return true;
        case "luminaris_angels_glow-1":
        case "luminaris_angels_glow-2":
          if (SearchPitchForColor($mainPlayer, 2) > 0) return true;
          else break;
        case "coercive_tendency_blue":
          return true;
        case "beckoning_mistblade":
          return true;
        case "slither":
          return true;
        case "first_tenet_of_chi_wind_blue":
          return true;
        case "shadowrealm_horror_red-2":
          return true;
        case "ROGUE710-GA":
          return true;
        case "flight_path":
          return true;
        case "arc_lightning_yellow-GOAGAIN":
          return true;
        case "machinations_of_dominion_blue":
          if(GetClassState($mainPlayer, $CS_NumAuras) >= 1) return true;
          else break;
        case "agility_stance_yellow":
          return true;
        case "knife_through_butter_red-GOAGAIN":
        case "knife_through_butter_yellow-GOAGAIN":
        case "knife_through_butter_blue-GOAGAIN":
          return IsHeroAttackTarget() && CheckMarked($defPlayer);
        case "dragonscaler_flight_path":
        case "path_of_vengeance":
          return true;
        case "trot_along_blue":
          return true;
        case "the_hand_that_pulls_the_strings":
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
        case "spinal_crush_red":
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
        case "remorseless_red":
          if ($from == "ARS" && IsCombatEffectActive($currentTurnEffects[$i])) $reactionPrevented = true;
          break;
        case "increase_the_tension_red-1":
        case "increase_the_tension_yellow-1":
        case "increase_the_tension_blue-1":
          if ($from == "HAND" && IsCombatEffectActive($currentTurnEffects[$i])) $reactionPrevented = true;
          break;
        case "release_the_tension_red-1":
        case "release_the_tension_yellow-1":
        case "release_the_tension_blue-1":
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
        case "cranial_crush_blue":
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
      $cardID = ExtractCardID($currentTurnEffects[$i]);
      switch ($cardID) {
        case "helm_of_isens_peak":
        case "pursuit_of_knowledge_blue":
        case "stamp_authority_blue":
        case "great_library_of_solana":
        case "nourishing_emptiness_red":
        case "evo_steel_soul_memory_blue":
        case "evo_steel_soul_memory_blue_equip":
          if($remove){// Handle transformations (Blasmophet, Dishonor, etc) restarting Intellect
            RemoveCurrentTurnEffect($i);
            break;
          }
          $intellectModifier += 1;
          break;
        case "knucklehead":
          if($remove){// Handle transformations (Blasmophet, Dishonor, etc) restarting Intellect
            RemoveCurrentTurnEffect($i);
            break;
          }
          $characters = GetPlayerCharacter($mainPlayer);
          $intellectModifier -= CharacterIntellect($characters[0]) - substr($currentTurnEffects[$i], -1);
          break;
        case "ten_foot_tall_and_bulletproof_red":
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
    $cardID = ExtractCardID($currentTurnEffects[$i]);
    if (SearchCurrentTurnEffects($cardID . "-UNDER", $currentTurnEffects[$i + 1])) {
      AddNextTurnEffect($currentTurnEffects[$i], $currentTurnEffects[$i + 1]);
    }
    switch ($cardID) {
      case "glisten_red":
      case "glisten_yellow":
      case "glisten_blue":
      case "oath_of_steel_red":
        if ($mainPlayer == $currentTurnEffects[$i + 1]) {
          $char = &GetPlayerCharacter($currentTurnEffects[$i + 1]);
          for ($j = 0; $j < count($char); $j += CharacterPieces()) {
            if (TypeContains($char[$j], "W", $mainPlayer)) $char[$j + 3] = 0;
          }
          $remove = true;
        }
        break;
      case "adaptive_plating": case "adaptive_dissolver": case "frostbite":
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
    case "banneret_of_salvation_yellow":
      return DTDCombatEffectActive($cardID, $cardToCheck);
    case "banneret_of_vigor_yellow":
      return DTDCombatEffectActive($cardID, $cardToCheck);
    case "ira_scarlet_revenger":
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
  if (DelimStringContains($cardID, "art_of_the_dragon_blood_red", true)) return true;
  switch ($cardID) {
    case "bloodrush_bellow_yellow":
    case "bravo_showstopper":
    case "bravo":
      return true;
    case "rapid_fire_yellow":
      return true;
    case "art_of_war_yellow-1":
      return true;
    case "plunder_run_red-1":
    case "plunder_run_yellow-1":
    case "plunder_run_blue-1":
      return true;
    case "crater_fist":
    case "breeze_rider_boots":
    case "spoils_of_war_red-2":
    case "plasma_purifier_red":
    case "perch_grapplers":
    case "poison_the_tips_yellow":
    case "cash_in_yellow":
      return true;
    case "lumina_ascension_yellow":
    case "v_of_the_vanguard_yellow":
    case "ray_of_hope_yellow":
    case "phantasmal_footsteps":
    case "gallantry_gold":
    case "spill_blood_red":
    case "eclipse_existence_blue":
    case "stubby_hammerers":
    case "exude_confidence_red":
      return true;
    case "blizzard_bolt_red":
    case "blizzard_bolt_yellow":
    case "blizzard_bolt_blue":
      return true;
    case "buzz_bolt_red":
    case "buzz_bolt_yellow":
    case "buzz_bolt_blue":
      return true;
    case "chilling_icevein_red":
    case "chilling_icevein_yellow":
    case "chilling_icevein_blue":
      return true;
    case "frazzle_red":
    case "frazzle_yellow":
    case "frazzle_blue":
      return true;
    case "force_of_nature_blue-HIT":
    case "explosive_growth_red":
    case "explosive_growth_yellow":
    case "explosive_growth_blue":
    case "fulminate_yellow-BUFF":
    case "fulminate_yellow-GA":
      return true;
    case "flashfreeze_red-DOM":
    case "flashfreeze_red-BUFF":
    case "amulet_of_earth_blue":
    case "ice_quake_red-HIT":
    case "ice_quake_yellow-HIT":
    case "ice_quake_blue-HIT":
      return true;
    case "shock_charmers":
    case "electrify_red":
    case "electrify_yellow":
    case "electrify_blue":
    case "rampart_of_the_rams_head":
      return true;
    case "skull_crushers":
    case "valda_brightaxe":
    case "outland_skirmish_red-1":
    case "outland_skirmish_yellow-1":
    case "outland_skirmish_blue-1":
    case "rain_razors_yellow":
      return true;
    case "this_rounds_on_me_blue":
    case "high_striker_red":
    case "high_striker_yellow":
    case "high_striker_blue":
    case "smashing_good_time_red-1":
    case "smashing_good_time_yellow-1":
    case "smashing_good_time_blue-1":
    case "potion_of_ironhide_blue":
      return true;
    case "glistening_steelblade_yellow-1":
      return true;
    case "skittering_sands_red":
    case "skittering_sands_yellow":
    case "skittering_sands_blue":
    case "heat_wave":
    case "spreading_flames_red":
      return true;
    case "berserk_yellow":
    case "roar_of_the_tiger_yellow":
    case "visit_the_imperial_forge_red":
    case "visit_the_imperial_forge_yellow":
    case "visit_the_imperial_forge_blue":
    case "galvanic_bender-UNDER":
    case "immobilizing_shot_red":
      return true;
    case "head_leads_the_tail_red":
    case "mask_of_shifting_perspectives":
    case "blade_cuff":
    case "knives_out_blue":
    case "premeditate_red-1":
      return true;
    case "figment_of_triumph_yellow":
      return true;
    case "beckoning_light_red":
      return true;//Beckoning Light
    case "spirit_of_war_red":
      return true;//Spirit of War
    case "blood_dripping_frenzy_blue":
      return true;
    case "call_down_the_lightning_yellow":
      return true;//Call Down the Lightning
    case "chorus_of_ironsong_yellow":
      return true;
    case "hack_to_reality_yellow-HIT":
      return true;
    case "metis_archangel_of_tenacity":
      return true;
    case "victoria_archangel_of_triumph":
      return true;
    case "evo_steel_soul_memory_blue_equip":
      return true;
    case $Card_LifeBanner:
      return true;
    case $Card_ResourceBanner:
      return true;
    case "stonewall_impasse":
    case "kassai_of_the_golden_sand":
    case "kassai":
      return true;
    case "commanding_performance_red":
      return true;
    case "talk_a_big_game_blue":
      return true;
    case "fabricate_red":
      return true;
    case "double_down_red":
      return true;
    case "coercive_tendency_blue":
      return true;
    case "ancestral_harmony_blue":
      return true;
    case "sacred_art_jade_tiger_domain_blue":
      return true;
    case "dense_blue_mist_blue-DEBUFF":
      return true;
    case "dense_blue_mist_blue-HITPREVENTION":
      return true;
    case "stonewall_gauntlet":
      return true;
    case "target_totalizer":
      return true;
    case "thrive_yellow":
      return true;
    case "burn_up__shock_red":
    case "succumb_to_temptation_yellow":
      return true;
    case "gauntlets_of_the_boreal_domain-E":
    case "gauntlets_of_the_boreal_domain-I":
      return true;
    case "wrath_of_retribution_red":
    case "fire_and_brimstone_red":
    case "agility_stance_yellow":
    case "power_stance_blue":
    case "knife_through_butter_red-GOAGAIN":
    case "knife_through_butter_yellow-GOAGAIN":
    case "knife_through_butter_blue-GOAGAIN":
    case "point_of_engagement_red-MARKEDBUFF":
    case "point_of_engagement_yellow-MARKEDBUFF":
    case "point_of_engagement_blue-MARKEDBUFF":
    case "rake_over_the_coals_red":
    case "poisoned_blade_red":
    case "poisoned_blade_yellow":
    case "poisoned_blade_blue":
    case "savor_bloodshed_red-HIT":
    case "quickdodge_flexors":
    case "imperial_seal_of_command_red":
    case "imperial_seal_of_command_red-HIT":
    case "war_cry_of_bellona_yellow-BUFF":
    case "war_cry_of_bellona_yellow-DMG":
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
      case "revel_in_runeblood_red":
        if (CountAura("runechant", $mainPlayer) > 0) {
          WriteLog(CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]) . " destroyed your Runechant tokens");
          DestroyAllThisAura($currentTurnEffects[$i + 1], "runechant");
        }
        break;
      case "helios_mitre-1":
        $attackCharIndex = FindCharacterIndex($mainPlayer, "helios_mitre");
        $defendCharIndex = FindCharacterIndex($defPlayer, "helios_mitre");
        if ($attackCharIndex > -1) {
          DestroyCharacter($mainPlayer, $attackCharIndex);
        } elseif ($defendCharIndex > -1) {
          DestroyCharacter($defPlayer, $defendCharIndex);
        }
      case "strategic_planning_red":
      case "strategic_planning_yellow":
      case "strategic_planning_blue":
        Draw($currentTurnEffects[$i + 1], false);
        break;
      case "hidden_agenda":
        $attackCharIndex = FindCharacterIndex($mainPlayer, "hidden_agenda");
        $defendCharIndex = FindCharacterIndex($defPlayer, "hidden_agenda");
        if ($attackCharIndex > -1) {
          DestroyCharacter($mainPlayer, $attackCharIndex);
        } elseif ($defendCharIndex > -1) {
          DestroyCharacter($defPlayer, $defendCharIndex);
        }
        break;
      case "sanctuary_of_aria-1":
        $player = $currentTurnEffects[$i+1];
        $sanctuaryIndex = GetItemIndex("sanctuary_of_aria", $player);
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
      case "seek_and_destroy_red-1": 
        AddLayer("TRIGGER", $defPlayer, "seek_and_destroy_red", $currentTurnEffects[$i + 1], "-", "-");
        break;
      case "heat_seeker_red":
        AddLayer("TRIGGER", $mainPlayer, "heat_seeker_red", $currentTurnEffects[$i + 1], "-", "-");
        break;
      case "plan_for_the_worst_blue-1":
        AddLayer("TRIGGER", $defPlayer, "plan_for_the_worst_blue", $currentTurnEffects[$i + 1], "-", "-");
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
        case "endless_winter_red-HIT":
          WriteLog(CardLink("endless_winter_red", "endless_winter_red") . " created a frostbite");
          PlayAura("frostbite", $currentPlayer, effectController:$mainPlayer);
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
  if (SearchCurrentTurnEffects("amnesia_red", $player)) return $name;
  switch ($effectID) {
    case "mask_of_many_faces":
      $name = $effectParameter;
      break;
    case "be_like_water_red":
    case "be_like_water_yellow":
    case "be_like_water_blue":
      $name = $effectParameter;
      break;
    case "crouching_tiger":
      $name = $effectParameter;
      break;
    case "retrace_the_past_blue":
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
        case "herald_of_triumph_red":
        case "herald_of_triumph_yellow":
        case "herald_of_triumph_blue":
          $mod -= 1;
          break;
        case "figment_of_triumph_yellow":
        case "victoria_archangel_of_triumph":
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
        case "star_struck_yellow":
          if ($attackValue <= $effectArr[1] && ($type == "AA" || $resolvedAbilityType == "AA" || $abilityType == "AA") && ($hasNoAbilityTypes || $resolvedAbilityType == "AA")) {
              $restrictedBy = "star_struck_yellow";
          }
          break;
        case "WarmongersPeace":
          if (($type == "AA" && !str_contains(GetAbilityTypes($cardID), "I") || (TypeContains($cardID, "W", $mainPlayer) && $resolvedAbilityType != "I"))) $restrictedBy = "warmongers_diplomacy_blue";
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
        case "burdens_of_the_past_blue":
          if (in_array(GamestateSanitize(NameOverride($cardID, $currentPlayer)), $effectArr) && CardType($cardID) == "DR" && ($turn[0] == "A" || $turn[0] == "D" || $turn[0] == "INSTANT")) $restriction = "burdens_of_the_past_blue";
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
        case "chains_of_eminence_red":
          if (GamestateSanitize(NameOverride($cardID)) == $effectArr[1]) $restrictedBy = "chains_of_eminence_red";
          break;
        case "censor_red":
          if ($from != "PLAY" && !IsStaticType(CardType($cardID)) && GamestateSanitize(CardName($cardID)) == $effectArr[1]) $restrictedBy = "censor_red";
          break;
        case "WarmongersWar":
          // warmongers processing for meld cards handled in AddPrePitchDecisionQueue
          if (DelimStringContains($type, "A") && !HasMeld($cardID) && CardType($cardID) != "W") $restrictedBy = "warmongers_diplomacy_blue";
          break;
        case "WarmongersPeace":
          // !str_contains(GetAbilityTypes($cardID), "I") should allow discarding attack actions for instant abilities under peace
          if (($type == "AA" && !str_contains(GetAbilityTypes($cardID), "I")) || (TypeContains($cardID, "W", $currentPlayer) && GetResolvedAbilityType($cardID) != "I")) $restrictedBy = "warmongers_diplomacy_blue";
          break;
        case "kabuto_of_imperial_authority":
          if (IsWeapon($cardID, $from) && !WeaponWithNonAttack($cardID, $from)) $restrictedBy = "kabuto_of_imperial_authority";
          break;
        case "coat_of_allegiance":
        case "oath_of_loyalty_red":
          if (!$resolutionCheck) {
            if (!SearchCurrentTurnEffects("fealty", $currentPlayer) && !TalentContains($cardID, "DRACONIC") && $from != "PLAY" && $from != "EQUIP" && $from != "CHAR" && !str_contains(GetAbilityTypes($cardID), "I")) {
              if (TypeContains($cardID, "AA")) {
                // this case is needed because brand with cinderclaw isn't set to become active until after the attack is played
                $restrict = true;
                for ($j = 0; $j < count($currentTurnEffects); $j += CurrentTurnEffectPieces()) {
                  switch ($currentTurnEffects[$j]) {
                    case "brand_with_cinderclaw_red":
                    case "brand_with_cinderclaw_yellow":
                    case "brand_with_cinderclaw_blue":
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
  $foundNullTime = SearchItemForModalities(GamestateSanitize(NameOverride($cardID)), $otherPlayer, "null_time_zone_blue") != -1;
  $foundNullTime = $foundNullTime || SearchItemForModalities(GamestateSanitize(NameOverride($cardID)), $currentPlayer, "null_time_zone_blue") != -1;
  if($foundNullTime && $from == "HAND"){
    $restrictedBy = "null_time_zone_blue";
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
        case "art_of_war_yellow-1":
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
  $cardID = ExtractCardID($effectID);
  switch ($cardID) {
    case "frostbite":
    case "barbed_castaway":
    case "adaptive_plating":
    case "adaptive_dissolver":
    case "marked":
      return true;
    default:
      return false;
  }
}
