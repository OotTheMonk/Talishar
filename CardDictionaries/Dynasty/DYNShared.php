<?php

function DYNAbilityCost($cardID)
{
    switch ($cardID) {
        case "DYN001": return 3;
        case "DYN068": return 3;
        case "DYN075": return 3; // TODO: Yoji cardID to be modified with set release
        case "DYN242": return 1;
        case "DYN243": return 2;
       
        default:
            return 0;
    }
}

function DYNAbilityType($cardID, $index = -1)
{
    global $currentPlayer, $mainPlayer, $defPlayer;
    switch ($cardID) {
        case "DYN001": return "A";
        case "DYN068": return "AA";
        case "DYN075": return "I"; // TODO: Yoji cardID to be modified with set release
        case "DYN242": case "DYN243": return "A";
        default:
            return "";
    }
}

// Natural go again or ability go again. Attacks that gain go again should be in CoreLogic (due to hypothermia)
function DYNHasGoAgain($cardID)
{
    switch ($cardID) {

        default:
            return false;
    }
}

function DYNAbilityHasGoAgain($cardID)
{
    switch ($cardID) {
        case "DYN243": return true;

        default:
            return false;
    }
}

function DYNEffectAttackModifier($cardID)
{
    global $combatChainState, $CCS_LinkBaseAttack;
    $params = explode(",", $cardID);
    $cardID = $params[0];
    if (count($params) > 1) $parameter = $params[1];
    switch ($cardID) {

        default:
            return 0;
    }
}

function DYNCombatEffectActive($cardID, $attackID)
{
    $params = explode(",", $cardID);
    $cardID = $params[0];
    switch ($cardID) {

        default:
            return false;
    }
}


function DYNCardTalent($cardID) // TODO
{
  $number = intval(substr($cardID, 3));
  if($number <= 0) return "";
  else if($number >= 1 && $number <= 2) return "ROYAL,DRACONIC";
//   else if($number >= 3 && $number <= 124) return "";
//   else if($number >= 125 && $number <= 150) return "";
//   else if($number >= 406 && $number <= 417 ) return "";
//   else if($number >= 439 && $number <= 441) return "";
  else return "NONE";
}

function DYNCardType($cardID)
{
    switch ($cardID) {
        case "DYN001": return "C";
        case "DYN068": return "W";
        case "DYN075": return "C"; // TODO: Yoji cardID to be modified with set release
        case "DYN116": case "DYN117": case "DYN118": return "A"; // TODO: Blessing of Aether cardID to be edited
        case "DYN234": return "E";
        case "DYN242": return "A";
        case "DYN243": return "T";

        default:
            return "";
    }
}

function DYNCardSubtype($cardID)
{
    switch ($cardID) {
        case "DYN068": return "Axe";
        case "DYN116": case "DYN117": case "DYN118": return "Aura"; // TODO: Blessing of Aether cardID to be edited
        case "DYN234": return "Head";
        case "DYN242": return "Item";
        case "DYN243": return "Item";

        default:
            return "";
    }
}

function DYNCardCost($cardID)
{
    switch ($cardID) {
        case "DYN001": return 0;
        case "DYN116": case "DYN117": case "DYN118": return 1; // TODO: Blessing of Aether cardID to be edited
        case "DYN242": return 2;
        case "DYN243": return 0;
        default:
            return 0;
    }
}

function DYNPitchValue($cardID)
{
    switch ($cardID) {
        case "DYN001": return 0;
        case "DYN068": return 0;
        case "DYN075": return 0; // TODO: Yoji cardID to be modified with set release
        case "DYN234": return 0;
        case "DYN116": return 1; // TODO: Blessing of Aether cardID to be edited
        case "DYN117": return 2; // TODO: Blessing of Aether cardID to be edited
        case "DYN242": return 1;
        case "DYN243": return 0;

        default:
            return 3;
    }
}

function DYNBlockValue($cardID)
{
    switch ($cardID) {
        case "DYN001": return -1;
        case "DYN068": return -1;
        case "DYN075": return -1; // TODO: Yoji cardID to be modified with set release
        case "DYN116": case "DYN117": case "DYN118": return 2; // TODO: Blessing of Aether cardID to be edited
        case "DYN234": return -1;
        case "DYN242": case "DYN243": return -1;
        default:
            return 3;
    }
}

function DYNAttackValue($cardID)
{
    switch ($cardID) {
        case "DYN068": return 3;
        default:
            return 0;
    }
}

function DYNPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
{
    global $currentPlayer, $combatChain, $CS_PlayIndex, $combatChainState, $CCS_GoesWhereAfterLinkResolves;
    global $CS_HighestRoll, $CS_NumNonAttackCards, $CS_NumAttackCards, $CS_NumBoosted, $mainPlayer, $CCS_NumBoosted, $CCS_RequiredEquipmentBlock;
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch ($cardID) {
        case "DYN001": 
            AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKCARD,ARC159");
            AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, "<-", 1);
            AddDecisionQueue("ATTACKWITHIT", $currentPlayer, "-", 1);
            AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
            return "";
        case "DYN075": // TODO: Yoji cardID to be modified with set release
            AddCurrentTurnEffect($cardID, $currentPlayer);
            return ""; 
        case "DYN242":   
            $rv = "";
            if($from == "PLAY"){
                DestroyMyItem(GetClassState($currentPlayer, $CS_PlayIndex));
                AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose any number of heroes");
                AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Both_Heroes,Target_Yourself,Target_No_Heroes");
                AddDecisionQueue("IMPERIALWARHORN", $currentPlayer, "<-", 1);
            }
        return $rv;
        case "DYN243":
            $rv = "";
            if($from == "PLAY"){
                DestroyMyItem(GetClassState($currentPlayer, $CS_PlayIndex));
                $rv = "Draws a card.";
                Draw($currentPlayer);
            }
            return $rv;
        default:
            return "";
    }
}

function DYNHitEffect($cardID)
{
    global $mainPlayer, $defPlayer, $CS_NumAuras, $chainLinks;
    switch ($cardID) {

        default: break;
    }
}

function IsRoyal($player) 
{
    $mainCharacter = &GetPlayerCharacter($player);

    if (SearchCharacterForCard($player, "DYN234")) return true;

    switch ($mainCharacter[0]) {
        case "DYN001":
            return true;
        default: break;
    }
    return false;
}