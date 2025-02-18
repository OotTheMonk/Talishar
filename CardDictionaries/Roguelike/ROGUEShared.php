<?php


//ROGUE5XX values are depreciated. Do not use them. They may not work properly.
function ROGUEAbilityCost($cardID)
{
    switch ($cardID) {
      case "ROGUE002": return 0;
      case "ROGUE005": return 0;
      case "ROGUE007": return 0;
      case "ROGUE032": return 0;
      default: return 0;
    }
}

function ROGUEAbilityType($cardID, $index = -1)
{
    switch ($cardID) {
      case "ROGUE002": return "AA";
      case "ROGUE005": return "AA";
      case "ROGUE007": return "A";
      case "ROGUE032": return "AA";
      default: return "";
    }
}

// Natural go again or ability go again. Attacks that gain go again should be in CoreLogic (due to hypothermia)
function ROGUEHasGoAgain($cardID)
{
    switch ($cardID) {
      default: return false;
    }
}

function ROGUEAbilityHasGoAgain($cardID)
{
    switch ($cardID) {
      case "ROGUE007": return true;
      default: return false;
    }
}

function ROGUEEffectAttackModifier($cardID)
{
    global $combatChain, $combatChainState, $CCS_LinkBaseAttack;
    $params = explode(",", $cardID);
    $cardID = $params[0];
    if (count($params) > 1) $parameter = $params[1];
    switch ($cardID) {
      case "ROGUE008": return 1;
      case "ROGUE018": return 1;
      case "ROGUE506": return 1;
      case "ROGUE509": return 1;
      case "ROGUE517":
        global $currentPlayer;
        $health = &GetHealth($currentPlayer);
        if($health <=2) return 2;
        else if($health <=5) return 1;
        else return 0;
      case "ROGUE521":
      case "ROGUE521-NA": return 1;
      case "ROGUE523": return -2;

      case "ROGUE601": return 1;
      case "ROGUE603": return 1;
      case "ROGUE605-first": return 2;
      case "ROGUE605-second": return -2;
      case "ROGUE607": return 1;
      case "ROGUE612": return 1;
      case "ROGUE613": return 2;
      case "ROGUE614": return 3;
      case "ROGUE615": return 4;
      case "ROGUE616": return 5;
      case "ROGUE702": case "ROGUE702-NA": return 2;
      case "ROGUE704":
        global $mainPlayer;
        $banish = &GetBanish($mainPlayer);
        $rv = -1;
        for($i = 0; $i < count($banish); $i += BanishPieces())
        {
          if(CardName($banish[$i]) == CardName($combatChain[0])) ++$rv;
        }
        return $rv;
      case "ROGUE707":
        $rv = 0;
        if(HasEphemeral($combatChain[0])) ++$rv;
        if(DoesAttackHaveGoAgain()) ++$rv;
        if(IsDominateActive()) ++$rv;
        if(isOverpowerActive()) ++$rv;
        if(HasRupture($combatChain[0])) ++$rv;
        if(HasCombo($combatChain[0])) ++$rv;
        if(HasFusion($combatChain[0]) != "") ++$rv;
        if(HasCrush($combatChain[0])) ++$rv;
        if(IsPhantasmActive()) ++$rv;
        if(HasStealth($combatChain[0])) ++$rv;
        if(ContractType($combatChain[0]) != "") ++$rv;
        return $rv;
      case "ROGUE709": return -2;
      case "ROGUE711":
        global $mainPlayer;
        $pitch = &GetPitch($mainPlayer);
        $rv = 0;
        for($i = 0; $i < count($pitch); $i += PitchPieces())
        {
          ++$rv;
        }
        $rv = (int) ($rv / 2);
        return $rv * 2;
      case "ROGUE802": return 1;
      case "ROGUE805":
        global $mainPlayer;
        $grave = &GetDiscard($mainPlayer);
        $rv = 0;
        for($i = 0; $i < count($grave); $i += DiscardPieces())
        {
          if(CardType($grave[$i]) == "E") ++$rv;
        }
        return $rv;
      case "ROGUE806": return 5;
      default: return 0;
    }
}

function ROGUECombatEffectActive($cardID, $attackID)
{
    global $currentPlayer, $CS_NumAttacks;
    $params = explode(",", $cardID);
    $cardID = $params[0];
    switch ($cardID) {
        case "ROGUE008": return true;
        case "ROGUE018": return true;
        case "ROGUE506": return CardType($attackID) == "AA";
        case "ROGUE509": return $attackID == "crouching_tiger";
        case "ROGUE512": return CardType($attackID) == "AA" && CardCost($attackID) <= 1;
        case "ROGUE513": return CardType($attackID) == "AA" && CardCost($attackID) >= 2;
        case "ROGUE517": return true;
        case "ROGUE521": return PitchValue($attackID) == 2;
        case "ROGUE521-NA": return true;
        case "ROGUE522": return true;
        case "ROGUE523": return true;

        case "ROGUE601": return true;
        case "ROGUE603": return $attackID == "crouching_tiger";
        case "ROGUE605-first": return true;
        case "ROGUE605-second": return GetClassState($currentPlayer, $CS_NumAttacks) == 2;
        case "ROGUE607": return true;
        case "ROGUE612": case "ROGUE613": case "ROGUE614": case "ROGUE615": case "ROGUE616": return true;
        case "ROGUE702": return PitchValue($attackID) == 2;
        case "ROGUE702-NA": return true;
        case "ROGUE704": return CardType($attackID) == "AA";
        case "ROGUE707": return true;
        case "ROGUE709": return true;
        case "ROGUE710-GA": return CardType($attackID) == "AA" && CardCost($attackID) <= 1;
        case "ROGUE710-DO": return CardType($attackID) == "AA" && CardCost($attackID) >= 2;
        case "ROGUE711": return true;
        case "ROGUE802": return CardType($attackID) == "AA";
        case "ROGUE805": return true;
        case "ROGUE806": return true;
        default:
            return false;
    }
}


function ROGUECardTalent($cardID) // TODO
{
  $number = intval(substr($cardID, 3));
  if($number <= 0) return "";
//   else if($number >= 3 && $number <= 124) return "";
//   else if($number >= 125 && $number <= 150) return "";
//   else if($number >= 406 && $number <= 417 ) return "";
//   else if($number >= 439 && $number <= 441) return "";
  else return "NONE";
}

function ROGUECardType($cardID)
{
    switch ($cardID) {
      case "ROGUE001": return "C";
      case "ROGUE002": return "W";
      case "ROGUE003": return "C";
      case "ROGUE004": return "C";
      case "ROGUE005": return "W";
      case "ROGUE006": return "C";
      case "ROGUE007": return "E";
      case "ROGUE008": return "C";
      case "ROGUE009": return "C";
      case "ROGUE010": return "C";
      case "ROGUE013": return "C";
      case "ROGUE014": return "C";
      case "ROGUE015": return "C";
      case "ROGUE016": return "C";
      case "ROGUE017": return "C";
      case "ROGUE018": return "C";
      case "ROGUE019": return "C";
      case "ROGUE020": return "C";
      case "ROGUE021": return "C";
      case "ROGUE022": return "C";
      case "ROGUE023": return "C";
      case "ROGUE024": return "C";
      case "ROGUE025": return "C";
      case "ROGUE026": return "C";
      case "ROGUE027": return "C";
      case "ROGUE028": return "C";
      case "ROGUE029": return "C";
      case "ROGUE030": return "C";
      case "ROGUE031": return "C";
      case "ROGUE032": return "W";

      case "ROGUE501": case "ROGUE502": case "ROGUE503": case "ROGUE504": case "ROGUE505": case "ROGUE506": case "ROGUE507": case "ROGUE508": case "ROGUE509": case "ROGUE510": case "ROGUE511": case "ROGUE512": case "ROGUE513": case "ROGUE514":
      case "ROGUE515": case "ROGUE516": case "ROGUE517": case "ROGUE518": case "ROGUE519": case "ROGUE520": case "ROGUE521": case "ROGUE522": case "ROGUE523": case "ROGUE524": case "ROGUE525": case "ROGUE526": case "ROGUE527": case "ROGUE528":
      return "A";

      case "ROGUE601": case "ROGUE602": case "ROGUE603": case "ROGUE604": case "ROGUE605":
      case "ROGUE606": case "ROGUE607": case "ROGUE608": case "ROGUE609": case "ROGUE610":
      case "ROGUE611": case "ROGUE612": case "ROGUE613": case "ROGUE614": case "ROGUE615":
      case "ROGUE616":
        return "A";
      case "ROGUE701": case "ROGUE702": case "ROGUE703": case "ROGUE704": case "ROGUE705":
      case "ROGUE706": case "ROGUE707": case "ROGUE708": case "ROGUE709": case "ROGUE710":
      case "ROGUE711":
        return "A";
      case "ROGUE801": case "ROGUE802": case "ROGUE803": case "ROGUE804": case "ROGUE805":
      case "ROGUE806": case "ROGUE807":
        return "A";
      default:
        return "";
    }
}

function ROGUECardSubtype($cardID)
{
    switch ($cardID) {
      case "ROGUE001": return "Hog";
      case "ROGUE002": return "Natural";
      case "ROGUE003": return "Monster";
      case "ROGUE004": return "Bear";
      case "ROGUE005": return "Natural";
      case "ROGUE006": return "Elemental";
      case "ROGUE007": return "Chest";
      case "ROGUE008": return "Ninja";
      case "ROGUE009": return "Ranger";
      case "ROGUE013": return "Ninja";
      case "ROGUE014": return "Assassin";
      case "ROGUE015": return "Ninja";
      case "ROGUE016": return "Ranger";
      case "ROGUE017": return "Ninja";
      case "ROGUE018": return "Elemental";
      case "ROGUE019": return "Ninja";
      case "ROGUE020": return "Mechanologist";
      case "ROGUE021": return "Brute";
      case "ROGUE022": return "Runeblade";
      case "ROGUE023": return "Guardian";
      case "ROGUE024": return "Ranger";
      case "ROGUE025": return "Brute";
      case "ROGUE026": return "Merchant";
      case "ROGUE027": return "Warrior";
      case "ROGUE028": return "Illusionist";
      case "ROGUE029": return "Warrior";
      case "ROGUE030": return "Thug";
      case "ROGUE031": return "Elemental";
      case "ROGUE032": return "Elemental";

      case "ROGUE501": //Broken Hourglass
      case "ROGUE502": //Perfect Mirror
      case "ROGUE503": //Scroll of Mastery
      case "ROGUE504": //Blacksmith's Tongs
      case "ROGUE505": //Teklo's Cranium
      case "ROGUE506": //Teachings of War
      case "ROGUE507": //Merchant's Handbag
      case "ROGUE508": //Shattered Mirror
      case "ROGUE509": //Qi Scroll
      case "ROGUE510": //Survival Kit
      case "ROGUE511": //Magnifying Glass
      case "ROGUE512": //Dagger of the Meek
      case "ROGUE513": //Hammer of the Mighty
      case "ROGUE514": //Teklo Power Generator (not implemented)
      case "ROGUE515": //Lucky Tooth (not implemented)
      case "ROGUE516": //Mark of Undeath
      case "ROGUE517": //Shamans Skullbone
      case "ROGUE518": //Wild Mirror
      case "ROGUE519": //Shard of the Mountain
      case "ROGUE520": //Blacksmith's Hammer (not implemented) (Quesioning how possible this is to implement, gonna ignore for now)
      case "ROGUE521": //Solanian Bell
      case "ROGUE522": //Thieves Tools
      case "ROGUE523": //Ward of Protection
      case "ROGUE524": //Gorganian Cipher
      case "ROGUE525": //Acorn of Korshem
      case "ROGUE526": //Blacktek Timebomb
      case "ROGUE527": //Merchant Supply Cart
      case "ROGUE528": //Sutcliffe's Tome
        return "Power";
      case "ROGUE601":
      case "ROGUE602": //Ollin's Hammer
      case "ROGUE603":
      case "ROGUE604":
      case "ROGUE605":
      case "ROGUE606":
      case "ROGUE607":
      case "ROGUE608": //Crown of Blessings
      case "ROGUE609":
      case "ROGUE610":
      case "ROGUE611": case "ROGUE612": case "ROGUE613": case "ROGUE614": case "ROGUE615": case "ROGUE616":
        return "Power";
      case "ROGUE701":
      case "ROGUE702":
      case "ROGUE703": //Broken Hourglass
      case "ROGUE704":
      case "ROGUE705":
      case "ROGUE706":
      case "ROGUE707":
      case "ROGUE708":
      case "ROGUE709":
      case "ROGUE710":
      case "ROGUE711":
        return "Power";
      case "ROGUE801":
      case "ROGUE802":
      case "ROGUE803":
      case "ROGUE804":
      case "ROGUE805":
      case "ROGUE806":
      case "ROGUE807":
        return "Power";
      default: return "";
    }
}

function ROGUEName($cardID)
{
    switch ($cardID) {
      case "ROGUE002": return "Tusk";
      case "ROGUE501": return "Broken Hourglass";
      case "ROGUE502": return "Perfect Mirror";
      case "ROGUE503": return "Scroll of Mastery";
      case "ROGUE504": return "Blacksmith's Tongs";
      case "ROGUE505": return "Teklo's Cranium";
      case "ROGUE506": return "Teachings of War";
      case "ROGUE507": return "Merchant's Handbag";
      case "ROGUE508": return "Shattered Mirror";
      case "ROGUE509": return "Qi Scroll";
      case "ROGUE510": return "Survival Kit";
      case "ROGUE511": return "Magnifying Glass";
      case "ROGUE512": return "Dagger of the Meek";
      case "ROGUE513": return "Hammer of the Mighty";
      case "ROGUE514": return "Teklo Power Generator"; //(not implemented)
      case "ROGUE515": return "Lucky Tooth"; //(not implemented)
      case "ROGUE516": return "Mark of Undeath";
      case "ROGUE517": return "Shamans Skullbone";
      case "ROGUE518": return "Wild Mirror";
      case "ROGUE519": return "Shard of the Mountain";
      case "ROGUE520": return "Blacksmith's Hammer"; //(not implemented) (Quesioning how possible this is to implement, gonna ignore for now)
      case "ROGUE521": return "Solanian Bell";
      case "ROGUE522": return "Thieves Tools";
      case "ROGUE523": return "Ward of Protection";
      case "ROGUE524": return "Gorganian Cipher";
      case "ROGUE525": return "Acorn of Korshem";
      case "ROGUE526": return "Blacktek Timebomb";
      case "ROGUE527": return "Merchant Supply Cart";
      case "ROGUE528": return "Sutcliffe's Tome";

      case "ROGUE601": return "Earthlore Whetstone";
      case "ROGUE602": return "Ollin Anvil";
      case "ROGUE603": return "Qi Scroll";
      case "ROGUE604": return "Gorganian Cypher";
      case "ROGUE605": return "Frail Rose";
      case "ROGUE606": return "Seekers Knapsack";
      case "ROGUE607": return "Tome of Sutcliffe";
      case "ROGUE608": return "Paper Crown";
      case "ROGUE609": return "Staff of Sol";
      case "ROGUE610": return "Deck of Cards";
      case "ROGUE611": return "Cracked Bloodstone";
      case "ROGUE612": return "Plain Bloodstone";
      case "ROGUE613": return "Polished Bloodstone";
      case "ROGUE614": return "Lustrous Bloodstone";
      case "ROGUE615": return "Perfect Bloodstone";
      case "ROGUE616": return "Legendary Bloodstone";

      case "ROGUE701": return "Acorn of Korshem";
      case "ROGUE702": return "Solanian Bell";
      case "ROGUE703": return "Broken Hourglass";
      case "ROGUE704": return "Mark of Mastery";
      case "ROGUE705": return "Magnifying Glass";
      case "ROGUE706": return "Sown Seed";
      case "ROGUE707": return "Blacktek Amplifier";
      case "ROGUE708": return "Unstable Core";
      case "ROGUE709": return "Ward of Protection";
      case "ROGUE710": return "Sword of the Brave and Timid";
      case "ROGUE711": return "Raven's Heart";

      case "ROGUE801": return "Perfect Mirror";
      case "ROGUE802": return "Teachings of War";
      case "ROGUE803": return "Mountain Shard";
      case "ROGUE804": return "Unstable Engine";
      case "ROGUE805": return "Ethereal Armor";
      case "ROGUE806": return "Soul of Blasmophet";
      case "ROGUE807": return "Teklo's Cranium";
      default: return "";
    }
}

function ROGUERarity($cardID)
{
    switch ($cardID) {
      case "ROGUE507": case "ROGUE508": case "ROGUE509": case "ROGUE510": case "ROGUE511": case "ROGUE512": case "ROGUE513": case "ROGUE516": case "ROGUE517": return "C";
      case "ROGUE501": case "ROGUE504": case "ROGUE518": case "ROGUE519": case "ROGUE521": case "ROGUE522": case "ROGUE523": case "ROGUE524": case "ROGUE525": return "R";
      case "ROGUE502": case "ROGUE503": case "ROGUE505": case "ROGUE506": case "ROGUE526": case "ROGUE527": case "ROGUE528": return "M";

      case "ROGUE601": case "ROGUE602": case "ROGUE603": case "ROGUE604": case "ROGUE605":
      case "ROGUE606": case "ROGUE607": case "ROGUE608": case "ROGUE609": case "ROGUE610":
      case "ROGUE611": case "ROGUE612": case "ROGUE613": case "ROGUE614": case "ROGUE615":
      case "ROGUE616":
        return "C";
      case "ROGUE701": case "ROGUE702": case "ROGUE703": case "ROGUE704": case "ROGUE705":
      case "ROGUE706": case "ROGUE707": case "ROGUE708": case "ROGUE709": case "ROGUE710":
      case "ROGUE711":
        return "R";
      case "ROGUE801": case "ROGUE802": case "ROGUE803": case "ROGUE804": case "ROGUE805":
      case "ROGUE806":
        return "M";
      default: return "";
    }
}

function ROGUECardCost($cardID)
{
    switch ($cardID) {
      case "ROGUE501": return 0;
      default: return 0;
    }
}

function ROGUEPitchValue($cardID)
{
    switch ($cardID) {
      case "ROGUE001": return -1;
      case "ROGUE002": return -1;
      case "ROGUE003": return -1;
      case "ROGUE004": return -1;
      case "ROGUE005": return -1;
      case "ROGUE006": return -1;
      case "ROGUE007": return -1;
      case "ROGUE008": return -1;
      case "ROGUE009": return -1;
      case "ROGUE010": return -1;
      case "ROGUE013": return -1;
      case "ROGUE014": return -1;
      case "ROGUE032": return -1;
      case "ROGUE501": return -1; //Broken Hourglass
      case "ROGUE502": return -1; //Perfect Mirror
      case "ROGUE503": return -1; //Scroll of Mastery
      case "ROGUE504": return -1; //Blacksmith's Tongs
      case "ROGUE505": return -1; //Teklo's Cranium
      case "ROGUE506": return -1; //Teachings of War
      case "ROGUE507": return -1; //Merchant's Handbag
      case "ROGUE508": return -1; //Shattered Mirror
      case "ROGUE509": return -1; //Qi Scroll
      case "ROGUE510": return -1; //Survival Kit
      case "ROGUE511": return -1; //Magnifying Glass
      case "ROGUE512": return -1; //Dagger of the Meek
      case "ROGUE513": return -1; //Hammer of the Mighty
      case "ROGUE514": return -1; //Teklo Power Generator (not implemented)
      case "ROGUE515": return -1; //Lucky Tooth (not implemented)
      case "ROGUE516": return -1; //Mark of Undeath
      case "ROGUE517": return -1; //Shamans Skullbone
      case "ROGUE518": return -1; //Wild Mirror
      case "ROGUE519": return -1; //Shard of the Mountain
      case "ROGUE520": return -1; //Blacksmith's Hammer (not implemented) (Quesioning how possible this is to implement, gonna ignore for now)
      case "ROGUE521": return -1; //Solanian Bell
      case "ROGUE522": return -1; //Thieves Tools
      case "ROGUE523": return -1; //Ward of Protection
      case "ROGUE524": return -1; //Gorganian Cipher
      case "ROGUE525": return -1; //Acorn of Korshem
      case "ROGUE526": return -1; //Blacktek Timebomb
      case "ROGUE527": return -1; //Merchant Supply Cart
      case "ROGUE528": return -1; //Sutcliffe's Tome

      case "ROGUE601": case "ROGUE602": case "ROGUE603": case "ROGUE604": case "ROGUE605":
      case "ROGUE606": case "ROGUE607": case "ROGUE608": case "ROGUE609": case "ROGUE610":
      case "ROGUE611": case "ROGUE612": case "ROGUE613": case "ROGUE614": case "ROGUE615":
      case "ROGUE616":
        return -1;
      case "ROGUE701": case "ROGUE702": case "ROGUE703": case "ROGUE704": case "ROGUE705":
      case "ROGUE706": case "ROGUE707": case "ROGUE708": case "ROGUE709": case "ROGUE710":
      case "ROGUE711":
        return -1;
      case "ROGUE801": case "ROGUE802": case "ROGUE803": case "ROGUE804": case "ROGUE805":
      case "ROGUE806":
        return -1;
      default: return 3;
    }
}

function ROGUEBlockValue($cardID)
{
    switch ($cardID) {
      case "ROGUE001": return -1;
      case "ROGUE002": return -1;
      case "ROGUE003": return -1;
      case "ROGUE004": return -1;
      case "ROGUE005": return -1;
      case "ROGUE006": return -1;
      case "ROGUE007": return -1;
      case "ROGUE008": return -1;
      case "ROGUE009": return -1;
      case "ROGUE010": return -1;
      case "ROGUE013": return -1;
      case "ROGUE014": return -1;
      case "ROGUE032": return -1;
      case "ROGUE501": return -1;
      default:
        return 3;
    }
}

function ROGUEAttackValue($cardID)
{
    switch ($cardID) {
      case "ROGUE002": return 2;
      case "ROGUE005": return 4;
      case "ROGUE007": return 2;
      case "ROGUE032": return 2;
      default:
        return 0;
    }
}

function ROGUEPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
{
    global $currentPlayer, $CS_PlayIndex;
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch ($cardID) {
      case "ROGUE007":
        $hand = &GetHand($currentPlayer);
        array_unshift($hand, "lightning_surge_blue");
        return "";
      case "ROGUE501":
        PutPermanentIntoPlay($currentPlayer, $cardID);
        return "";
      default:
        return "";
    }
}

function ROGUEHitEffect($cardID)
{
    switch ($cardID) {

        default: break;
    }
}

function ROGUEPowerStart()
{
  global $mainPlayer, $defPlayer;
  $permanents = &GetPermanents($mainPlayer);

  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {
      case "ROGUE508":
        $deck = &GetDeck($mainPlayer);
        $optionOne = rand(0, count($deck)-1);
        $optionTwo = rand(0, count($deck)-1);
        $optionThree = rand(0, count($deck)-1);
        if($optionOne == $optionTwo)
        {
          if($optionOne == 0) ++$optionTwo;
          else --$optionTwo;
        }
        for($i = 0; $i < 5 && ($optionThree == $optionOne || $optionThree == $optionOne); ++$i)
        {
          if($optionThree <= 4) $optionThree += 3;
          else --$optionThree;
        }
        AddDecisionQueue("CHOOSEDECK", $mainPlayer, $optionOne . "," . $optionTwo . "," . $optionThree);
        AddDecisionQueue("ROGUEMIRRORGAMESTART", $mainPlayer, "0");
        break;
      case "ROGUE510":
        GainHealth(5, $mainPlayer);
        break;
      case "ROGUE516":
        $health = &GetHealth($mainPlayer);
        if($health <= 2) $health = 20;
        break;
      case "ROGUE524":
        $deck = &GetDeck($mainPlayer);
        array_unshift($deck, "gorganian_tome", "gorganian_tome", "gorganian_tome");
        break;

      case "ROGUE604":
        $deck = &GetDeck($mainPlayer);
        array_unshift($deck, "gorganian_tome", "gorganian_tome", "gorganian_tome");
        break;
      case "ROGUE609":
        $health = &GetHealth($mainPlayer);
        if($health <= 10) $health += 5;
        break;
      case "ROGUE708":
        $options = [];
        for ($j = count($permanents) - PermanentPieces(); $j >= 0; $j -= PermanentPieces())
        {
          if($permanents[$j] != "ROGUE708" && $permanents[$j] != "ROGUE609" && $permanents[$j] != "ROGUE604" && CardSubType($permanents[$j]) == "Power") array_push($options, $permanents[$j]);
        }
        if(count($options) > 0) $permanents[$i] = $options[rand(0, (count($options)-1))];
        break;
      default:
        break;
    }
  }
}
?>
