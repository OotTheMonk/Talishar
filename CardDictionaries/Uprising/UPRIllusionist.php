<?php

  function UPRIllusionistPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer;
    $permanents = &GetPermanents($currentPlayer);
    $targetIndex = explode("-", $target)[1];
    $matID = $permanents[$targetIndex];
    if ($matID == "UPR439" || $matID == "UPR440" || $matID == "UPR441") { //untransform sand cover
      $origMaterial = explode(",", $permanents[$targetIndex+2])[0];
      DestroyPermanent($currentPlayer, $targetIndex);
      $targetIndex = PutPermanentIntoPlay($currentPlayer, $origMaterial);
      $target = "MYPERM-$targetIndex";
    }
    switch($cardID)
    {
      case "silken_form": Transform($currentPlayer, "Ash", "aether_ashwing", target:$target); return "";
      case "invoke_dracona_optimai_red": Transform($currentPlayer, "Ash", "dracona_optimai", target:$target); return "";
      case "invoke_tomeltai_red": Transform($currentPlayer, "Ash", "tomeltai", target:$target); return "";
      case "invoke_dominia_red": Transform($currentPlayer, "Ash", "dominia", target:$target); return "";
      case "invoke_azvolai_red": Transform($currentPlayer, "Ash", "azvolai", target:$target); return "";
      case "invoke_cromai_red": Transform($currentPlayer, "Ash", "cromai", target:$target); return "";
      case "invoke_kyloria_red": Transform($currentPlayer, "Ash", "kyloria", target:$target); return "";
      case "invoke_miragai_red": Transform($currentPlayer, "Ash", "miragai", target:$target); return "";
      case "invoke_nekria_red": Transform($currentPlayer, "Ash", "nekria", target:$target); return "";
      case "invoke_ouvia_red": Transform($currentPlayer, "Ash", "ouvia", target:$target); return "";
      case "invoke_themai_red": Transform($currentPlayer, "Ash", "themai", target:$target); return "";
      case "invoke_vynserakai_red": Transform($currentPlayer, "Ash", "vynserakai", target:$target); return "";
      case "invoke_yendurai_red": Transform($currentPlayer, "Ash", "yendurai", target:$target); return "";
      case "billowing_mirage_red": case "billowing_mirage_yellow": case "billowing_mirage_blue": Transform($currentPlayer, "Ash", "aether_ashwing", true); return "";
      case "sweeping_blow_red": case "sweeping_blow_yellow": case "sweeping_blow_blue":
        PutPermanentIntoPlay($currentPlayer, "ash");
        return "";
      case "rake_the_embers_red": case "rake_the_embers_yellow": case "rake_the_embers_blue":
        PutPermanentIntoPlay($currentPlayer, "ash");
        if($cardID == "rake_the_embers_red") $maxTransform = 3;
        else if($cardID == "rake_the_embers_yellow") $maxTransform = 2;
        else $maxTransform = 1;
        for($i=0; $i<$maxTransform; ++$i) Transform($currentPlayer, "Ash", "aether_ashwing", true, ($i == 0 ? false : true), ($i == 0 ? false : true));
        return "";
      case "sand_cover_red":
        Transform($currentPlayer, "Ash", "UPR439", target:$target);
        return "";
      case "sand_cover_yellow":
        Transform($currentPlayer, "Ash", "UPR440", target:$target);
        return "";
      case "sand_cover_blue":
        Transform($currentPlayer, "Ash", "UPR441", target:$target);
        return "";
      case "skittering_sands_red": case "skittering_sands_yellow": case "skittering_sands_blue":
        Transform($currentPlayer, "Ash", "aether_ashwing", target:$target);
        AddDecisionQueue("MZOP", $currentPlayer, "GETUNIQUEID");
        AddDecisionQueue("ADDLIMITEDCURRENTEFFECT", $currentPlayer, $cardID . ",HAND");
        return "";
      case "ghostly_touch":
        $gtIndex = FindCharacterIndex($currentPlayer, "ghostly_touch");
        $char = &GetPlayerCharacter($currentPlayer);
        $index = PlayAlly("UPR551", $currentPlayer);
        $allies = &GetAllies($currentPlayer);
        $allies[$index+2] = $char[$gtIndex+2];
        AddCurrentTurnEffect($cardID . "-" . $char[$gtIndex+2], $currentPlayer);
        return "";
      case "semblance_blue":
        AddCurrentTurnEffect("semblance_blue", $currentPlayer);
        return "";
      case "transmogrify_red": case "transmogrify_yellow": case "transmogrify_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      default: return "";
    }
  }

  function UPRIllusionistHitEffect($cardID)
  {
    global $mainPlayer, $combatChainState, $CCS_WeaponIndex, $defPlayer;
    switch($cardID)
    {
      case "dustup_red": case "dustup_yellow": case "dustup_blue":
        PutPermanentIntoPlay($mainPlayer, "ash");
        Transform($mainPlayer, "Ash", "aether_ashwing", true);
        break;
      case "kyloria":
        if(IsHeroAttackTarget()) {
          AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS");
          AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to take");
          AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
          AddDecisionQueue("MZOP", $mainPlayer, "GAINCONTROL", 1);
          AddDecisionQueue("ELSE", $mainPlayer, "-");
          AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
        }
        break;
      case "nekria":
        $index = $combatChainState[$CCS_WeaponIndex];
        $allies = &GetAllies($mainPlayer);
        $allies[$index+2] -= 1;
        $allies[$index+7] -= 1;
        PutPermanentIntoPlay($mainPlayer, "ash");
        break;
      case "vynserakai": if(IsHeroAttackTarget()) { DealArcane(3, 0, "ABILITY", $cardID, true, $mainPlayer); } break;
      default: break;
    }
  }

function UPRIllusionistDealDamageEffect($cardID)
{
  global $mainPlayer, $combatChainState, $CCS_WeaponIndex;
  switch ($cardID) {
    case "nekria":
      $index = $combatChainState[$CCS_WeaponIndex];
      $allies = &GetAllies($mainPlayer);
      $allies[$index + 2] -= 1;
      $allies[$index + 7] -= 1;
      PutPermanentIntoPlay($mainPlayer, "ash");
      break;
    default:
      break;
  }
}

  function Transform($player, $materialType, $into, $optional=false, $subsequent=false, $firstTransform=true, $target="")
  {
    if($target != ""){
      $index = explode("-", $target);
      if ($into == "UPR439" || $into == "UPR440" || $into == "UPR441") {
        AddDecisionQueue("PASSPARAMETER", $player, $index[1], 1);
        AddDecisionQueue("TRANSFORMPERMANENT", $player, $into, 1);
      }
      else {
        AddDecisionQueue("PASSPARAMETER", $player, $index[1], 1);
        AddDecisionQueue("TRANSFORM", $player, $into.",".$firstTransform, 1);
      }
    } else if($materialType == "Ash") {
      AddDecisionQueue("FINDINDICES", $player, "PERMSUBTYPE," . $materialType, ($subsequent ? 1 : 0));
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a material to transform into " . CardLink($into, $into), 1);
      if($optional) AddDecisionQueue("MAYCHOOSEPERMANENT", $player, "<-", 1);
      else AddDecisionQueue("CHOOSEPERMANENT", $player, "<-", 1);
      AddDecisionQueue("TRANSFORM", $player, $into.",".$firstTransform, 1);
    } else if($materialType == "spectral_shield") {
      $subType = CardSubType($materialType);
      AddDecisionQueue("FINDINDICES", $player, "spectral_shield",($subsequent ? 1 : 0));
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a " . CardLink($materialType, $materialType) . " to transform into " . CardLink($into, $into), 1);
      AddDecisionQueue("CHOOSEMY" . strtoupper($subType), $player, "<-", 1);
      AddDecisionQueue("TRANSFORM" . strtoupper($subType), $player, $into, 1);
    } else {
      $subType = CardSubType($materialType);
      AddDecisionQueue("FINDINDICES", $player, "PERMSUBTYPE," . $subType, ($subsequent ? 1 : 0));
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a " . CardLink($materialType, $materialType) . " to transform into " . CardLink($into, $into), 1);
      AddDecisionQueue("CHOOSEMY" . strtoupper($subType), $player, "<-", 1);
      AddDecisionQueue("TRANSFORM" . strtoupper($subType), $player, $into, 1);
    }
  }

  function ResolveTransform($player, $materialIndex, $into, $firstTransform=true)
  {
    $materialType = RemovePermanent($player, $materialIndex);
    return PlayAlly($into, $player, $materialType, firstTransform: $firstTransform);
  }

  function ResolveTransformPermanent($player, $materialIndex, $into)
  {
    $materialType = RemovePermanent($player, $materialIndex);
    return PutPermanentIntoPlay($player, $into, subCards: $materialType);
  }

  function ResolveTransformAura($player, $materialIndex, $into)
  {
    $materialType = RemoveAura($player, $materialIndex, -1);
    return PlayAlly($into, $player, $materialType);
  }

  function GhostlyTouchPhantasmDestroy()
  {
    global $mainPlayer;
    $ghostlyTouchIndex = FindCharacterIndex($mainPlayer, "ghostly_touch");
    if($ghostlyTouchIndex > -1) {
      $char = &GetPlayerCharacter($mainPlayer);
      ++$char[$ghostlyTouchIndex+2];
    }
  }

?>
