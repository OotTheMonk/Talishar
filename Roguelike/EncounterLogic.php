<?php

function GetOptions($amount, $upperBound, $lowerBound = 0, $step = 1) //amount needs to be less than both upperbound and the amount of options in the pool being chosen from
{
  $options = [];
  $randomNums = range($lowerBound, $upperBound, $step);
  shuffle($randomNums);
  for($i = 0; $i < $amount; ++$i)
  {
    array_push($options, $randomNums[$i]);
  }
  return $options;
}

function GetBackgrounds($character)
{
  switch($character)
  {
    case "Dorinthea": $backgroundChoices = array("The_Volcai_Sellsword", "The_Lowly_Solanian", "The_Fierce_Warrior", "Spiders_Deserter"); break;
    case "Bravo": $backgroundChoices = array("The_Everfest_Showman", "The_Reclusive_Blacksmith", "The_Slumbering_Giant"); break;
    case "Lexi": $backgroundChoices = array("The_Ancient_Ollin", "The_Exuberant_Adventurer", "The_Hired_Crow", "The_Roadside_Bandit"); break;
    case "Fai": $backgroundChoices = array("The_Rebel_Organizer", "The_Travelling_Duo", "The_Archaeologist"); break;
    case "Arakni": $backgroundChoices = array("The_Emperor", "The_Doctor", "The_Warrior"); break;
  }
  $options = getOptions(2, count($backgroundChoices)-1);
  return $backgroundChoices[$options[0]] . "," . $backgroundChoices[$options[1]];
}

function GetPowers($amount = 3, $rarity = "-", $special = "-")
{
  //$common = array("ROGUE507", "ROGUE508", "ROGUE509", "ROGUE510", "ROGUE511", "ROGUE512", "ROGUE513", "ROGUE516", "ROGUE517");
  //$rare = array("ROGUE501", "ROGUE504", "ROGUE518", "ROGUE519", "ROGUE521", "ROGUE522", "ROGUE523", "ROGUE524", "ROGUE525");
  //$majestic = array("ROGUE502", "ROGUE503", "ROGUE505", "ROGUE506", "ROGUE526", "ROGUE527", "ROGUE528");
  $common = array("ROGUE601", "ROGUE602", "ROGUE603", "ROGUE604", "ROGUE605", "ROGUE606", /*"ROGUE607", */"ROGUE608", "ROGUE609", "ROGUE610", "ROGUE611");
  $rare = array("ROGUE701", "ROGUE702", "ROGUE703", "ROGUE704", "ROGUE705", "ROGUE706", "ROGUE707", "ROGUE708", "ROGUE709", "ROGUE710", "ROGUE711");
  $majestic = array("ROGUE801", "ROGUE802", "ROGUE803", "ROGUE804", "ROGUE805", "ROGUE806", "ROGUE807");
  if($special = "-")
  {
    $options = [];
    $rarityCount = array(0, 0, 0);
    for($i = 0; $i < $amount; ++$i)
    {
      if($rarity == "-")
      {
        $random = rand(1,100); //current rarity numbers make rares appear about 1 in every 3 rewards and majestics appear about 1 in every 10 rewards. Feel free to change in testing.
        if($random >= 95) ++$rarityCount[2]; //MAKE SURE THIS IS 95 WHEN PUSHED
        else if($random >= 75) ++$rarityCount[1]; //MAKE SURE THIS IS 75 WHEN PUSHED
        else ++$rarityCount[0];
      }
      else
      {
        switch($rarity)
        {
          case "Common": ++$rarityCount[0]; break;
          case "Rare": ++$rarityCount[1]; break;
          case "Majestic": ++$rarityCount[2]; break;
        }
      }
    }
    if($rarityCount[0] > 0)
    {
      $randomNums = getOptions($rarityCount[0], count($common)-1);
      for($i = 0; $i < $rarityCount[0]; ++$i)
      {
        array_push($options, $common[$randomNums[$i]]);
      }
    }
    if($rarityCount[1] > 0)
    {
      $randomNums = getOptions($rarityCount[1], count($rare)-1);
      for($i = 0; $i < $rarityCount[1]; ++$i)
      {
        array_push($options, $rare[$randomNums[$i]]);
      }
    }
    if($rarityCount[2] > 0)
    {
      $randomNums = getOptions($rarityCount[2], count($majestic)-1);
      for($i = 0; $i < $rarityCount[2]; ++$i)
      {
        array_push($options, $majestic[$randomNums[$i]]);
      }
    }
    $result = "";
    for($i = 0; $i < count($options); ++$i)
    {
      if($i != 0) $result.=",";
      $result.=$options[$i];
    }
    return $result;
  }
}

//function GetRandomCards($number = 4, $special = "-", $specialType = "-")
function GetRandomCards($inputString)
{
  $parameters = explode(",", $inputString);
  switch($parameters[0])
  {
    case "Reward":
      $rarity = "-";
      $specialTags = "-";
      for($i = 2; $i < count($parameters); ++$i)
      {
        $tags = explode("-", $parameters[$i]);
        switch($tags[0])
        {
          case "ForcedRarity":
            $rarity = $tags[1];
            break;
          case "SpecialTag":
            $specialTags = $tags[1];
            break;
        }
      }
      $tags = explode("-", $parameters[1]);
      $result = [];
      for($i = 0; $i < count($tags); ++$i)
      {
        $pool = GeneratePool($result, $tags[$i], $rarity, $specialTags);
        array_push($result, $pool[rand(0, count($pool)-1)]);
      }
      $resultStr = "";
      for($i = 0; $i < count($result); ++$i)
      {
        if($i != 0) $resultStr.=",";
        $resultStr.=$result[$i];
      }
      return $resultStr;
    case "Deck":
      return GetRandomDeckCard(1, $parameters[1]);
    case "Power":
      array_push($parameters, "-");
      return GetPowers($parameters[1], $parameters[2]);
    case "Equipment":
      array_push($parameters, "");
      array_push($parameters, "");
      $encounter = &GetZone(1, "Encounter");
      $result = [];
      $pool = GetPool("Equipment", $encounter->hero, $parameters[1], $encounter->background, "All", $parameters[2]);
      array_push($result, $pool[rand(0, count($pool) - 1)]);
      return $result[0];
    case "ResourceGems":
      $poolResources = array("heart_of_fyendal_blue", "eye_of_ophidia_blue", "grandeur_of_valahai_blue", "arknight_shard_blue", "blood_of_the_dracai_red", "titanium_bauble_blue", "cracked_bauble_yellow", "cracked_bauble_yellow", "cracked_bauble_yellow", "cracked_bauble_yellow", "cracked_bauble_yellow"); //5 cracked baubles to weight them as more likely to occur. 11 options equally likely
      return $poolResources[rand(0, count($poolResources) - 1)];
  }
}

function GetRandomDeckCard($player, $special = "") //TODO add in a seperate special call to remove random cards instead of any card and a special call to remove powers.
{

  $deck = &GetZone($player, "Deck");
  $fullList = "";
  for($i = 0; $i < count($deck); ++$i)
  {
    if(CardSubtype($deck[$i]) != "Power")
    {
      //WriteLog(CardSubtype($deck[$i]));
      if($i != 0) $fullList .= ",";
      $fullList .= $deck[$i];
    }
  }

  if ($special == "") {
    $special = "ALL"; //This is the default mode
  }
  if ($special == "ALL") return $fullList; // By default, this is all we need
  elseif($special == 4) {
    //WriteLog($fullList);
    $deckNoPowers = explode(",", $fullList);
    $options = GetOptions(4, 0, count($deckNoPowers) - 1, 1); //If empty cards keep showing up, maybe get rid of the '- 1' in front of count(#deckNoPowers)
    $return = "";
    for($i = 0; $i < count($options); $i++){
      if($i != 0) $return .= ",";
      $return .= $deckNoPowers[$options[$i]];
    }
    return $return;
  }
  WriteLog("Function GetRandomDeckCard failed to find a case");
  return "This should never happen";
}

function GeneratePool($selected, $type, $rarity = "-", $specialTags = "-")
{
  $encounter = &GetZone(1, "Encounter");
  if($rarity == "-" && $type != "Equipment")
  {
    $randRarity = rand(1,100);
    if($randRarity <= $encounter->majesticCard)
    {
      $encounter->majesticCard = 1;
      $rarity = "Majestic";
    }
    else if($randRarity >= 75)
    {
      $encounter->majesticCard += 3;
      $rarity = "Rare";
    }
    else
    {
      $encounter->majesticCard +=1;
      $rarity = "Common";
    }
  }
  if($specialTags == "-") $pool = GetPool($type, $encounter->hero, $rarity, $encounter->background);
  else if($specialTags == "AnyPool") $pool = GetPool($type, "ALL", $rarity, $encounter->background);
  $generatedPool = [];

  /*$options = GetOptions($selected, count($pool));
  for($i = 0; $i < count($options); $i++){
    $generatedPool[$i] = $pool[$i];
  }
  return $generatedPool;*/

  for($i = 0; $i < count($pool); ++$i)
  {
    $found = false;
    for($j = 0; $j < count($selected); ++$j)
    {
      if($selected[$j] == $pool[$i]) $found = true;
    }
    if(!$found) array_push($generatedPool, $pool[$i]);
  }
  return $generatedPool;
}

function GetShop($inputString = "Class,Class,Talent,Equipment-Common,Equipment,Generic,Generic,Power-1")
{
  if($inputString == "-")
  {
    $inputString = "Class,Class,Talent,Equipment-Common,Equipment,Generic,Generic,Power-1";
  }
  $result = [];

  $input = explode(",", $inputString);
  for($i = 0; $i < count($input); ++$i)
  {
    $params = explode("-", $input[$i]);
    array_push($params, "-");
    array_push($params, "-");
    if($params[0] == "Power")
    {
      array_push($result, GetPowers($params[1]));
    }
    else
    {
      $pool = GeneratePool($result, $params[0], $params[1], $params[2]);
      array_push($result, $pool[rand(0, count($pool)-1)]);
    }
  }
  $resultStr = "";
  for($i = 0; $i < count($result); ++$i)
  {
    if($i != 0) $resultStr.=",";
    $resultStr.=$result[$i];
  }
  return $resultStr;
}

function GetShopCost($cardID)
{
  if($cardID == "CardBack") return 0;
  $cost = 0;
  switch(Rarity($cardID))
  {
    case "C": case "T": $cost = 2; break;
    case "R": $cost = 4; break;
    case "S": case "M": $cost = 6; break;
  }
  if(CardSubtype($cardID) == "Power") $cost += 4;
  if(CardType($cardID) == "E"){
    if(Rarity($cardID) == "L") $cost = 12;
    else $cost = 4;
  }
  $encounter = &GetZone(1, "Encounter");
  if($encounter->encounterID == 211) $cost = $cost / 2;
  if($encounter->encounterID == 213) $cost -= 2;
  if($encounter->encounterID == 230) $cost -= 1;
  return $cost;
}

function WriteFullEncounter() {
  $encounter = &GetZone(1, "Encounter");
  WriteLog("===============================");
  WriteLog("encounterID->" . $encounter->encounterID);
  WriteLog("subphase->" . $encounter->subphase);
  WriteLog("position->" . $encounter->position);
  WriteLog("hero->" . $encounter->hero);
  WriteLog("adventure->" . $encounter->adventure);
  WriteLog("visited->[" . implode(", ", $encounter->visited) . "]");
  WriteLog("majesticCard->" . $encounter->majesticCard);
  WriteLog("background->" . $encounter->background);
  WriteLog("difficulty->" . $encounter->difficulty);
  WriteLog("gold->" . $encounter->gold);
  WriteLog("rerolls->" . $encounter->rerolls);
  WriteLog("costToHeal->" . $encounter->costToHeal);
  WriteLog("costToRemove->" . $encounter->costToRemove);
  WriteLog("===============================");
}
?>
