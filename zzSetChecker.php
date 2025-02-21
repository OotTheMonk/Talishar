<?php

include 'GameLogic.php';
include './zzImageConverter.php';

$set = "OUT";
$count = 0;
for ($number = 0; $number < 300; ++$number) {
  $card = strval($number);
  if ($number < 10) $card = "0" . $card;
  if ($number < 100) $card = "0" . $card;
  $card = $set . $card;
  $type = CardType($card);
  if ($type == "") {
    echo ($card . " not found.<br>");
    ++$count;
  }
  if($type == "AR" && AttackValue($card) != 0) echo($card . " Attack Reaction has attack " . AttackValue($card) . ".<BR>");
  if ($type == "AA" && $card != "fractal_replication_red" && AttackValue($card) == 0) echo ($card . " Attack action has no attack.<br>");
  if (($type != "C" && $type != "E" && $type != "W" && $type != "T") && PitchValue($card) == 0) echo ($card . " has no pitch value.<br>");
  CheckImage($card);
}
echo ("Total missing: " . $count);


?>
