<?php


function ARCGenericPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer, $CS_NextNAACardGoAgain, $CS_ArcaneDamagePrevention, $CombatChain;
  $rv = "";
  switch($cardID) {
    case "talismanic_lens":
      PlayerOpt($currentPlayer, 2);
      return "";
    case "bracers_of_belief":
      $deck = new Deck($currentPlayer);
      if($deck->Reveal()) {
        $bonus = 3 - PitchValue($deck->Top());
        if($bonus > 0) AddCurrentTurnEffect($cardID . "-" . $bonus, $currentPlayer);
      }
      return $rv;
    case "mage_master_boots":
      SetClassState($currentPlayer, $CS_NextNAACardGoAgain, 1);
      return "";
    case "art_of_war_yellow":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "ARTOFWAR", 1);
      return "";
    case "chains_of_eminence_red":
      AddDecisionQueue("INPUTCARDNAME", $currentPlayer, "-");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("WRITELOG", $currentPlayer, "📣<b>{0}</b> was chosen");
      AddDecisionQueue("ADDCURRENTANDNEXTTURNEFFECT", ($currentPlayer == 1 ? 2 : 1), "chains_of_eminence_red,{0}");
      return "";
    case "life_for_a_life_red": case "life_for_a_life_yellow": case "life_for_a_life_blue":
      if(PlayerHasLessHealth($currentPlayer)) GiveAttackGoAgain();
      return "";
    case "plunder_run_red": case "plunder_run_yellow": case "plunder_run_blue":
      AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
      if($from == "ARS") {
        AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
        $rv = "Played from arsenal: Gives your next attack action card +" . EffectAttackModifier($cardID . "-2");
      }
      return $rv;
    case "eirinas_prayer_red": case "eirinas_prayer_yellow": case "eirinas_prayer_blue":
      if($cardID == "eirinas_prayer_red") $prevent = 6;
      else if($cardID == "eirinas_prayer_yellow") $prevent = 5;
      else $prevent = 4;
      $deck = new Deck($currentPlayer);
      if($deck->Reveal(1)) {
        $prevent -= PitchValue($deck->Top());
      }
      IncrementClassState($currentPlayer, $CS_ArcaneDamagePrevention, $prevent);
      return "Eirina's Prayer reduces your next arcane damage by " . $prevent;
    case "fervent_forerunner_red": case "fervent_forerunner_yellow": case "fervent_forerunner_blue":
      if($from == "ARS") GiveAttackGoAgain();
      return "";
    case "ravenous_rabble_red": case "ravenous_rabble_yellow": case "ravenous_rabble_blue":
      $deck = new Deck($currentPlayer);
      if($deck->Empty()) return "Ravenous Rabble does not get negative attack because your deck is empty";
      if($deck->Reveal(1)) {
        $top = $deck->Top();
        $pitch = PitchValue($top);
        $CombatChain->AttackCard()->ModifyPower(-$pitch);
        return "Reveals " . CardLink($top, $top) . " and gets -" . $pitch . " attack";
      }
      return "Ravenous Rabble does not get negative attack because the reveal was prevented";
    case "fate_foreseen_red": case "fate_foreseen_yellow": case "fate_foreseen_blue":
      PlayerOpt($currentPlayer, 1);
      return "";
    case "come_to_fight_red": case "come_to_fight_yellow": case "come_to_fight_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "force_sight_red": case "force_sight_yellow": case "force_sight_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if($from == "ARS") {
        PlayerOpt($currentPlayer, 2);
        $rv = "Was played from arsenal and lets you Opt 2";
      }
      return $rv;
    case "lead_the_charge_red": case "lead_the_charge_yellow": case "lead_the_charge_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if($cardID == "lead_the_charge_red") $cost = 0;
      else if($cardID == "lead_the_charge_yellow") $cost = 1;
      else $cost = 2;
      return "";
    case "sun_kiss_red": case "sun_kiss_yellow": case "sun_kiss_blue":
      if($cardID == "sun_kiss_red") $health = 3;
      else if($cardID == "sun_kiss_yellow") $health = 2;
      else $health = 1;
      GainHealth($health, $currentPlayer);
      if(SearchCurrentTurnEffects("moon_wish_red-GA", $currentPlayer)) Draw($currentPlayer);
      return "";
    case "whisper_of_the_oracle_red": case "whisper_of_the_oracle_yellow": case "whisper_of_the_oracle_blue":
      if($cardID == "whisper_of_the_oracle_red") $opt = 4;
      else if($cardID == "whisper_of_the_oracle_yellow") $opt = 3;
      else $opt = 2;
      PlayerOpt($currentPlayer, $opt);
      return "";
    default: return "";
  }
}

function ARCGenericHitEffect($cardID)
{
  global $mainPlayer, $CS_NextNAAInstant, $defPlayer;
  switch($cardID) {
    case "command_and_conquer_red":
      if(IsHeroAttackTarget()) DestroyArsenal($defPlayer, effectController:$mainPlayer);      
      break;
    case "life_for_a_life_red": case "life_for_a_life_yellow": case "life_for_a_life_blue":
      GainHealth(1, $mainPlayer);
      break;
    case "pursuit_of_knowledge_blue":
      AddCurrentTurnEffect($cardID, $mainPlayer);
      break;
    case "cadaverous_contraband_red": case "cadaverous_contraband_yellow": case "cadaverous_contraband_blue":
      MZMoveCard($mainPlayer, "MYDISCARD:type=A", "MYTOPDECK", may:true);
      break;
    case "fervent_forerunner_red": case "fervent_forerunner_yellow":  case "fervent_forerunner_blue":
      PlayerOpt($mainPlayer, 2);
      break;
    case "moon_wish_red": case "moon_wish_yellow": case "moon_wish_blue":
      MZMoveCard($mainPlayer, "MYDECK:isSameName=sun_kiss_red", "MYHAND", may:true, isReveal:true);
      AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-");
      break;
    case "rifting_red": case "rifting_yellow": case "rifting_blue":
      SetClassState($mainPlayer, $CS_NextNAAInstant, 1);
      break;
    default: break;
  }
  return "";
}
