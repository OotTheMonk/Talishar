<?php

  function MONAbilityCost($cardID)
  {
    switch($cardID)
    {
      case "great_library_of_solana": return 0;
      case "prism_sculptor_of_arc_light": case "prism": return 2;
      case "ser_boltyn_breaker_of_dawn": case "boltyn": case "raydn_duskbane": return 0;
      case "halo_of_illumination": return 1;
      case "dream_weavers": return 0;
      case "hatchet_of_body": case "hatchet_of_mind": return 1;
      case "gallantry_gold": return 1;
      case "hexagore_the_death_hydra": return 2;
      case "chane_bound_by_shadow": case "chane": return 0;
      case "galaxxi_black": return 1;
      case "ebon_fold": return 1;
      case "guardian_of_the_shadowrealm_red": return 2;
      case "blasmophet_the_soul_harvester": return 0;
      case "ursur_the_soul_reaper": return 0;
      case "ravenous_meataxe": return 2;
      case "dread_scythe": return 3;
      case "blood_drop_brocade": case "stubby_hammerers": return 0;
      case "time_skippers": return 3;
      case "exude_confidence_red": return 3;
      case "rally_the_rearguard_red": case "rally_the_rearguard_yellow": case "rally_the_rearguard_blue": return 0;
      default: return 0;
    }
  }

  function MONAbilityType($cardID, $index=-1, $from="")
  {
    global $currentPlayer, $mainPlayer, $defPlayer;
    switch($cardID)
    {
      case "great_library_of_solana": return "A";
      case "prism_sculptor_of_arc_light": case "prism": return "I";
      case "ser_boltyn_breaker_of_dawn": case "boltyn": return "AR";
      case "raydn_duskbane": return "AA";
      case "halo_of_illumination": return "I";
      case "dream_weavers": return "A";
      case "hatchet_of_body": case "hatchet_of_mind": return "AA";
      case "gallantry_gold": return "A";
      case "hexagore_the_death_hydra": return "AA";
      case "chane_bound_by_shadow": case "chane": return "A";
      case "galaxxi_black": return "AA";
      case "ebon_fold": return "I";
      case "guardian_of_the_shadowrealm_red": return "A";
      case "blasmophet_the_soul_harvester": return "AA";
      case "ursur_the_soul_reaper": return "AA";
      case "ravenous_meataxe": return "AA";
      case "dread_scythe": return "AA";
      case "aether_ironweave": return "A";
      case "blood_drop_brocade": return "I";
      case "stubby_hammerers": case "time_skippers": return "A";
      case "exude_confidence_red": 
        if($from == "PLAY") return "I";
        else return "AA";
      case "rally_the_rearguard_red": case "rally_the_rearguard_yellow": case "rally_the_rearguard_blue": 
        if($from == "PLAY") return "I";
        else return "AA";
      default: return "";
    }
  }

  function MONAbilityHasGoAgain($cardID)
  {
    switch($cardID)
    {
      case "great_library_of_solana": return true;
      case "dream_weavers": return true;
      case "gallantry_gold": return true;
      case "chane_bound_by_shadow": case "chane": return true;
      case "aether_ironweave": return true;
      case "stubby_hammerers": return true;
      default: return false;
    }
  }

  function MONEffectAttackModifier($cardID)
  {
    global $mainPlayer, $CS_NumNonAttackCards, $CombatChain;
    $arr = explode(",", $cardID);
    $cardID = $arr[0];
    switch($cardID)
    {
      case "herald_of_triumph_red": case "herald_of_triumph_yellow": case "herald_of_triumph_blue": return -1;
      case "lumina_ascension_yellow": return 1;
      case "v_of_the_vanguard_yellow": return 1;
      case "seek_enlightenment_red": return 3;
      case "seek_enlightenment_yellow": return 2;
      case "seek_enlightenment_blue": return 1;
      case "ray_of_hope_yellow": return 1;
      case "phantasmify_red": return 5;
      case "phantasmify_yellow": return 4;
      case "phantasmify_blue": return 3;
      case "gallantry_gold": return 1;
      case "spill_blood_red": return 2;
      case "dusk_path_pilgrimage_red": return 3;
      case "dusk_path_pilgrimage_yellow": return 2;
      case "dusk_path_pilgrimage_blue": return 1;
      case "plow_through_red": return 3;
      case "plow_through_yellow": return 2;
      case "plow_through_blue": return 1;
      case "second_swing_red": return 4;
      case "second_swing_yellow": return 3;
      case "second_swing_blue": return 2;
      case "endless_maw_red": case "endless_maw_yellow": case "endless_maw_blue": return 3;
      case "convulsions_from_the_bellows_of_hell_red": return 3;
      case "convulsions_from_the_bellows_of_hell_yellow": return 2;
      case "convulsions_from_the_bellows_of_hell_blue": return 1;
      case "unworldly_bellow_red": return 4;
      case "unworldly_bellow_yellow": return 3;
      case "unworldly_bellow_blue": return 2;
      case "seeping_shadows_red": case "seeping_shadows_yellow": case "seeping_shadows_blue": return 1;
      case "bounding_demigon_red": case "bounding_demigon_yellow": case "bounding_demigon_blue": return 1;
      case "rift_bind_red": case "rift_bind_yellow": case "rift_bind_blue": return GetClassState($mainPlayer, $CS_NumNonAttackCards);
      case "shadow_puppetry_red": return 1;
      case "soul_harvest_blue": return $arr[1];
      case "howl_from_beyond_red": return 3;
      case "howl_from_beyond_yellow": return 2;
      case "howl_from_beyond_blue": return 1;
      case "spew_shadow_red": return 2;
      case "ravenous_meataxe": return 2;
      case "tear_limb_from_limb_blue": return ModifiedAttackValue($CombatChain->AttackCard()->ID(), $mainPlayer, "CC", source:"");
      case "stubby_hammerers": return 1;
      case "rouse_the_ancients_blue": return 7;
      case "captains_call_red-1": case "captains_call_yellow-1": case "captains_call_blue-1": return 2;
      case "adrenaline_rush_red": case "adrenaline_rush_yellow": case "adrenaline_rush_blue": return 3;
      case "brandish_red": case "brandish_yellow": case "brandish_blue": return 1;
      case "minnowism_red": return 3;
      case "minnowism_yellow": return 2;
      case "minnowism_blue": return 1;
      case "warmongers_recital_red": return 3;
      case "warmongers_recital_yellow": return 2;
      case "warmongers_recital_blue": return 1;
      default: return 0;
    }
  }

  function MONCombatEffectActive($cardID, $attackID)
  {
    global $defPlayer, $mainPlayer;
    $arr = explode(",", $cardID);
    $cardID = $arr[0];
    switch($cardID)
    {
      case "herald_of_triumph_red": case "herald_of_triumph_yellow": case "herald_of_triumph_blue": return CardType($attackID) == "AA";
      case "lumina_ascension_yellow": return TypeContains($attackID, "W", $mainPlayer);
      case "v_of_the_vanguard_yellow": return true;
      case "seek_enlightenment_red": case "seek_enlightenment_yellow": case "seek_enlightenment_blue": return CardType($attackID) == "AA";
      case "ray_of_hope_yellow": $theirChar = GetPlayerCharacter($defPlayer); return TalentContains($theirChar[0], "SHADOW");
      case "phantasmal_footsteps": return true;
      case "dream_weavers": return ClassContains($attackID, "ILLUSIONIST", $mainPlayer) && CardType($attackID) == "AA";
      case "phantasmify_red": case "phantasmify_yellow": case "phantasmify_blue": return CardType($attackID) == "AA";
      case "gallantry_gold": return TypeContains($attackID, "W", $mainPlayer);
      case "spill_blood_red": return CardSubtype($attackID) == "Axe";
      case "dusk_path_pilgrimage_red": case "dusk_path_pilgrimage_yellow": case "dusk_path_pilgrimage_blue": return TypeContains($attackID, "W", $mainPlayer);
      case "plow_through_red": case "plow_through_yellow": case "plow_through_blue": return TypeContains($attackID, "W", $mainPlayer);
      case "second_swing_red": case "second_swing_yellow": case "second_swing_blue": return true;
      case "endless_maw_red": case "endless_maw_yellow": case "endless_maw_blue": return true;
      case "writhing_beast_hulk_red": case "writhing_beast_hulk_yellow": case "writhing_beast_hulk_blue": return true;
      case "convulsions_from_the_bellows_of_hell_red": case "convulsions_from_the_bellows_of_hell_yellow": case "convulsions_from_the_bellows_of_hell_blue": return CardType($attackID) == "AA";
      case "dread_screamer_red": case "dread_screamer_yellow": case "dread_screamer_blue": return true;
      case "unworldly_bellow_red": case "unworldly_bellow_yellow": case "unworldly_bellow_blue": return CardType($attackID) == "AA" && (ClassContains($attackID, "BRUTE", $mainPlayer) || TalentContains($attackID, "SHADOW", $mainPlayer));
      case "chane_bound_by_shadow": case "chane": return ClassContains($attackID, "RUNEBLADE", $mainPlayer) || TalentContains($attackID, "SHADOW", $mainPlayer);
      case "seeping_shadows_red": return CardType($attackID) == "AA" && CardCost($attackID) <= 2;
      case "seeping_shadows_yellow": return CardType($attackID) == "AA" && CardCost($attackID) <= 1;
      case "seeping_shadows_blue": return CardType($attackID) == "AA" && CardCost($attackID) == 0;
      case "bounding_demigon_red": case "bounding_demigon_yellow": case "bounding_demigon_blue": return true;
      case "rift_bind_red": case "rift_bind_yellow": case "rift_bind_blue": return true;
      case "shadow_puppetry_red": return CardType($attackID) == "AA";
      case "consuming_aftermath_red": case "consuming_aftermath_yellow": case "consuming_aftermath_blue": return true;
      case "soul_harvest_blue": return true;
      case "howl_from_beyond_red": case "howl_from_beyond_yellow": case "howl_from_beyond_blue": return CardType($attackID) == "AA";
      case "spew_shadow_red": return true;
      case "eclipse_existence_blue": return true;
      case "ravenous_meataxe": return true;
      case "tear_limb_from_limb_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "BRUTE", $mainPlayer);
      case "pulping_red": case "pulping_yellow": case "pulping_blue": return true;
      case "stubby_hammerers": return CardType($attackID) == "AA" && AttackValue($attackID) <= 3;//Base power
      case "rouse_the_ancients_blue": return true;
      case "captains_call_red-1": case "captains_call_red-2": return CardType($attackID) == "AA" && CardCost($attackID) <= 2;
      case "captains_call_yellow-1": case "captains_call_yellow-2": return CardType($attackID) == "AA" && CardCost($attackID) <= 1;
      case "captains_call_blue-1": case "captains_call_blue-2": return CardType($attackID) == "AA" && CardCost($attackID) <= 0;
      case "adrenaline_rush_red": case "adrenaline_rush_yellow": case "adrenaline_rush_blue": return true;
      case "brandish_red": case "brandish_yellow": case "brandish_blue": return IsWeaponAttack();
      case "pound_for_pound_red": case "pound_for_pound_yellow": case "pound_for_pound_blue": return true;
      case "minnowism_red": case "minnowism_yellow": case "minnowism_blue": return CardType($attackID) == "AA" && AttackValue($attackID) <= 3;//Base power
      case "warmongers_recital_red": case "warmongers_recital_yellow": case "warmongers_recital_blue": return CardType($attackID) == "AA";
      case "lady_barthimont": return true;
      default: return false;
    }
  }