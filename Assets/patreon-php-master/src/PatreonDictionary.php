<?php

enum ContentCreators : string
{
  case InstantSpeed = "0";
  case ManSant = "731";

  public function SessionName(): string
  {
    switch($this->value)
    {
      case "0": return "isInstantSpeedPatron";
      case "731": return "isManSantPatron";
      default: return "";
    }
  }

  public function PatreonLink(): string
  {
    switch($this->value)
    {
      case "0": return "https://www.patreon.com/instantspeedpod";
      case "731": return "https://www.patreon.com/ManSant";
      default: return "";
    }
  }

  public function ChannelLink(): string
  {
    switch($this->value)
    {
      case "0": return "https://www.youtube.com/playlist?list=PLIo1KFShm1L3e91QrlPG6ZdwfmqKk0NIP";
      case "731": return "https://www.youtube.com/@ManSantFaB";
      default: return "";
    }
  }

  public function BannerURL(): string
  {
    switch($this->value)
    {
      case "0": return "./Assets/patreon-php-master/assets/ContentCreatorImages/InstantSpeedBanner.webp";
      default: return "";
    }
  }

  public function HeroOverlayURL($heroID): string
  {
    switch($this->value)
    {
      case "0": //WatchFlake
        if(CardClass($heroID) == "GUARDIAN") return "./Assets/patreon-php-master/assets/ContentCreatorImages/Matt_anathos_overlay.webp";
        return "./Assets/patreon-php-master/assets/ContentCreatorImages/flakeOverlay.webp";
      case "731":
        return "./Assets/patreon-php-master/assets/ContentCreatorImages/ManSantLevia.webp";
      default: return "";
    }
  }

  public function NameColor(): string
  {
    switch($this->value)
    {
      case "0": return "rgb(2,190,253)";
      case "731": return "rgb(255,53,42)";
      default: return "";
    }
  }
}

enum PatreonCampaign : string
{
  //These ones have no patreon
  case Pummelowanko = "0";
  case DragonShieldProTeamWB = "1";
  case AscentGaming = "2";
  case EternalOracles = "3";
  case Luminaris = "4";
  case FABLAB = "5";
  case OnHit = "6";
  case SecondCycle = "7";
  case SonicDoom = "8";
  case TeamPummel = "9";
  case TeamEmperorsRome = "10";
  case SunflowerSamurai = "11";
  case ColdFoilControl = "12";
  case RighteousGaming = "13";
  case TeamTalishar = "14";
  case TeamTideBreakers = "15";
  case CupofTCG = "16";
  case ScowlingFleshBag = "17";
  case ThaiCardsShop = "18";
  case Talishar = "7198186";
  case PushThePoint = "7579026";
  case GoAgainGaming = "7329070";
  case RedZoneRogue = "1787491";
  case Fabrary = "8997252";
  case ManSant = "8955846";
  case AttackActionPodcast = "6839952";
  case ArsenalPass = "7285727";
  case TheTekloFoundry = "8635931";
  case FleshandCommonBlood = "8736344";
  case SinOnStream = "7593240";
  case FreshandBuds = "8458487";
  case Sloopdoop = "6996822";
  case DMArmada = "1919413";
  case TheCardGuyz = "7733166";
  case HomeTownTCG = "7009853";
  case FleshAndPod = "8338817";
  case Kappolo = "9361474";
  case LibrariansOfSolana = "3828539";
  case TheMetrixMetagame = "8951973";
  case TheTablePit = "9370276";
  case TCGTed = "9404423";
  case CardAdvantage = "8040288";
  case RavenousBabble = "10147920";
  case BlackWingStudios = "4006023";
  case OnHitEffect = "10811477";
  case DaganWhite = "9851977";
  case BrandaoTCG = "279086";
  case OffTheRailsTCG = "11184392";
  case Nxi = "11481720";
  case PvtVoid = "9408649";
  case WeMakeBest = "9734205";
  case MnRCast = "9574942";
  case OnTheBauble = "11561507";
  case GorganianTome = "9555916";
  case FABChaos = "8716783";
  case DailyFab = "11266104";
  case ThePlagueHive = "12144126";
  case Dropcast = "12245100";
  case FleshandBloodwithPablo = "12300349";
  case ChantsAndDaggers = "10956423";
  case Dazzyfizzle = "12977197";
  case Nikobru = "13586904";

  public function SessionID(): string
  {
    switch($this->value)
    {
      case "7198186": return "isPatron";
      case "7579026": return "isPtPPatron";
      case "7329070": return "isGoAgainGamingPatron";
      case "1787491": return "isRedZoneRoguePatron";
      case "8997252": return "isFabraryPatron";
      case "8955846": return "isManSantPatron";
      case "6839952": return "isAttackActionPodcastPatreon";
      case "7285727": return "isArsenalPassPatreon";
      case "8635931": return "isTheTekloFoundryPatreon";
      case "8736344": return "isFleshAndCommonBloodPatreon";
      case "7593240": return "isSinOnStreamPatreon";
      case "8458487": return "isFreshAndBudsPatreon";
      case "6996822": return "isSloopdoopPatron";
      case "1919413": return "isDMArmadaPatron";
      case "7733166": return "isTheCardGuyzPatron";
      case "7009853": return "isHomeTownTCGPatron";
      case "8338817": return "isFleshAndPodPatron";
      case "9361474": return "isKappoloPatron";
      case "3828539": return "isLibrariansOfSolanaPatron";
      case "8951973": return "isTheMetrixMetagamePatron";
      case "9370276": return "isTheTablePitPatron";
      case "9404423": return "isTCGTedPatron";
      case "8040288": return "isCardAdvantagePatron";
      case "10147920": return "isRavenousBabblePatron";
      case "4006023": return "isBlackWingStudiosPatron";
      case "10811477": return "isOnHitEffectPatron";
      case "9851977": return "isDaganWhitePatron";
      case "279086": return "isBrandaoTCGPatron";
      case "11184392": return "isOffTheRailsTCGPatron";
      case "11481720": return "isNxiPatron";
      case "9408649": return "isPvtVoidPatron";
      case "9734205": return "isWeMakeBestPatron";
      case "9574942": return "isMnRCastPatron";
      case "11561507": return "isOnTheBaublePatron";
      case "9555916": return "isGorganianTomePatron";
      case "8716783": return "isFABChaosPatron";
      case "11266104": return "isDailyFabPatron";
      case "12245100": return "isDropcastPatron";
      case "12300349": return "IsFleshandBloodwithPabloPatron";
      case "10956423": return "isChantsAndDaggersPatron";
      case "12977197": return "isDazzyfizzlePatron";
      case "13586904": return "isNikobruPatron";
      default: return "";
    }
  }

  public function CampaignName(): string
  {
    switch($this->value)
    {
      case "0": return "Pummelowanko";
      case "1": return "Dragon Shield Pro Team";
      case "2": return "AscentGaming";
      case "3": return "Eternal Oracles";
      case "4": return "Luminaris";
      case "5": return "FAB-LAB";
      case "6": return "OnHit";
      case "7": return "Second Cycle";
      case "8": return "Sonic Doom";
      case "9": return "Pummel 52100";
      case "10": return "Team Emperors Rome";
      case "11": return "Sunflower Samurai";
      case "12": return "Cold Foil Control";
      case "13": return "Righteous Gaming";
      case "14": return "Team Talishar";
      case "15": return "Team Tide Breakers";
      case "16": return "Cup of TCG";
      case "17": return "ScowlingFleshBag";
      case "18": return "Thai Cards Shop";
      case "7198186": return "Talishar";
      case "7579026": return "Push the Point";
      case "7329070": return "Go Again Gaming";
      case "1787491": return "Red Zone Rogue";
      case "8997252": return "Fabrary";
      case "8955846": return "Man Sant";
      case "6839952": return "Attack Action Podcast";
      case "7285727": return "Arsenal Pass";
      case "8635931": return "The Teklo Foundry";
      case "8736344": return "Flesh and Common Blood";
      case "7593240": return "Sin On Stream";
      case "8458487": return "Fresh and Buds";
      case "6996822": return "Sloopdoop";
      case "1919413": return "DM Armada";
      case "7733166": return "The Card Guyz";
      case "7009853": return "HomeTownTCG";
      case "8338817": return "Flesh And Pod";
      case "9361474": return "Kappolo";
      case "3828539": return "Librarians of Solana";
      case "8951973": return "The Metrix Metagame";
      case "9370276": return "The Table Pit";
      case "9404423": return "TCG Ted";
      case "8040288": return "Card Advantage";
      case "10147920": return "Ravenous Babble";
      case "4006023": return "Black Wing Studios";
      case "10811477": return "On Hit Effect";
      case "9851977": return "Dagan White";
      case "279086": return "BrandaoTCG";
      case "11184392": return "Off the Rails TCG";
      case "11481720": return "Nxi";
      case "9408649": return "PvtVoid";
      case "9734205": return "WeMakeBest";
      case "9574942": return "MnRCast";
      case "11561507": return "OnTheBauble";
      case "9555916": return "GorganianTome";
      case "8716783": return "FABChaos";
      case "11266104": return "DailyFab";
      case "12144126": return "ThePlagueHive";
      case "12245100": return "Dropcast";
      case "12300349": return "Flesh and Blood with Pablo";
      case "10956423": return "Chants and Daggers";
      case "12977197": return "Dazzyfizzle";
      case "13586904": return "Nikobru";
      default: return "";
    }
  }

  public function IsTeamMember($userName): string
  {
    switch($this->value)
    {
      case "0": return ($userName == "MrShub" || $userName == "duofanel" || $userName == "Matiisen" ||  $userName == "Pepowski" ||  $userName == "Seba_stian" ||  $userName == "NatAlien" ||  $userName == "dvooyas" || $userName == "Lukashu" || $userName == "Qwak" || $userName == "NatAlien");
      case "1": return ($userName == "TwitchTvFabschool" || $userName == "MattRogers" || $userName == "TariqPatel");
      case "2": return ($userName == "hometowntcg" || $userName == "ProfessorKibosh" || $userName == "criticalclover8" || $userName == "bomberman" || $userName == "woodjp64" || $userName == "TealWater" || $userName == "Bravosaur" || $userName == "DaganTheZookeeper" || $userName == "Dratylis" || $userName == "MoBogsly");
      case "3": return ($userName == "DeadSummer");
      case "4": return ($userName == "LeoLeo");
      case "5": return ($userName == "XIR");
      case "6": return ($userName == "wackzitt" || $userName == "RainyDays" || $userName == "HelpMeJace2" || $userName == "S1lverback55" || $userName == "VexingTie" || $userName == "Ragnell");
      case "7": return IsTeamSecondCycle($userName);
      case "8": return IsTeamSonicDoom($userName);
      case "9": return IsTeamPummel($userName);
      case "10": return IsTeamEmperorsRome($userName);
      case "11": return IsTeamSunflowerSamurai($userName);
      case "12": return IsTeamColdFoilControl($userName);
      case "13": return IsTeamRighteousGaming($userName);
      case "14": return IsTeamTalishar($userName) || $userName == "Aegisworn";
      case "15": return IsTeamTideBreakers($userName);
      case "16": return IsTeamCupofTCG($userName);
      case "17": return IsTeamScowlingFleshBag($userName);
      case "18": return IsTeamThaiCardsShop($userName);
      case "7198186": return ($userName == "OotTheMonk" || $userName == "Aegisworn");
      case "7579026": return ($userName == "Hamsack" || $userName == "BigMedSi" || $userName == "Tripp");
      case "7329070": return ($userName == "GoAgainGamingAz");
      case "1787491": return ($userName == "RedZoneRogue");
      case "8997252": return ($userName == "phillip");
      case "8955846": return ($userName == "Man_Sant" || $userName == "Aegisworn");
      case "6839952": return ($userName == "chonigman" || $userName == "Ijaque");
      case "7285727": return ($userName == "Brendan" || $userName == "TheClub");
      case "8635931": return ($userName == "TheTekloFoundry");
      case "8736344": return ($userName == "Smithel");
      case "7593240": return ($userName == "SinOnStream");
      case "8458487": return ($userName == "FreshLord");
      case "6996822": return ($userName == "Sloopdoop");
      case "1919413": return ($userName == "DMArmada");
      case "7733166": return ($userName == "NamVoTCGz" || $userName == "AlexTheCardGuy" || $userName == "RegularDegular" || $userName == "joshlau7" || $userName == "WillyB" || $userName == "Spoofy" || $userName == "ItsSebBruh" || $userName == "Knight");
      case "7009853": return ($userName == "hometowntcg");
      case "8338817": return ($userName == "imjorman" || $userName == "ADavis83" || $userName == "loganpetersen");
      case "9361474": return ($userName == "kappolo");
      case "3828539": return ($userName == "Tee");
      case "8951973": return ($userName == "Wes" || $userName == "Brandon");
      case "9370276": return ($userName == "TheTablePitYT" || $userName == "TunaTCG");
      case "9404423": return ($userName == "TCGTed");
      case "8040288": return IsTeamCardAdvantage($userName);
      case "10147920": return ($userName == "RavenousBabble" || $userName == "Arty" || $userName == "jargowsky" || $userName == "Nick52cost" || $userName == "Boomerang" || $userName == "matthias" || $userName == "Repas801");
      case "4006023": return ($userName == "BlackWingStudio");
      case "10811477": return ($userName == "Mezzo");
      case "9851977": return ($userName == "DaganTheZookeeper" || $userName == "OotTheMonk");
      case "279086": return ($userName == "brandaotcg" || $userName == "OotTheMonk" || $userName == "Aegisworn");
      case "11184392": return ($userName == "PatSmashGood" || $userName == "OotTheMonk");
      case "11481720": return ($userName == "nxi");
      case "9408649": return ($userName == "PvtVoid" || $userName == "Aegisworn");
      case "9734205": return ($userName == "tog" || $userName == "bnet" || $userName == "balakay" || $userName == "PvtVoid");
      case "9574942": return ($userName == "Azor" || $userName == "PvtVoid");
      case "11561507": return ($userName == "PvtVoid");
      case "9555916": return ($userName == "Cathardigan" || $userName == "GorganianTome" || $userName == "PvtVoid");
      case "8716783": return IsTeamFABChaos($userName);
      case "11266104": return ($userName == "Lupinefiasco" || $userName == "PvtVoid");
      case "12144126": return ($userName == "Pentregarth" || $userName == "Archon Alters" || $userName == "PvtVoid");
      case "12245100": return ($userName == "PvtVoid" || $userName == "Smithel" || $userName == "Poopking" || $userName == "HeyLookItsBrice");
      case "12300349": return ($userName == "PvtVoid");
      case "10956423": return ($userName == "ChantsAndDaggers" || $userName == "OotTheMonk");
      case "12977197": return ($userName == "PvtVoid" || $userName == "dazzyfizzle" || $userName == "OotTheMonk");
      case "13586904": return ($userName == "PvtVoid" || $userName == "Nikobru");
      default: return "";
    }
  }

  public function AltArts(): string
  {
    switch($this->value)
    {
      // Talishar
      case "7198186": return "lightning_press_red=ELE183-T,rosetta_thorn=ELE222-T,mask_of_recurring_nightmares=MST004-T,sacred_art_undercurrent_desires_blue=MST010-T,sacred_art_immortal_lunar_shrine_blue=MST032-T,twelve_petal_kasaya=MST048-T,sacred_art_jade_tiger_domain_blue=MST053-T,traverse_the_universe=MST066-T,koi_blessed_kimono=MST068-T,a_drop_in_the_ocean_blue=MST095-T,homage_to_ancestors_blue=MST096-T,pass_over_blue=MST097-T,path_well_traveled_blue=MST098-T,preserve_tradition_blue=MST099-T,rising_sun_setting_moon_blue=MST100-T,stir_the_pot_blue=MST101-T,the_grain_that_tips_the_scale_blue=MST102-T,MST000_inner_chi_blue=MST400-T,MST010_inner_chi_blue=MST410-T,MST032_inner_chi_blue=MST432-T,MST053_inner_chi_blue=MST453-T,MST095_inner_chi_blue=MST495-T,MST096_inner_chi_blue=MST496-T,MST097_inner_chi_blue=MST497-T,MST098_inner_chi_blue=MST498-T,MST099_inner_chi_blue=MST499-T,MST100_inner_chi_blue=MST500-T,MST101_inner_chi_blue=MST501-T,MST102_inner_chi_blue=MST502-T,sigil_of_brilliance_yellow=ROS022-T,barkskin_of_the_millennium_tree=ROS028-T,earth_form_red=ROS036-T,rootbound_carapace_red=ROS042-T,rootbound_carapace_yellow=ROS043-T,rootbound_carapace_blue=ROS044-T,sigil_of_sanctuary_blue=ROS045-T,sigil_of_earth_blue=ROS070-T,lightning_form_red=ROS082-T,sigil_of_conductivity_blue=ROS088-T,flittering_charge_red=ROS089-T,flittering_charge_yellow=ROS090-T,flittering_charge_blue=ROS091-T,electrostatic_discharge_red=ROS110-T,electrostatic_discharge_yellow=ROS111-T,electrostatic_discharge_blue=ROS112-T,sigil_of_lightning_blue=ROS113-T,face_purgatory=ROS114-T,sigil_of_the_arknight_blue=ROS133-T,runerager_swarm_red=ROS149-T,deadwood_dirge_red=ROS155-T,sigil_of_deadwood_blue=ROS161-T,sigil_of_aether_blue=ROS168-T,glyph_overlay_red=ROS173-T,sigil_of_temporal_manipulation_blue=ROS182-T,sigil_of_forethought_blue=ROS210-T,arcanite_fortress=ROS211-T,ten_foot_tall_and_bulletproof_red=ROS217-T,call_to_the_grave_blue=ROS218-T,sigil_of_cycles_blue=ROS226-T,sigil_of_fyendal_blue=ROS230-T,chivalry_blue=TCC048-T,fyendals_fighting_spirit_red=UPR194-T,flic_flak_red=WTR092-T,timesnap_potion_blue=WTR172-T,sigil_of_solace_red=WTR173-T,command_and_conquer_red=ARC159-T,bare_fangs_red=EVR008-T,bare_fangs_yellow=EVR009-T,bare_fangs_blue=EVR010-T,swarming_gloomveil_red=EVR105-T,exude_confidence_red=MON245-T,erase_face_red=UPR187-T,rhinar_reckless_rampage=WTR001-T,rhinar=WTR002-T,head_jab_red=WTR098-T,head_jab_yellow=WTR099-T,head_jab_blue=WTR100-T,fyendals_spring_tunic=WTR150-T,crazy_brew_blue=WTR162-T,cracked_bauble_yellow=WTR224-T,ravenous_rabble_red=ARC191-T,twinning_blade_yellow=CRU082-T,galaxxi_black=MON155-T,blood_tribute_red=MON215-T,blood_tribute_yellow=MON216-T,blood_tribute_blue=MON217-T,blasmophet_the_soul_harvester=MON219-T,ursur_the_soul_reaper=MON220-T,channel_lake_frigid_blue=ELE146-T,earthlore_bounty=EVR020-T,invoke_dracona_optimai_red=UPR006-T,invoke_tomeltai_red=UPR007-T,invoke_dominia_red=UPR008-T,invoke_azvolai_red=UPR009-T,invoke_cromai_red=UPR010-T,invoke_kyloria_red=UPR011-T,invoke_miragai_red=UPR012-T,invoke_nekria_red=UPR013-T,invoke_ouvia_red=UPR014-T,invoke_themai_red=UPR015-T,invoke_vynserakai_red=UPR016-T,invoke_yendurai_red=UPR017-T,aether_ashwing=UPR042-T,ash=UPR043-T,rewind_blue=UPR169-T,dracona_optimai=UPR006-T,tomeltai=UPR007-T,dominia=UPR008-T,azvolai=UPR009-T,cromai=UPR010-T,kyloria=UPR011-T,miragai=UPR012-T,nekria=UPR013-T,ouvia=UPR014-T,themai=UPR015-T,vynserakai=UPR016-T,yendurai=UPR017-T,emperor_dracai_of_aesir=DYN001-T,rok=DYN005-T,seasoned_saviour=DYN026-T,blazen_yoroi=DYN045-T,crouching_tiger=DYN065-T,merciless_battleaxe=DYN068-T,hanabi_blaster=DYN088-T,construct_nitro_mechanoid_yellow=DYN092-T,regicide_blue=DYN121-T,sandscour_greatbow=DYN151-T,amethyst_tiara=DYN171-T,surgent_aethertide=DYN192-T,invoke_suraya_yellow=DYN212-T,celestial_kimono=DYN213-T,crown_of_dominion=DYN234-T,nitro_mechanoida=DYN492a-T,nitro_mechanoidb=DYN492b-T,nitro_mechanoidc=DYN492c-T,suraya_archangel_of_knowledge=DYN212-T,figment_of_erudition_yellow=DTD005-T,figment_of_judgment_yellow=DTD006-T,figment_of_protection_yellow=DTD007-T,figment_of_ravages_yellow=DTD008-T,figment_of_rebirth_yellow=DTD009-T,figment_of_tenacity_yellow=DTD010-T,figment_of_triumph_yellow=DTD011-T,figment_of_war_yellow=DTD012-T,levia_redeemed=DTD164-T,levia_redeemed=DTD164-T,suraya_archangel_of_erudition=DTD005-T,themis_archangel_of_judgment=DTD006-T,aegis_archangel_of_protection=DTD007-T,sekem_archangel_of_ravages=DTD008-T,avalon_archangel_of_rebirth=DTD009-T,metis_archangel_of_tenacity=DTD010-T,victoria_archangel_of_triumph=DTD011-T,bellona_archangel_of_war=DTD012-T,singularity_red=EVO010-T,evo_steel_soul_memory_blue=EVO026-T,evo_steel_soul_processor_blue=EVO027-T,evo_steel_soul_controller_blue=EVO028-T,evo_steel_soul_tower_blue=EVO029-T,evo_circuit_breaker_red=EVO030-T,evo_atom_breaker_red=EVO031-T,evo_face_breaker_red=EVO032-T,evo_mach_breaker_red=EVO033-T,teklovossen_the_mechropotent=EVO010-T,teklovossen_the_mechropotenta=EVO410a-T,teklovossen_the_mechropotentb=EVO410b-T,evo_steel_soul_memory_blue_equip=EVO426-T,evo_steel_soul_processor_blue_equip=EVO427-T,evo_steel_soul_controller_blue_equip=EVO428-T,evo_steel_soul_tower_blue_equip=EVO429-T,peace_of_mind_red=OUT231-T,in_the_swing_red=EVR063-T,wrecker_romp_blue=WTR031-T,electrify_blue=ELE200-T,sap_red=DYN206-T,sap_yellow=DYN207-T,sap_blue=DYN208-T,iris_of_reality=MON088-T,zero_to_sixty_red=ARC026-T,stir_the_wildwood_red=ELE082-T,fate_foreseen_red=ARC200-T,scar_for_a_scar_red=WTR191-T,boneyard_marauder_red=MON135-T,sand_cover_red=UPR039-T,oasis_respite_red=UPR221-T,energy_potion_blue=WTR170-T";
      //PvtVoid
      case "9408649": return "lightning_press_red=ELE183-T,rosetta_thorn=ELE222-T,mask_of_recurring_nightmares=MST004-T,sacred_art_undercurrent_desires_blue=MST010-T,sacred_art_immortal_lunar_shrine_blue=MST032-T,twelve_petal_kasaya=MST048-T,sacred_art_jade_tiger_domain_blue=MST053-T,traverse_the_universe=MST066-T,koi_blessed_kimono=MST068-T,a_drop_in_the_ocean_blue=MST095-T,homage_to_ancestors_blue=MST096-T,pass_over_blue=MST097-T,path_well_traveled_blue=MST098-T,preserve_tradition_blue=MST099-T,rising_sun_setting_moon_blue=MST100-T,stir_the_pot_blue=MST101-T,the_grain_that_tips_the_scale_blue=MST102-T,MST000_inner_chi_blue=MST400-T,MST010_inner_chi_blue=MST410-T,MST032_inner_chi_blue=MST432-T,MST053_inner_chi_blue=MST453-T,MST095_inner_chi_blue=MST495-T,MST096_inner_chi_blue=MST496-T,MST097_inner_chi_blue=MST497-T,MST098_inner_chi_blue=MST498-T,MST099_inner_chi_blue=MST499-T,MST100_inner_chi_blue=MST500-T,MST101_inner_chi_blue=MST501-T,MST102_inner_chi_blue=MST502-T,sigil_of_brilliance_yellow=ROS022-T,barkskin_of_the_millennium_tree=ROS028-T,earth_form_red=ROS036-T,rootbound_carapace_red=ROS042-T,rootbound_carapace_yellow=ROS043-T,rootbound_carapace_blue=ROS044-T,sigil_of_sanctuary_blue=ROS045-T,sigil_of_earth_blue=ROS070-T,lightning_form_red=ROS082-T,sigil_of_conductivity_blue=ROS088-T,flittering_charge_red=ROS089-T,flittering_charge_yellow=ROS090-T,flittering_charge_blue=ROS091-T,electrostatic_discharge_red=ROS110-T,electrostatic_discharge_yellow=ROS111-T,electrostatic_discharge_blue=ROS112-T,sigil_of_lightning_blue=ROS113-T,face_purgatory=ROS114-T,sigil_of_the_arknight_blue=ROS133-T,runerager_swarm_red=ROS149-T,deadwood_dirge_red=ROS155-T,sigil_of_deadwood_blue=ROS161-T,sigil_of_aether_blue=ROS168-T,glyph_overlay_red=ROS173-T,sigil_of_temporal_manipulation_blue=ROS182-T,sigil_of_forethought_blue=ROS210-T,arcanite_fortress=ROS211-T,ten_foot_tall_and_bulletproof_red=ROS217-T,call_to_the_grave_blue=ROS218-T,sigil_of_cycles_blue=ROS226-T,sigil_of_fyendal_blue=ROS230-T,chivalry_blue=TCC048-T,fyendals_fighting_spirit_red=UPR194-T,flic_flak_red=WTR092-T,timesnap_potion_blue=WTR172-T,rhinar_reckless_rampage=WTR001-T,rhinar=WTR002-T,head_jab_red=WTR098-T,head_jab_yellow=WTR099-T,head_jab_blue=WTR100-T,fyendals_spring_tunic=WTR150-T,crazy_brew_blue=WTR162-T,sigil_of_solace_red=WTR173-T,cracked_bauble_yellow=WTR224-T,ravenous_rabble_red=ARC191-T,twinning_blade_yellow=CRU082-T,galaxxi_black=MON155-T,blood_tribute_red=MON215-T,blood_tribute_yellow=MON216-T,blood_tribute_blue=MON217-T,blasmophet_the_soul_harvester=MON219-T,ursur_the_soul_reaper=MON220-T,channel_lake_frigid_blue=ELE146-T,earthlore_bounty=EVR020-T,invoke_dracona_optimai_red=UPR006-T,invoke_tomeltai_red=UPR007-T,invoke_dominia_red=UPR008-T,invoke_azvolai_red=UPR009-T,invoke_cromai_red=UPR010-T,invoke_kyloria_red=UPR011-T,invoke_miragai_red=UPR012-T,invoke_nekria_red=UPR013-T,invoke_ouvia_red=UPR014-T,invoke_themai_red=UPR015-T,invoke_vynserakai_red=UPR016-T,invoke_yendurai_red=UPR017-T,aether_ashwing=UPR042-T,ash=UPR043-T,rewind_blue=UPR169-T,dracona_optimai=UPR006-T,tomeltai=UPR007-T,dominia=UPR008-T,azvolai=UPR009-T,cromai=UPR010-T,kyloria=UPR011-T,miragai=UPR012-T,nekria=UPR013-T,ouvia=UPR014-T,themai=UPR015-T,vynserakai=UPR016-T,yendurai=UPR017-T,emperor_dracai_of_aesir=DYN001-T,rok=DYN005-T,seasoned_saviour=DYN026-T,blazen_yoroi=DYN045-T,crouching_tiger=DYN065-T,merciless_battleaxe=DYN068-T,hanabi_blaster=DYN088-T,construct_nitro_mechanoid_yellow=DYN092-T,regicide_blue=DYN121-T,sandscour_greatbow=DYN151-T,amethyst_tiara=DYN171-T,surgent_aethertide=DYN192-T,invoke_suraya_yellow=DYN212-T,celestial_kimono=DYN213-T,crown_of_dominion=DYN234-T,nitro_mechanoida=DYN492a-T,nitro_mechanoidb=DYN492b-T,nitro_mechanoidc=DYN492c-T,suraya_archangel_of_knowledge=DYN212-T,figment_of_erudition_yellow=DTD005-T,figment_of_judgment_yellow=DTD006-T,figment_of_protection_yellow=DTD007-T,figment_of_ravages_yellow=DTD008-T,figment_of_rebirth_yellow=DTD009-T,figment_of_tenacity_yellow=DTD010-T,figment_of_triumph_yellow=DTD011-T,figment_of_war_yellow=DTD012-T,levia_redeemed=DTD164-T,levia_redeemed=DTD164-T,suraya_archangel_of_erudition=DTD005-T,themis_archangel_of_judgment=DTD006-T,aegis_archangel_of_protection=DTD007-T,sekem_archangel_of_ravages=DTD008-T,avalon_archangel_of_rebirth=DTD009-T,metis_archangel_of_tenacity=DTD010-T,victoria_archangel_of_triumph=DTD011-T,bellona_archangel_of_war=DTD012-T,singularity_red=EVO010-T,evo_steel_soul_memory_blue=EVO026-T,evo_steel_soul_processor_blue=EVO027-T,evo_steel_soul_controller_blue=EVO028-T,evo_steel_soul_tower_blue=EVO029-T,evo_circuit_breaker_red=EVO030-T,evo_atom_breaker_red=EVO031-T,evo_face_breaker_red=EVO032-T,evo_mach_breaker_red=EVO033-T,teklovossen_the_mechropotent=EVO010-T,teklovossen_the_mechropotenta=EVO410a-T,teklovossen_the_mechropotentb=EVO410b-T,evo_steel_soul_memory_blue_equip=EVO426-T,evo_steel_soul_processor_blue_equip=EVO427-T,evo_steel_soul_controller_blue_equip=EVO428-T,evo_steel_soul_tower_blue_equip=EVO429-T,peace_of_mind_red=OUT231-T,in_the_swing_red=EVR063-T,wrecker_romp_blue=WTR031-T,electrify_blue=ELE200-T,sap_red=DYN206-T,sap_yellow=DYN207-T,sap_blue=DYN208-T,iris_of_reality=MON088-T,zero_to_sixty_red=ARC026-T,stir_the_wildwood_red=ELE082-T,fate_foreseen_red=ARC200-T,scar_for_a_scar_red=WTR191-T,boneyard_marauder_red=MON135-T,sand_cover_red=UPR039-T,oasis_respite_red=UPR221-T,energy_potion_blue=WTR170-T";
      //ManSant
      case "8955846": return "levia_shadowborn_abomination=MON119-ManSant,levia=MON120-ManSant,agility=HVY240-ManSant,might=TCC105-ManSant";
      //Brandao
      case "279086": return "dash_inventor_extraordinaire=ARC001-Brandao,kano_dracai_of_aether=ARC113-Brandao,kassai_cintari_sellsword=CRU077-Brandao,lexi_livewire=ELE031-Brandao,riptide_lurker_of_the_deep=OUT091-Brandao,dromai_ash_artist=UPR001-Brandao,bravo_showstopper=WTR038-Brandao,teklo_plasma_pistol=ARC003-Brandao,cintari_saber=CRU079-Brandao,cintari_saber=CRU079-Brandao,copper=CRU197-Brandao,frostbite=ELE111-Brandao,storm_of_sandikai=UPR003-Brandao,aether_ashwing=UPR042-Brandao,ash=UPR043-Brandao,anothos=WTR040-Brandao,seismic_surge=WTR075-Brandao,runechant=ARC112_Brandao,harmonized_kodachi=WTR078_Brandao,vynnset=DTD134_Brandao,flail_of_agony=DTD135_Brandao,maxx_the_hype_nitro=EVO004_Brandao,banksy=EVO006_Brandao,hyper_driver=EVO234_Brandao,katsu_the_wanderer=WTR076_Brandao,harmonized_kodachi=WTR078_Brandao,mandible_claw=CRU004_Brandao,mandible_claw=CRU004_Brandao,courage=DTD232_Brandao,rhinar_reckless_rampage=WTR001_Brandao,dorinthea_ironsong=WTR113_Brandao,dawnblade=WTR115_Brandao";
      //TeamTalishar
      case "14": return "embodiment_of_earth=ELE109-Promo,embodiment_of_lightning=ELE110-Promo";
      default: return "";
    }
  }

  public function CardBacks(): string
  {
    switch($this->value)
    {
      case "0": return "27";
      case "1": return "28";
      case "2": return "37";
      case "3": return "42";
      case "4": return "45";
      case "5": return "46";
      case "6": return "48";
      case "7": return "49";
      case "8": return "55";
      case "9": return "64";
      case "10": return "67";
      case "11": return "70";
      case "12": return "75";
      case "13": return "77,78";
      case "14": return "82,83";
      case "15": return "84";
      case "16": return "87";
      case "17": return "88";
      case "18": return "96";
      case "7198186": return "1,2,3,4,5,6,7,8,82,83";
      case "7579026": return "9";
      case "7329070": return "10,11,12,13,14,15,16";
      case "1787491": return "17,18,19,20";
      case "8997252": return "21,22,57,58,59,60,61,62";
      case "8955846": return "23,52";
      case "6839952": return "24";
      case "7285727": return "25";
      case "8635931": return "26";
      case "8736344": return "29";
      case "7593240": return "30";
      case "8458487": return "31";
      case "6996822": return "32";
      case "1919413": return "33";
      case "7733166": return "35";
      case "7009853": return "36";
      case "8338817": return "38";
      case "9361474": return "39";
      case "3828539": return "40";
      case "8951973": return "41";
      case "9370276": return "43";
      case "9404423": return "44";
      case "8040288": return "47";
      case "10147920": return "50";
      case "4006023": return "51";
      case "10811477": return "53";
      case "9851977": return "54";
      case "279086": return "56";
      case "11184392": return "63";
      case "11481720": return "65";
      case "9408649": return "1,2,3,4,5,6,7,8,82,83";
      case "9734205": return "68,69";
      case "9574942": return "71";
      case "11561507": return "72";
      case "9555916": return "73";
      case "8716783": return "74";
      case "11266104": return "76";
      case "12144126": return "79";
      case "12245100": return "80";
      case "12300349": return "81";
      case "10956423": return "85,86";
      case "12977197": return "89,90,91,92,93,94,95";
      case "13586904": return "97";
      default: return "";
    }
  }
}
