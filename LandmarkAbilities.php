<?php

function PlayLandmark($cardID, $player, $from="-")
{
  global $landmarks;
  if (count($landmarks) > 0) DestroyLandmark(0); //Right now, playing a new landmark destroys the old landmark
  if (CardSubtype($cardID) != "Landmark") {
    WriteError($cardID . " was tried to play as a landmark, but is not a landmark.");
    return;
  }
  array_push($landmarks, $cardID);
  array_push($landmarks, $player); //The player that originally played the landmark
  array_push($landmarks, $from);
}

function DestroyLandmark($index, $skipDestroy=false)
{
  global $landmarks;
  $cardID = $landmarks[$index];
  $ownerID = $landmarks[$index + 1];
  for($j = $index + LandmarkPieces() - 1; $j >= $index; --$j) {
    unset($landmarks[$j]);
  }
  $landmarks = array_values($landmarks);
  if(!$skipDestroy) AddGraveyard($cardID, $ownerID, "PLAY");
  return $cardID;
}

function LandmarkBeginEndPhaseAbilities()
{
  global $landmarks, $mainPlayer, $CS_NumBluePlayed, $CS_NumBlueDefended;
  for ($i = 0; $i < count($landmarks); ++$i) {
    switch ($landmarks[$i]) {
      case "great_library_of_solana":
        if (SearchPitchForColor($mainPlayer, 2) >= 2) {
          AddCurrentTurnEffect("great_library_of_solana", $mainPlayer);
        }
        break;
      case "mistcloak_gully":
        if(GetClassState($landmarks[$i+1], $CS_NumBluePlayed) > 0 && SearchPitchForColor($landmarks[$i+1], 3) > 0 && GetClassState($landmarks[$i+1], $CS_NumBlueDefended) > 0) {
          Transcend($landmarks[$i+1], "MST000_inner_chi_blue", $landmarks[$i+2]);
          DestroyLandmark($i, true);
        }
        elseif(GetClassState($landmarks[$i+1], $CS_NumBluePlayed) <= 0 && SearchPitchForColor($landmarks[$i+1], 3) <= 0 && GetClassState($landmarks[$i+1], $CS_NumBlueDefended) <= 0) {
          DestroyLandmark($i);
        }
        break;
      default:
        break;
    }
  }
}

function LandmarkStartTurnAbilities()
{
  global $landmarks, $mainPlayer;
  for ($i = 0; $i < count($landmarks); ++$i) {
    switch ($landmarks[$i]) {
      case "mistcloak_gully":
        if($landmarks[$i+1] != $mainPlayer) {
          AddCurrentTurnEffect($landmarks[$i], $mainPlayer);
        }
        break;
      default:
        break;
      }
    }
}