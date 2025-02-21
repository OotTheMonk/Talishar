<?php

function DTDAbilityCost($cardID)
{
  switch($cardID) {
    case "prism_awakener_of_sol": case "prism_advent_of_thrones": return 2;
    case "luminaris_celestial_fury": return 2;
    case "empyrean_rapture": return 1;
    case "beaming_blade": return 2;
    case "v_for_valor_red": case "v_for_valor_yellow": case "v_for_valor_blue": return 1;
    case "radiant_view": case "radiant_raiment": case "radiant_touch": case "radiant_flow": return 0;
    case "hell_hammer": return 2;
    case "spoiled_skull": return 1;
    case "flail_of_agony": return 0;
    case "grimoire_of_the_haunt": return 1;
    case "levia_redeemed": return 0;
    case "nasreth_the_soul_harrower": return 0;
    case "rugged_roller": return 1;
    case "decimator_great_axe": return 3;
    case "ironsong_versus": return 1;
    case "scepter_of_pain": return 2;
    case "suraya_archangel_of_erudition": case "themis_archangel_of_judgment": case "aegis_archangel_of_protection": case "sekem_archangel_of_ravages"://Angels
    case "avalon_archangel_of_rebirth": case "metis_archangel_of_tenacity": case "victoria_archangel_of_triumph": case "bellona_archangel_of_war": return 2;
    default: return 0;
  }
}

function DTDAbilityType($cardID, $index = -1)
{
  switch($cardID) {
    case "prism_awakener_of_sol": case "prism_advent_of_thrones": return "I";
    case "luminaris_celestial_fury": return "I";
    case "empyrean_rapture": return "I";
    case "beaming_blade": return "AA";
    case "v_for_valor_red": case "v_for_valor_yellow": case "v_for_valor_blue": return "AR";
    case "radiant_view": case "radiant_raiment": case "radiant_touch": case "radiant_flow": return "I";
    case "hell_hammer": return "AA";
    case "spoiled_skull": return "A";
    case "flail_of_agony": return "AA";
    case "grimoire_of_the_haunt": return "I";
    case "levia_redeemed": return "A";
    case "nasreth_the_soul_harrower": return "AA";
    case "rugged_roller": return "AA";
    case "decimator_great_axe": return "AA";
    case "ironsong_versus": return "A";
    case "scepter_of_pain": return "A";
    case "suraya_archangel_of_erudition": case "themis_archangel_of_judgment": case "aegis_archangel_of_protection": case "sekem_archangel_of_ravages"://Angels
    case "avalon_archangel_of_rebirth": case "metis_archangel_of_tenacity": case "victoria_archangel_of_triumph": case "bellona_archangel_of_war": return "AA";
    default: return "";
  }
}

function DTDAbilityHasGoAgain($cardID)
{
  switch($cardID) {
    case "spoiled_skull": return true;
    case "ironsong_versus": return true;
    default: return false;
  }
}

function DTDEffectAttackModifier($cardID)
{
  $params = explode(",", $cardID);
  $dashArr = explode(",", $cardID);
  $cardID = $params[0];
  if(count($params) > 1) $parameter = $params[1];
  if(strlen($cardID) > 6) $cardID = $dashArr[0];
  switch($cardID) {
    case "figment_of_triumph_yellow": return -1;
    case "angelic_descent_red": return 3;
    case "angelic_descent_yellow": return 2;
    case "angelic_descent_blue": return 1;
    case "angelic_wrath_red": return 4;
    case "angelic_wrath_yellow": return 3;
    case "angelic_wrath_blue": return 2;
    case "prayer_of_bellona_yellow": return 2;//Prayer of Bellona
    case "beaming_bravado_red": case "beaming_bravado_yellow": case "beaming_bravado_blue": return 1;//Beaming Bravado
    case "v_for_valor_red": return 3;
    case "v_for_valor_yellow": return 2;
    case "v_for_valor_blue": return 1;
    case "resounding_courage_red": return 3;//Resounding Courage
    case "resounding_courage_yellow": return 2;
    case "resounding_courage_blue": return 1;
    case "charge_of_the_light_brigade_red": return 3;//Charge of the Light Brigade
    case "charge_of_the_light_brigade_yellow": return 2;
    case "charge_of_the_light_brigade_blue": return 1;
    case "lumina_lance_yellow-1": return 2;
    case "lay_to_rest_red": case "lay_to_rest_yellow": case "lay_to_rest_blue": return 1;
    case "blood_dripping_frenzy_blue": return $parameter;
    case "shaden_scream_red": return 5;
    case "shaden_scream_yellow": return 4;
    case "shaden_scream_blue": return 3;
    case "tribute_to_demolition_red": case "tribute_to_demolition_yellow": case "tribute_to_demolition_blue": return 2;
    case "tribute_to_the_legions_of_doom_red": case "tribute_to_the_legions_of_doom_yellow": case "tribute_to_the_legions_of_doom_blue": return 2;
    case "envelop_in_darkness_red": return 3;
    case "envelop_in_darkness_yellow": return 2;
    case "envelop_in_darkness_blue": return 1;
    case "putrid_stirrings_red": return 5;
    case "putrid_stirrings_yellow": return 4;
    case "putrid_stirrings_blue": return 3;
    case "beseech_the_demigon_red": return 3;
    case "beseech_the_demigon_yellow": return 2;
    case "beseech_the_demigon_blue": return 1;
    case "anthem_of_spring_blue": return 1;//Anthem of Spring
    case "chorus_of_ironsong_yellow": return 1;
    case "runic_reckoning_red": return 3;
    case "hack_to_reality_yellow": return 2;
    case "courage": return 1;//Courage
    case "victoria_archangel_of_triumph": return -1;
    default:
      return 0;
  }
}

function DTDCombatEffectActive($cardID, $attackID)
{
  global $combatChainState, $mainPlayer, $combatChainState, $CCS_AttackNumCharged, $CombatChain;
  global $Card_LifeBanner, $Card_ResourceBanner, $CCS_WasRuneGate;
  $params = explode(",", $cardID);
  $dashArr = explode(",", $cardID);
  $cardID = $params[0];
  if(strlen($cardID) > 6) $cardID = $dashArr[0];
  switch($cardID) {
    case "figment_of_tenacity_yellow": return true;
    case "figment_of_triumph_yellow": return CardType($attackID) == "AA";
    case "angelic_descent_red": case "angelic_descent_yellow": case "angelic_descent_blue": return SubtypeContains($attackID, "Angel", $mainPlayer);
    case "angelic_wrath_red": case "angelic_wrath_yellow": case "angelic_wrath_blue": return str_contains(NameOverride($attackID, $mainPlayer), "Herald");
    case "beckoning_light_red": return CardType($attackID) == "AA";//Beckoning Light
    case "spirit_of_war_red": return CardType($attackID) == "AA";//Spirit of War
    case "prayer_of_bellona_yellow": return true;//Prayer of Bellona
    case "beaming_bravado_red": case "beaming_bravado_yellow": case "beaming_bravado_blue": return true;//Beaming Bravado
    case "v_for_valor_red": case "v_for_valor_yellow": case "v_for_valor_blue": return true;
    case "glaring_impact_red": case "glaring_impact_yellow": case "glaring_impact_blue": return true;//Glaring Impact
    case "light_the_way_red": case "light_the_way_yellow": case "light_the_way_blue": return true;
    case "resounding_courage_red": case "resounding_courage_yellow": case "resounding_courage_blue": return true;//Resounding Courage
    case "charge_of_the_light_brigade_red": case "charge_of_the_light_brigade_yellow": case "charge_of_the_light_brigade_blue": return $combatChainState[$CCS_AttackNumCharged] > 0;//Charge of the Light Brigade
    case "lumina_lance_yellow-1": case "lumina_lance_yellow-2": case "lumina_lance_yellow-3": return true;
    case "lay_to_rest_red": case "lay_to_rest_yellow": case "lay_to_rest_blue": return true;
    case "defender_of_daybreak_red": case "defender_of_daybreak_yellow": case "defender_of_daybreak_blue": return true;
    case "blood_dripping_frenzy_blue": return ClassContains($attackID, "BRUTE", $mainPlayer) || TalentContains($attackID, "SHADOW", $mainPlayer);
    case "shaden_scream_red": case "shaden_scream_yellow": case "shaden_scream_blue": return ClassContains($attackID, "BRUTE", $mainPlayer) || TalentContains($attackID, "SHADOW", $mainPlayer);
    case "tribute_to_demolition_red": case "tribute_to_demolition_yellow": case "tribute_to_demolition_blue": return true;
    case "tribute_to_the_legions_of_doom_red": case "tribute_to_the_legions_of_doom_yellow": case "tribute_to_the_legions_of_doom_blue": return true;
    case "envelop_in_darkness_red": case "envelop_in_darkness_yellow": case "envelop_in_darkness_blue": return $combatChainState[$CCS_WasRuneGate] == 1;
    case "putrid_stirrings_red": case "putrid_stirrings_yellow": case "putrid_stirrings_blue": return $combatChainState[$CCS_WasRuneGate] == 1;
    case "anthem_of_spring_blue": return CardType($attackID) == "AA";//Anthem of Spring
    case "call_down_the_lightning_yellow": return true;//Call Down the Lightning
    case "beseech_the_demigon_red": case "beseech_the_demigon_yellow": case "beseech_the_demigon_blue":
      return SearchCurrentTurnEffects($cardID . "," . $attackID, $mainPlayer) && $CombatChain->AttackCard()->From() == "BANISH";
    case "tear_through_the_portal_red": case "tear_through_the_portal_yellow": case "tear_through_the_portal_blue":
      return SearchCurrentTurnEffects($cardID . "," . $attackID, $mainPlayer) && $CombatChain->AttackCard()->From() == "BANISH";
    case "ironsong_versus": return SubtypeContains($attackID, "Sword", $mainPlayer);//Ironsong Versus
    case "chorus_of_ironsong_yellow": return true;
    case "runic_reckoning_red": return CardType($attackID) == "AA" && ClassContains($attackID, "RUNEBLADE", $mainPlayer);
    case "hack_to_reality_yellow": return true;
    case "hack_to_reality_yellow-HIT": return true;
    case "courage": return true;//Courage
    case $Card_LifeBanner: return true;
    case $Card_ResourceBanner: return true;
    case "metis_archangel_of_tenacity": return true;
    case "victoria_archangel_of_triumph": return CardType($attackID) == "AA";
    default:
      return false;
  }
}

function DTDPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
{
  global $currentPlayer, $defPlayer, $CS_NumCharged, $CS_DamagePrevention, $CS_NumCardsDrawn, $combatChain, $CombatChain;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $rv = "";
  switch($cardID) {
    case "prism_awakener_of_sol": case "prism_advent_of_thrones":
      AddDecisionQueue("AWAKEN", $currentPlayer, $target, 1);
      return "";
    case "luminaris_celestial_fury":
      GiveAttackGoAgain();
      return "";
    case "empyrean_rapture":
      AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
      return "";
    case "figment_of_erudition_yellow":
      PlayAura("ponder", $currentPlayer);
      return "";
    case "figment_of_judgment_yellow":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRBANISH&MYBANISH");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to turn face-down");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "TURNBANISHFACEDOWN", 1);
      return "";
    case "figment_of_protection_yellow":
      PlayAura("spectral_shield", $currentPlayer);
      return "";
    case "figment_of_ravages_yellow":
      DealArcane(1, 2, "PLAYCARD", $cardID, false, $currentPlayer);
      return "";
    case "figment_of_rebirth_yellow":
      MZMoveCard($currentPlayer, "MYDISCARD:type=A;pitch=2&MYDISCARD:type=AA;pitch=2", "MYTOPDECK", may:true);
      return;
    case "figment_of_tenacity_yellow":
      if(count($combatChain) > 0) AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
      else AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "figment_of_triumph_yellow":
      AddCurrentTurnEffect($cardID, $otherPlayer);
      return "";
    case "figment_of_war_yellow":
      PlayAura("courage", $currentPlayer);
      return "";
    case "angelic_descent_red": case "angelic_descent_yellow": case "angelic_descent_blue":
      GiveAttackGoAgain();
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "angelic_wrath_red": case "angelic_wrath_yellow": case "angelic_wrath_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer, "PLAY");
      break;
    case "celestial_reprimand_red": case "celestial_reprimand_yellow": case "celestial_reprimand_blue":
      if($cardID == "celestial_reprimand_red") $amount = -3;
      else if($cardID == "celestial_reprimand_yellow") $amount = -2;
      else $amount = -1;
      if($target != "-") $CombatChain->Card(intval($target))->ModifyPower($amount);
      return "";
    case "celestial_resolve_red": case "celestial_resolve_yellow": case "celestial_resolve_blue":
      $options = GetChainLinkCards($defPlayer, nameContains:"Herald");
      if($options == "") return "No defending attack action cards";
      WriteLog($options);
      AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, $options);
      AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $currentPlayer, PlayBlockModifier($cardID), 1);
      return "";
    case "prayer_of_bellona_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      $deck = new Deck($currentPlayer);
      if($deck->Reveal() && PitchValue($deck->Top()) == 2)
      {
        $card = $deck->Top(remove:true);
        AddPlayerHand($card, $currentPlayer, "DECK");
        Charge(false);
      }
      return "";
    case "v_for_valor_red": case "v_for_valor_yellow": case "v_for_valor_blue"://V for Valor
      if($from == "PLAY") AddCurrentTurnEffect($cardID, $currentPlayer, from:"PLAY");
      return "";
    case "resounding_courage_red": case "resounding_courage_yellow": case "resounding_courage_blue"://Resounding Courage
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if(GetClassState($currentPlayer, $CS_NumCharged) > 0) PlayAura("courage", $currentPlayer);
      return "";
    case "charge_of_the_light_brigade_red": case "charge_of_the_light_brigade_yellow": case "charge_of_the_light_brigade_blue"://Charge of the Light Brigade
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "radiant_view": case "radiant_raiment": case "radiant_touch": case "radiant_flow":
      IncrementClassState($currentPlayer, $CS_DamagePrevention, 2);
      return "";
    case "lumina_lance_yellow":
      if($additionalCosts != "-"){
        $modes = explode(",", $additionalCosts);
        for($i=0; $i<count($modes); ++$i)
        {
          BanishFromSoul($currentPlayer);
          switch($modes[$i])
          {
            case "+2_Attack": AddCurrentTurnEffect("lumina_lance_yellow-1", $currentPlayer); break;
            case "Draw_on_hit": AddCurrentTurnEffect("lumina_lance_yellow-2", $currentPlayer); break;
            case "Go_again_on_hit": AddCurrentTurnEffect("lumina_lance_yellow-3", $currentPlayer); break;
            default: break;
          }
        }
      }
      return "";
    case "lay_to_rest_red": case "lay_to_rest_yellow": case "lay_to_rest_blue"://Lay to Rest
      $theirChar = &GetPlayerCharacter($otherPlayer);
      if(TalentContains($theirChar[0], "SHADOW", $otherPlayer)) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "blessing_of_salvation_red": GainHealth(3, $currentPlayer); break;//Blessing of Salvation
    case "blessing_of_salvation_yellow": GainHealth(2, $currentPlayer); break;
    case "blessing_of_salvation_blue": GainHealth(1, $currentPlayer); break;
    case "cleansing_light_red": case "cleansing_light_yellow": case "cleansing_light_blue"://Cleansing Light
      $params = explode("-", $target);
      if(substr($params[0], 0, 5) != "THEIR") {
        $zone = "MYAURAS-";
        $player = $currentPlayer;
      }
      else {
        $zone = "THEIRAURAS-";
        $player = $otherPlayer;
      }
      $index = SearchAurasForUniqueID($params[1], $player);
      if ($index != -1) {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $zone . $index, 1);
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      } 
      else {
        WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect.");
        return "FAILED";
      }
      return "";
    case "blistering_assault_red": case "blistering_assault_yellow": case "blistering_assault_blue":
      if(SearchPitchForColor($currentPlayer, 2) > 0) GiveAttackGoAgain();
      return "";
    case "break_of_dawn_red": case "break_of_dawn_yellow": case "break_of_dawn_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "hell_hammer":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "spoiled_skull":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "MULTIACTIONSBANISH");
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "3-", 1);
      AddDecisionQueue("APPENDLASTRESULT", $currentPlayer, "-3", 1);
      AddDecisionQueue("MULTICHOOSEBANISH", $currentPlayer, "<-", 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "SPOILEDSKULL", 1);
      return "";
    case "shaden_death_hydra_yellow":
      $numBD = SearchCount(SearchBanish($currentPlayer, "", "", -1, -1, "", "", true));
      $damage = 13 - $numBD;
      DamageTrigger($currentPlayer, $damage, "PLAYCARD", $cardID);
      return "";
    case "blood_dripping_frenzy_blue":
      $cards = explode(",", $additionalCosts);
      $num6Pow = 0;
      for($i=0; $i<count($cards); ++$i)
      {
        if(HasBloodDebt($cards[$i])) Draw($currentPlayer);
        if(ModifiedAttackValue($cards[$i], $currentPlayer, "HAND", source:$cardID) >= 6) ++$num6Pow;
      }
      if($num6Pow > 0) AddCurrentTurnEffect("blood_dripping_frenzy_blue," . $num6Pow, $currentPlayer);
      return "";
    case "ram_raider_red": case "ram_raider_yellow": case "ram_raider_blue":
      if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) GiveAttackGoAgain();
      return "";
    case "shaden_scream_red": case "shaden_scream_yellow": case "shaden_scream_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "tribute_to_demolition_red": case "tribute_to_demolition_yellow": case "tribute_to_demolition_blue":
      if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "tribute_to_the_legions_of_doom_red": case "tribute_to_the_legions_of_doom_yellow": case "tribute_to_the_legions_of_doom_blue":
      if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "grimoire_of_the_haunt":
      PlayAura("eloquence", $currentPlayer);
      return "";
    case "funeral_moon_red":
      PlayAura("runechant", $currentPlayer);
      return "";
    case "requiem_for_the_damned_red":
      PlayAura("eloquence", $currentPlayer);
      return "";
    case "oblivion_blue":
      PlayAlly("nasreth_the_soul_harrower", $currentPlayer);
      return "";
    case "envelop_in_darkness_red": case "envelop_in_darkness_yellow": case "envelop_in_darkness_blue":
      PlayAura("runechant", $currentPlayer);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "putrid_stirrings_red": case "putrid_stirrings_yellow": case "putrid_stirrings_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "levia_redeemed":
      ResolveTransformHero($currentPlayer, "levia_redeemed", "");
      return "";
    case "dabble_in_darkness_red":
      $deck = new Deck($currentPlayer);
      if($deck->Empty()) return "Ravenous Dabble does not get negative attack because your deck is empty";
      $top = $deck->BanishTop();
      $pitch = PitchValue($top);
      $CombatChain->AttackCard()->ModifyPower(-$pitch);
      return "";
    case "chains_of_mephetis_blue":
      if($from == "BANISH")
      {
        $auras = &GetAuras($currentPlayer);
        $index = count($auras) - AuraPieces();
        $auras[$index+2] = 1;
      }
      return "";
    case "dimenxxional_vortex":
      MZMoveCard($currentPlayer, "MYARS", "MYBANISH,ARS,-");
      MZMoveCard($otherPlayer, "MYARS", "MYBANISH,ARS,-");
      return "";
    case "grim_feast_red": GainHealth(3, $currentPlayer); return "";
    case "grim_feast_yellow": GainHealth(2, $currentPlayer); return "";
    case "grim_feast_blue": GainHealth(1, $currentPlayer); return "";
    case "vile_inquisition_red": case "vile_inquisition_yellow": case "vile_inquisition_blue":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target hero");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Yourself");
      AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, $cardID, 1);
      return "";
    case "soul_cleaver_red": case "soul_cleaver_yellow": case "soul_cleaver_blue":
      $theirSoul = &GetSoul($otherPlayer);
      if(count($theirSoul) > 0) GiveAttackGoAgain();
      return "";
    case "beseech_the_demigon_red": case "beseech_the_demigon_yellow": case "beseech_the_demigon_blue":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYBANISH:type=AA");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
      AddDecisionQueue("ADDCURRENTEFFECTLASTRESULT", $currentPlayer, $cardID . ",", 1);
      return "";
    case "tear_through_the_portal_red": case "tear_through_the_portal_yellow": case "tear_through_the_portal_blue":
      $pitch = "";
      if (PitchValue($cardID) == 1) $pitch = "1";
      elseif (PitchValue($cardID) == 2) $pitch = "2";
      else $pitch = "3";
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYBANISH:type=A;pitch=". $pitch . "&MYBANISH:type=AA;pitch=". $pitch);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
      AddDecisionQueue("ADDCURRENTEFFECTLASTRESULT", $currentPlayer, $cardID . ",", 1);
      return "";
    case "anthem_of_spring_blue"://Anthem of Spring
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "northern_winds_blue":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRCHAR:type=E", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which card you want to freeze", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "FREEZE", 1);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRITEMS", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which card you want to freeze", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "FREEZE", 1);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRALLY");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which card you want to freeze", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "FREEZE", 1);
      return "";
    case "call_down_the_lightning_yellow"://Call Down the Lightning
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "dig_up_dinner_blue":
      $discard = new Discard($currentPlayer);
      $deck = new Deck($currentPlayer);
      $cards = explode(",", $discard->RemoveRandom(3));
      $num6plus = 0;
      for($i=0; $i<count($cards); ++$i)
      {
        WriteLog(CardLink($cards[$i], $cards[$i]) . " chosen randomly");
        if(ModifiedAttackValue($cards[$i], $currentPlayer, "GY", source:$cardID) >= 6) {
          ++$num6plus;
          $deck->AddBottom($cards[$i], "GY");
        }
        else $discard->Add($cards[$i]);
      }
      if($num6plus > 0) {
        GainHealth($num6plus, $currentPlayer);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      }
      return "";
    case "ironsong_versus"://Ironsong Versus
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "chorus_of_ironsong_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "morlock_hill_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "scepter_of_pain":
      DealArcane(1, 3, "ABILITY", $cardID);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "SCEPTEROFPAIN");
      return "";
    case "bequest_the_vast_beyond_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "runic_reckoning_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "alluring_inducement_yellow":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRHAND:type=AA");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "1", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
      AddDecisionQueue("CURRENTATTACKBECOMES", $currentPlayer, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{1}", 1);
      return "";
    case "lost_in_thought_red"://Lost in Thought
      LookAtHand($otherPlayer);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRHAND:type=AA");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $otherPlayer, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $otherPlayer, "ponder", 1);
      AddDecisionQueue("PUTPLAY", $otherPlayer, "-", 1);
      return "";
    case "hold_the_line_blue":
      if(GetClassState($otherPlayer, $CS_NumCardsDrawn) >= 2)
      {
        IncrementClassState($currentPlayer, $CS_DamagePrevention, 3);
        WriteLog("Prevents the next 3 damage");
      }
      return "";
    case "hack_to_reality_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect($cardID . "-HIT", $currentPlayer);
      return "";
    case "warmongers_diplomacy_blue":
      WarmongersDiplomacy($otherPlayer);
      AddDecisionQueue("ADDTHEIRNEXTTURNEFFECT", $otherPlayer, "<-");
      WarmongersDiplomacy($currentPlayer);
      AddDecisionQueue("ADDTHEIRNEXTTURNEFFECT", $currentPlayer, "<-");
      return "";
    case "poison_the_well_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    default:
      return "";
  }
}

function WarmongersDiplomacy($player)
{
  AddDecisionQueue("BUTTONINPUT", $player, "War,Peace");
  AddDecisionQueue("SETDQVAR", $player, "0", 1);
  AddDecisionQueue("WRITELOG", $player, "Player $player chose {0}", 1);
  AddDecisionQueue("PREPENDLASTRESULT", $player, "Warmongers");
}

function DTDHitEffect($cardID)
{
  global $mainPlayer, $defPlayer;
  switch($cardID) {
    case "lay_to_rest_red": case "lay_to_rest_yellow": case "lay_to_rest_blue":
      if(IsHeroAttackTarget())
      {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRBANISH");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "TURNBANISHFACEDOWN", 1);
      }
      break;
    case "flail_of_agony":
      PlayAura("runechant", $mainPlayer);
      break;
    case "hungering_demigon_red": case "hungering_demigon_yellow": case "hungering_demigon_blue":
      if(IsHeroAttackTarget()) MZMoveCard($mainPlayer, "THEIRSOUL", "THEIRBANISH,SOUL,-");
      break;
    case "nasreth_the_soul_harrower":
      if(IsHeroAttackTarget()) MZMoveCard($mainPlayer, "THEIRSOUL", "THEIRBANISH,SOUL,-," . $cardID);
      break;
    case "censor_red":
      if(IsHeroAttackTarget()) {
        AddDecisionQueue("INPUTCARDNAME", $mainPlayer, "-");
        AddDecisionQueue("SETDQVAR", $mainPlayer, "0");
        AddDecisionQueue("WRITELOG", $mainPlayer, "<b>📣{0}</b> was chosen");
        AddDecisionQueue("ADDCURRENTANDNEXTTURNEFFECT", $defPlayer, "censor_red,{0}");
      }
      break;
    case "mischievous_meeps_red":
      if(IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS:minCost=0;maxCost=2");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to gain control");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "GAINCONTROL", 1);
        AddDecisionQueue("ELSE", $mainPlayer, "-");
        AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
      }
      break;
    default: break;
  }
}

function DoesAttackTriggerMirage()
{
  global $CombatChain, $mainPlayer;
  if(ClassContains($CombatChain->CurrentAttack(), "ILLUSIONIST", $mainPlayer)) return false;
  return CachedTotalAttack() >= 6;
}

function ProcessMirageOnBlock($index)
{
  global $mainPlayer;
  if(IsMirageActive($index) && DoesAttackTriggerMirage() && (SearchLayersForCardID("MIRAGE") == -1))
  {
    AddLayer("LAYER", $mainPlayer, "MIRAGE");
  }
}

function ProcessAllMirage()
{
  global $CombatChain;
  for($i=1; $i<$CombatChain->NumCardsActiveLink(); ++$i) {
    ProcessMirageOnBlock($i*CombatChainPieces());
  }
}

function IsMirageActive($index)
{
  global $CombatChain;
  if(!$CombatChain->HasCurrentLink()) return false;
  return HasMirage($CombatChain->Card($index)->ID());
}

function HasMirage($cardID)
{
  switch($cardID)
  {
    case "flicker_trick_red": return true;
    default: return false;
  }
}

function MirageLayer()
{
  global $CombatChain, $mainPlayer, $combatChainState, $defPlayer, $turn, $layers;
  if(DoesAttackTriggerMirage())
  {
    for($i=$CombatChain->NumCardsActiveLink()-1; $i>=1; --$i)
    {
      if(IsMirageActive($i*CombatChainPieces())) {
        $cardID = $CombatChain->Remove($i, cardNumber:true);
        AddGraveyard($cardID, $defPlayer, "CC");
        WriteLog(CardLink($cardID, $cardID) . " is destroyed by Mirage");
        if(ClassContains($cardID, "ILLUSIONIST", $mainPlayer)) PhantomTidemawDestroy();
      }
    }
  }
  else {
    $turn[0] = "A";
    $currentPlayer = $mainPlayer;
    for($i=count($layers)-LayerPieces(); $i >= 0; $i-=LayerPieces())
    {
      if($layers[$i] == "DEFENDSTEP" || ($layers[$i] == "LAYER" && $layers[$i+2] == "MIRAGE"))
      {
        for($j=$i; $j<($i+LayerPieces()); ++$j) unset($layers[$j]);
      }
    }
    $layers = array_values($layers);
  }
}

function ResolveTransformHero($player, $cardID, $parameter)
{
  $permIndex = SearchPermanentsForCard($player, "levia_redeemed");
  if($permIndex != "") RemovePermanent($player, $permIndex);
  $inventoryIndex = SearchInventoryForCard($player, "levia_redeemed");
  if($inventoryIndex != "") RemoveInventory($player, $inventoryIndex);
  $char = &GetPlayerCharacter($player);
  AddSoul($char[0], $player, "PLAY");
  $char[0] = $cardID;
  $char[1] = 2; //When you transformm, You are no longer that hero, therefore you are not dishonored and reset your stats 🐝
  $char[2] = CharacterCounters($cardID);
  $char[3] = 0;
  $char[4] = 0;
  $char[5] = 1;
  $char[6] = 0;
  $char[7] = 0;
  $char[8] = 0;
  $char[9] = CharacterDefaultActiveState($cardID);
  $char[13] = 0;
  AddEvent("HERO_TRANSFORM", $cardID);
  $health = &GetHealth($player);
  $health = DemiHeroHealth($cardID);
  $banish = new Banish($player);
  CurrentEffectIntellectModifier(true); ///When you transformm, You are no longer that hero, therefore your intellect reset 🐝
  switch($cardID)
  {
    case "levia_redeemed":
      for($i=$banish->NumCards() - 1; $i >= 0; --$i) TurnBanishFaceDown($player, $i * BanishPieces());
      break;
    case "levia_redeemed": // 3.0.3a A player may look at any private object they own, or is in a zone that they own, unless the object is in the deck zone.
      $deck = new Deck($player);
      for($i=0; $i<$parameter; ++$i) $deck->BanishTop();
      WriteLog("Banished $parameter cards to your remaining blood debt triggers");
      break;
    default: break;
  }
}

function DemiHeroHealth($cardID)
{
  switch($cardID)
  {
    case "levia_redeemed": return 8;
    case "levia_redeemed": return 13;
    default: return 0;
  }
}

function CallDownLightning()
{
  global $mainPlayer, $CombatChain;
  WriteLog(CardLink("call_down_the_lightning_yellow", "call_down_the_lightning_yellow") . " deals 1 damage");
  if(IsDecisionQueueActive()) {
    PrependDecisionQueue("MZDAMAGE", $mainPlayer, "1,ATTACKHIT," . $CombatChain->CurrentAttack());
    PrependDecisionQueue("PASSPARAMETER", $mainPlayer, "THEIRCHAR-0");
  } else {
    AddDecisionQueue("PASSPARAMETER", $mainPlayer, "THEIRCHAR-0");
    AddDecisionQueue("MZDAMAGE", $mainPlayer, "1,ATTACKHIT," . $CombatChain->CurrentAttack());
  }
}