<?php

function GetNextEncounter() //TODO overhaul this whole function and children
{
  $encounter = &GetZone(1, "Encounter");
  // WriteLog("hijacked GetNextEncounter");
  // WriteLog("Encounter[0]: " . $encounter->encounterID);
  // WriteLog("Encounter[1]: " . $encounter->subphase);
  // WriteLog("Encounter[2]: " . $encounter->position);
  ++$encounter->position;
  switch($encounter->adventure)
  {
    case "Ira":
      switch($encounter->difficulty)
      {
        case "Easy":
        case "Normal":
        case "Hard":
          switch($encounter->position)
          {
            case 1: return CrossroadsDoubleChoice("Make_your_way_up_through_Metrix,Take_the_scenic_route_through_the_back_streets,Catch_a_ferry_across_the_lake");//combat choice of X, Y, and Z
            case 2: return RandomEvent();
            case 3: return CrossroadsDoubleChoice("Turn_back_and_take_the_long_way_around,Approach_the_roadblock_head_on,Venture_into_the_forest_and_attempt_to_sneak_past");//combat choice of X, Y, and Z
            case 4: return RandomEvent();
            case 5: return CrossroadsDoubleChoice("Ignore_your_instincts_and_stop_for_the_night,Leave_the_town_immediately");//combat choice of X, Y, and Z
            case 6: return RandomEvent();
            case 7: return "Follow_the_sounds_of_laughter"; //Campfire or a shop
            case 8: return "Explore_the_cave"; //Elite
            case 9: return "Search_through_the_treasures"; //Get a power
            case 10: return CrossroadsDoubleChoice("Attempt_to_cross_the_river_here,Travel_downstream_to_find_a_bridge,Travel_upstream_to_the_nearest_town");//combat choice of X, Y, and Z
            case 11: return RandomEvent();
            case 12: return CrossroadsDoubleChoice("You_notice_a_mountain_pass_you_can_move_through,Travel_through_a_thin_ravine,Travel_through_a_nearby_cavern");//combat choice of X, Y, and Z
            case 13: return RandomEvent();
            case 14: return CrossroadsDoubleChoice("Use_the_cover_of_darkness_to_cross,Take_precaution_and_find_a_different_way_across,Brave_the_bridge");//combat choice of X, Y, and Z
            case 15: return RandomEvent();
            case 16: return "Follow_the_sounds_of_laughter"; //Campfire or a shop
            case 17: return "Approach_your_destination"; //Boss
            case 18: return "Return_to_menu";
          }
      }
  }
}

function RandomEvent()
{
  $devTestEvents = array(); //Put events in here to test them. They will be the only ones to show up. Make sure you put at least 2 options
  $commonEvents = array("You_wander_through_a_fresh_battlefield", "You_find_a_great_library", "You_see_one_of_the_most_beautiful_views_in_all_of_Rathe", "You_find_a_small_smithing_hut", "You_come_across_a_small_dojo", "A_lavish_noble_passes_you_by", "You_pass_a_strange_man_in_robes", "A_knight_approaches_you_asking_to_spar", "You_find_a_small_brown_chest", "You_find_a_small_white_chest", "You_find_a_small_green_chest", "You_find_a_small_blue_chest", "You_find_a_small_red_chest", "You_stumble_into_a_lively_tavern");
  $rareEvents = array("You_see_a_small_temple_a_ways_from_the_path", "A_radiant_woman_comes_across_your_path", "You_find_a_small_purple_chest", "You_find_an_ornate_brown_chest", "You_find_an_ornate_white_chest", "You_find_an_ornate_blue_chest", "You_find_an_ornate_red_chest", "You_find_an_ornate_green_chest", "You_see_smoke_rising_in_the_distance", "You_find_a_large_shrine", "You_find_a_large_mirror", "You_visit_an_old_friend", "You_come_across_a_strange_library", "You_find_an_old_cottage");
  $majesticEvents = array("You_find_an_ornate_purple_chest", "You_see_a_beautiful_sigil", "You_find_a_clear_pool");

  $encounter = &GetZone(1, "Encounter");
  $rv = "";
  if($encounter->cleanse) $rv = "Return_to_the_pool,";
  $randEvent = rand(1,100);
  if(count($devTestEvents) >= 2 ){
    $options = GetOptions(2, count($devTestEvents)-1);
    return $rv.$devTestEvents[$options[0]].",".$devTestEvents[$options[1]];
  }
  if($randEvent > 90)
  {
    $options = GetOptions(2, count($majesticEvents)-1);
    return $rv.$majesticEvents[$options[0]] . "," . $majesticEvents[$options[1]];
  }
  else if($randEvent > 70)
  {
    $options = GetOptions(2, count($rareEvents)-1);
    return $rv.$rareEvents[$options[0]] . "," . $rareEvents[$options[1]];
  }
  else
  {
    $options = GetOptions(2, count($commonEvents)-1);
    return $rv.$commonEvents[$options[0]] . "," . $commonEvents[$options[1]];
  }
}

function CrossroadsDoubleChoice($string)
{
  $choices = explode(",", $string);
  $options = GetOptions(2, count($choices)-1);
  return $choices[$options[0]].",".$choices[$options[1]];
}

function GetCrossroadsDescription()
{
  $encounter = &GetZone(1, "Encounter");
  switch($encounter->adventure)
  {
    case "Ira":
      switch($encounter->difficulty)
      {
        case "Easy":
        case "Normal":
        case "Hard":
          switch($encounter->position)
          {
            case 1: return "Your destination lies beyond the Pits.";
            case 3: return "Ahead of you lies a fallen tree. It likely did not fall naturally.";
            case 5: return "You find yourself in a small town. Something about this town feels unnatural. It unsettles you.";
            case 7: case 16: return "Your route takes you to a trade settlement. There are merchants here that have taken residence while they can turn a profit, and other residents passing through.";
            case 8: return "Off to the side of the road is a small cave. You hear a roar echo from inside. In your experience, caves like this typically hold hidden treasures, so it's certainly worth checking out.";
            case 9: return "With the great beast felled, you turn to the pile of treasures within the cave.";
            case 10: return "You come upon a great river. It's too wide to cross on your own.";
            case 12: return "Before you lies the mountains of Misteria, and within them your goal.";
            case 14: return "As you travel through the mountains, you come upon a rope bridge connecting the mountains. You can sense the end of your journey is approaching. A hooded figure stands on the bridge, waiting.";
            case 17: return "It seems your journey has come to an end.";
            default: return "During your journey, something quite interesting happens.";
          }
      }
  }
}

function GetCrossroadsChoiceHeader() {
  $encounter = &GetZone(1, "Encounter");
  switch($encounter->adventure)
  {
    case "Ira":
      switch($encounter->difficulty)
      {
        case "Easy":
        case "Normal":
        case "Hard":
          switch($encounter->position)
          {
            case 1: return "How would you like to leave?";
            case 3: return "What would you like to do?";
            case 5: return "What would you like to do?";
            case 7: case 16: return "";
            case 8: return "";
            case 9: return "";
            case 10: return "What would you like to do?";
            case 12: return "Where would you like to go?";
            case 14: return "What would you like to do?";
            case 17: return "";
            default: return "What happens?";
          }
      }
  }
}

function GetCrossroadsImage()
{
  {
    $encounter = &GetZone(1, "Encounter");
    switch($encounter->adventure)
    {
      case "Ira":
        switch($encounter->difficulty)
        {
          case "Easy":
          case "Normal":
          case "Hard":
            switch($encounter->position)
            {
              case 1: return "perch_grapplers_cropped.png";
              case 3: return "skullhorn_cropped.png";
              case 5: return "sigil_of_suffering_red_cropped.png";
              case 8: return "clearing_bellow_blue_cropped.png";
              case 9: return "potion_of_seeing_blue_cropped.png";
              case 10: return "time_skippers_cropped.png";
              case 12: return "zen_state_cropped.png";
              case 14: return "flic_flak_red_cropped.png";
              case 17: return "ira_crimson_haze_cropped.png";
              default: return "read_the_glide_path_red_cropped.png";

            }
        }
    }
  }
}
?>
