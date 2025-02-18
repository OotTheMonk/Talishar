<?php

function CardName($cardID)
{
    if($cardID == NULL) return "";
    if($cardID == "ATKCOU") return "+1 Attack Counter";
    $setID = SetID($cardID);
    $arr = str_split($setID, 3);
    if(count($arr) < 2) return "";
    $set = $arr[0];
    if($set != "ROG" && $set != "DUM")
    {
      return GeneratedCardName($cardID);
      // $number = intval(substr($setID, 3));
      // if($number < 400 || ($set != "MON" && $set != "DYN" && $set != "MST" && $set != "HNT" && $cardID != "teklovossen_the_mechropotent" && $cardID != "teklovossen_the_mechropotentb" && $cardID != "levia_redeemed")) return GeneratedCardName($cardID);
    }
    if ($set == "ROG") {
      return ROGUEName($cardID);
    }
    switch($cardID)
    {
		//   case "MON400": return "Spell Fray Cloak";
		//   case "MON401": return "Spell Fray Gloves";
		//   case "MON402": return "Spell Fray Leggings";
		//   case "the_librarian": return "The Librarian";
		//   case "minerva_themis": return "Minerva Themis";
		//   case "lady_barthimont": return "Lady Barthimont";
		//   case "lord_sutcliffe": return "Lord Sutcliffe";
    //   case "nitro_mechanoid": return "Nitro Mechanoid";
		//   case "nitro_mechanoida": return "Nitro Mechanoid";
		//   case "nitro_mechanoidb": return "Nitro Mechanoid";
		//   case "nitro_mechanoidc": return "Nitro Mechanoid";
    //   case "suraya_archangel_of_knowledge": return "Suraya, Archangel of Knowledge";
      case "DUMMY": return "Practice Dummy";
      case "DUMMYDISHONORED": return "Dishonored Hero";
    //   case "teklovossen_the_mechropotent": return "Teklovossen, the Mechropotent";
    //   case "teklovossen_the_mechropotentb": return "Teklovossen, the Mechropotent";
    //   case "levia_redeemed": return "Blasmophet, Levia Consumed";
    //   case "mistcloak_gully_inner_chi_blue": case "sacred_art_undercurrent_desires_blue_inner_chi_blue": case "sacred_art_immortal_lunar_shrine_blue_inner_chi_blue": case "sacred_art_jade_tiger_domain_blue_inner_chi_blue": 
    //   case "MST095_inner_chi_blue": case "MST096_inner_chi_blue": case "pass_over_blue_inner_chi_blue": case "path_well_traveled_blue_inner_chi_blue": 
    //   case "preserve_tradition_blue_inner_chi_blue": case "rising_sun_setting_moon_blue_inner_chi_blue": case "stir_the_pot_blue_inner_chi_blue": case "the_grain_that_tips_the_scale_blue_inner_chi_blue":
    //     return "Inner Chi";
    //   case "the_hand_that_pulls_the_strings": 
    //     return "The Hand that Pulls the Strings";
      default: return "";
    }
	}
