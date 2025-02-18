<?php

function ProcessMacros()
{
  global $currentPlayer, $turn, $actionPoints, $mainPlayer, $layers, $decisionQueue, $numPass, $CS_SkipAllRunechants, $defPlayer;
  $somethingChanged = true;
  $lastPhase = $turn[0];
  for ($i = 0; $i < $numPass; ++$i) {
    PassInput();
  }

  if (!IsGameOver()) {
    for ($i = 0; $i < 10 && $somethingChanged; ++$i) {
      if ($lastPhase != $turn[0]) $i = 0;
      $lastPhase = $turn[0];
      $somethingChanged = false;
      if($turn[0] == "A" && ShouldSkipARs($currentPlayer)) { $somethingChanged = true; PassInput(); }
      else if($turn[0] == "D" && ShouldSkipDRs($currentPlayer)) { $somethingChanged = true; PassInput(); }
      else if(($turn[0] == "B") && IsAllyAttackTarget()) { $somethingChanged = true; PassInput(); }
      else if($turn[0] == "CHOOSECARDID" && strlen($turn[2]) <= 6) { $somethingChanged = true; ContinueDecisionQueue($turn[2]); }
      else if($turn[0] == "CHOOSECARD" && strlen($turn[2]) <= 6) { $somethingChanged = true; ContinueDecisionQueue($turn[2]); }
      else if($turn[0] == "CHOOSETHEIRHAND" && strlen($turn[2]) <= 1) { $somethingChanged = true; ContinueDecisionQueue($turn[2]); }
      else if($turn[0] == "CHOOSETHEIRCHARACTER" && strlen($turn[2]) <= 2) { $somethingChanged = true; ContinueDecisionQueue($turn[2]); }
      else if($turn[0] == "CHOOSETOPOPPONENT" && strlen($turn[2]) <= 6) { $somethingChanged = true; ProcessInput($currentPlayer, 29, $turn[2], $turn[2], 0, ""); }
      else if((count($decisionQueue) == 0 || $decisionQueue[0] == "INSTANT") && count($layers) > 0 && $layers[count($layers)-LayerPieces()] == "ENDPHASE" && count($layers) < (LayerPieces() * 3)) { $somethingChanged = true; PassInput(); }
      else if($turn[0] == "INSTANT" || ($turn[0] == "M" && ($actionPoints == 0 || $currentPlayer != $mainPlayer)))
      {
        if(HoldPrioritySetting($currentPlayer) == 0 && !HasPlayableCard($currentPlayer, $turn[0]))
        {
          $somethingChanged = true;
          PassInput();
        }
        if($turn[0] == "INSTANT" && count($layers) > 0)
        {
          if($layers[0] == "FINALIZECHAINLINK" && HoldPrioritySetting($currentPlayer) != "1" && !HasPlayableCard($currentPlayer, $turn[0])) { $somethingChanged = true; PassInput(); }
          else if($layers[0] == "DEFENDSTEP" && HoldPrioritySetting($currentPlayer) != "1") { $somethingChanged = true; PassInput(); }
          else if($layers[0] == "ATTACKSTEP" && HoldPrioritySetting($currentPlayer) != "1") { $somethingChanged = true; PassInput(); }
          else if($layers[5] != "-")//Means there is a unique ID
          {
            $subtype = CardSubType($layers[2]);
            if(DelimStringContains($subtype, "Aura") && GetAuraGemState($layers[1], $layers[2]) == 0 && HoldPrioritySetting($currentPlayer) != "1") { $somethingChanged = true; PassInput(); }
            else if(DelimStringContains($subtype, "Item") && GetItemGemState($layers[1], $layers[2]) == 0 && HoldPrioritySetting($currentPlayer) != "1") { $somethingChanged = true; PassInput(); }
            else if($layers[2] == "levia_redeemed" && GetCharacterGemState($layers[1], $layers[2]) == 0 && HoldPrioritySetting($currentPlayer) != "1") { $somethingChanged = true; PassInput(); }
          }
        }
      }
      else if(AutopassPhaseWithOneOption($turn[0]) && SearchCount($turn[2]) == 1) { $somethingChanged = true; ContinueDecisionQueue($turn[2]); }
      if($turn[0] == "B" || $turn[0] == "D")
      {
        $threshold = ShortcutAttackThreshold($currentPlayer);
        if($threshold == "99") { $somethingChanged = true; PassInput(); }
        else if($threshold == "1")
        {
          CacheCombatResult();
          if(CachedTotalAttack() <= 1) { $somethingChanged = true; PassInput(); }
        }
      }
      if(!IsGameOver() && ($turn[0] == "CHOOSEMULTIZONE" || $turn[0] == "MAYCHOOSEMULTIZONE") && GetClassState($currentPlayer, $CS_SkipAllRunechants) == 1) { 
        $somethingChanged = true; 
        SetClassState($currentPlayer, $CS_SkipAllRunechants, 0); 
      }
      else if (!IsGameOver() && isset($layers[2]) && $layers[2] == "runechant" && GetClassState($currentPlayer, $CS_SkipAllRunechants) == 1) { 
        $somethingChanged = true; 
        ContinueDecisionQueue("0"); 
      }
      else if (GetClassState($currentPlayer, $CS_SkipAllRunechants) == 1) { 
        SetClassState($currentPlayer, $CS_SkipAllRunechants, 0); 
      }
    }
  }
}

function AutopassPhaseWithOneOption($phase)
{
  switch ($phase) {
    case "BUTTONINPUT":
    case "CHOOSEMULTIZONE":
    case "CHOOSECHARACTER":
    case "CHOOSECOMBATCHAIN":
    case "CHOOSEARCANE":
    case "CHOOSEARSENAL":
    case "CHOOSEHAND":
    case "CHOOSEPERMANENT":
    case "CHOOSEMYAURA":
      return true;
    default:
      return false;
  }
}

function HasPlayableCard($player, $phase)
{
  global $CombatChain;
  $restriction = "";
  $character = &GetPlayerCharacter($player);
  $otherPlayer = $player == 1 ? 2 : 1;
  for($i=0; $i<count($character); $i+=CharacterPieces()) {
    if($character[$i+1] == 2 && IsPlayable($character[$i], $phase, "CHAR", $i, $restriction, $player)) return true;
  }
  $hand = &GetHand($player);
  for($i=0; $i<count($hand); $i+=HandPieces()) {
    if(IsPlayable($hand[$i], $phase, "HAND", $i, $restriction, $player)) return true;
  }
  for ($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    if(IsPlayable($CombatChain->Card($i, cardNumber:true)->ID(), $phase, "CC", $i, $restriction, $player)) return true;
  }
  $arsenal = &GetArsenal($player);
  for($i=0; $i<count($arsenal); $i+=ArsenalPieces()) {
    if(IsPlayable($arsenal[$i], $phase, "ARS", $i, $restriction, $player)) return true;
  }
  $items = &GetItems($player);
  for($i=0; $i<count($items); $i+=ItemPieces()) {
    if(IsPlayable($items[$i], $phase, "PLAY", $i, $restriction, $player)) return true;
  }
  $banish = &GetBanish($player);
  for($i=0; $i<count($banish); $i+=BanishPieces()) {
    if(IsPlayable($banish[$i], $phase, "BANISH", $i, $restriction, $player)) return true;
  }
  $theirBanish = &GetBanish($otherPlayer);
  for($i=0; $i<count($theirBanish); $i+=BanishPieces()) {
    if(IsPlayable($theirBanish[$i], $phase, "THEIRBANISH", $i, $restriction, $player)) return true;
  }
  $auras = &GetItems($player);
  for($i=0; $i<count($auras); $i+=AuraPieces()) {
    if(IsPlayable($auras[$i], $phase, "PLAY", $i, $restriction, $player)) return true;
  }
  $character = GetPlayerCharacter($player);
  if ($character[0] == "dash_io" || $character[0] == "dash_database") {
    $deck = &GetDeck($player);
    if(count($deck) > 0 && $character[1] == 2) {
      if(IsPlayable($deck[0], $phase, "DECK", 0)) return true;
    }
  }
  return false;
}

function PlayerMacrosCardPlayed()
{
  global $turn, $currentPlayer, $SET_PassDRStep;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  if($turn[0] == "A")
  {
    ChangeSetting($otherPlayer, $SET_PassDRStep, 0);
  }
}

?>
