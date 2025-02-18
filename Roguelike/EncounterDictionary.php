<?php

include "EncounterLogic.php";
include "CardRewardPools.php";
include "AdventurePools.php";

function EncounterDescription()
{
  $encounter = &GetZone(1, "Encounter");
  switch($encounter->encounterID)
  {
    case 001:
      return "Blackjack's Tavern is a lively space. You have been here many times before, and you come today with a purpose. What will you do?";
    case 002:
      return "A woman at a nearby table calls you over and asks you your name. You are in high spirits today, and give it freely.";
    case 003:
      return "You walk over to the bounty board. You see the bounty you are about to turn in, but you also see a few new ones. Which one catches your eye?";
    case 004:
      return "The bartender owes you a favor. Perhaps you should cash it in for some refreshments. The strength of the drink may make what's to come more difficult, though, so you choose carefully.";
    case 005:
      return "You approach the man in the corner. He greets you pleasantly. You have just finished a contract, which bounty are you removing from the board?";
    case 006:
      return "The man smiles and hands you your reward. You take it before making your way back out in search of your next bounty.";
    case 007:
      return "You found a campfire. Choose what you want to do.";
    case 8: //Shop event
      $myDQ = &GetZone(1, "DecisionQueue");
      if($myDQ[0] == "SHOP"){
        return "You come across a small village. You wander through the market, investigating the various wares. The village has many residents, and you note the location of a local healer, as well as an elderly urchin with outstretched palms.";
      }
      else { //This should just be the beggar/removedeck
        return "Thank you, traveller. Please, honor me by sitting and enjoying the scenery with me. I find this spot to be perfect for attuning to oneself.";
      }
    case 9:
      return GetCrossroadsDescription();
    case 11:
      return "Thank you for playing Blackjack's Tavern! We have a lot more in the works that we can't wait to show off. If you're interested in helping with future updates, join the Talishar Discord!";

    case 101:
      if($encounter->subphase == "BeforeFight") return "You're attacked by a Woottonhog.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Woottonhog.";
    case 102:
      if($encounter->subphase == "BeforeFight") return "While wandering through the woods, you're attacked by a mysterious and deadly looking creature.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Ravenous Rabble. There don't seem to be any other threats in these woods... at least not right now. You continue your trek.";
    case 103:
      if($encounter->subphase == "BeforeFight") return "You're attacked by a Barraging Brawnhide.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Barraging Brawnhide.";
    case 104:
      if($encounter->subphase == "BeforeFight") return "You're attacked by a Shock Striker.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Shock Striker.";
    case 105:
      if($encounter->subphase == "BeforeFight") return "You've finished the game (so far!). If you'd like to help out with adding new encounters/classes, check out our Discord! The code is open source and can be found here: https://github.com/Talishar/Talishar/tree/main/Roguelike";
      else if($encounter->subphase == "AfterFight") return "You defeated the group of bandits.";
    case 106:
      if($encounter->subphase == "BeforeFight") return "You turn to find another way, only to see someone has been following. They knock an arrow, ready to fight.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Quickshot Apprentice. His bow lies in pieces on the ground. ";
    case 107:
      if($encounter->subphase == "BeforeFight") return "You're attacked by a Cursed Scholar.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Rune Scholar.";
    case 108:
      if($encounter->subphase == "BeforeFight") return "You come upon Ira. Prepare to fight.";
      else if($encounter->subphase == "AfterFight") return "You defeated Ira.";
    case 113:
      if($encounter->subphase == "BeforeFight") return "You're attacked by a Cloaked Figure wielding two daggers.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Cloaked Figure.";
    case 114:
      if($encounter->subphase == "BeforeFight") return "You barely notice as a stranger raises a dagger behind your back.";
      else if($encounter->subphase == "AfterFight") return "You defended yourself, and defeated the assassin.";
    case 115:
      if($encounter->subphase == "BeforeFight") return "A graceful figure stands atop a spire some distance away. He remains there, as if asking you to make the first move.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Master of the Arts.";
    case 117:
      if($encounter->subphase == "BeforeFight") return "As night falls around you, you realize you aren't alone. A figure approaches, but does not draw their blade. Yet.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Master of the Arts.";
    case 118:
      if($encounter->subphase == "BeforeFight") return "As you travel, you encounter a boisterous traveler wearing leather armor with a green glimmer. \"Hail, bounty hunter. Would you like to spar?\"";
      else if($encounter->subphase == "AfterFight") return "\"Wow stranger, that was an exciting match. Thank you for the lessons learned!\"";
    case 119:
      if($encounter->subphase == "BeforeFight") return "As you cross the bridge, thunder cracks. A storm begins, and the winds nearly knock you into the chasm below. A figure runs towards you, as though dancing on the rain itself.";
      else if($encounter->subphase == "AfterFight") return "You defeated the Master of the Arts.";
    case 120:
      if($encounter->subphase == "BeforeFight") return "You emerge in the noticeably cleaner streets of Metrix. Just as you're emerging, a mailman sees you and panics, attempting to attack you.";
      else if($encounter->subphase == "AfterFight") return "You killed a poor mailman. You heartless monster! Oh well, no use dwelling on the past, now that you're out of the city it's time to move towards your objective.";
    case 121:
      if($encounter->subphase == "BeforeFight") return "You hear a loud bellow from the other side of the fallen tree. The tree rises, revealing a brute picking it up. He seems incredibly angry at the block in the road.";
      else if($encounter->subphase == "AfterFight") return "After you deliver a humbling smackdown, the brute calms down and continues on his way. He gives no apology for his outburst before.";
    case 122:
      if($encounter->subphase == "BeforeFight") return "As you drift off to sleep, a scream pierces through your skull. You have a visitor.";
      else if($encounter->subphase == "AfterFight") return "You banished the spirit.";
    case 123:
      if($encounter->subphase == "BeforeFight") return "As you come upon the mountain pass, a great voice booms, \"Stop, stranger! You are not welcome here!\"";
      else if($encounter->subphase == "AfterFight") return "You bested the great Guardian.";
    case 124:
      if($encounter->subphase == "BeforeFight") return "An arrow hits that water near you. As you look up, you see what looks to be an Arian Fisherman.";
      else if($encounter->subphase == "AfterFight") return "\"Whoah there friend! You should watch where you wade! I almost hit you there!\"";
    case 125:
      if($encounter->subphase == "BeforeFight") return "As you enter the cave, you find a great beast standing in front of a pile of treasures. You are not welcome here.";
      else if($encounter->subphase == "AfterFight") return "The beast lets out one last roar before toppling over. You've vanquished the greedy monster.";
    case 126:
      if($encounter->subphase == "BeforeFight") return "As you walk into a nearby shop, the door closes behind you. The shopkeeper flashes you a crooked grin";
      else if($encounter->subphase == "AfterFight") return "The shopkeeper turns into a pile of ash on the floor. You blink, and you find yourself standing in the burnt out husk of what was once a shop.";
    case 127:
      if($encounter->subphase == "BeforeFight") return "Partway across the lake, your ferry gets boarded by a feisty pirate. \"Empty yer wallets! Gimme yer gold!\"";
      else if($encounter->subphase == "AfterFight") return "You made sure the ferry made it to the other side safely, and defeated the pirate.";
    case 128:
      if($encounter->subphase == "BeforeFight") return "In the town, you see posters for a celebration tonight. You decide it would be fun to stay for the celebration.";
      else if($encounter->subphase == "AfterFight") return "As fireworks light up the sky, you can't help but feel at peace.";
    case 129:
      if($encounter->subphase == "BeforeFight") return "As you cross the bridge, a man bigger than a mountain yells a battlecry.";
      else if($encounter->subphase == "AfterFight") return "The defeated man falls. His eyes do not change, for nothing was ever behind his eyes.";
    case 130:
      if($encounter->subphase == "BeforeFight") return "As you travel, a wandering thug carrying a spiked club jumps out from behind a large boulder and ambushes you. His size is impressive, yet he seems limber... keep your guard up against him."; //TODO
      else if($encounter->subphase == "AfterFight") return "The man runs away, with the pieces of his club on the ground. You were too clever for him!";
    case 131:
      if($encounter->subphase == "BeforeFight") return "Despite keeping an eye on your surroundings, you're caught by surprise when a giant winged creature made of crackling lightning suddenly attacks you.";
      else if($encounter->subphase == "AfterFight") return "As you slay the creature, it falls to the ground, but leaves no trace of blood. Just before leaving, you turn back and see no body or evidence of your fight at all.";

    case 999: return "This text is not the default! This text means something is wrong!"; //Maybe $encounter->subphase is set to something weird? Maybe there's a typo?

    case 201: return "There was a battle here recently. There aren't any combatants, but there are bodies littering the ground, still dripping with blood.";
    case 202: return "You found a library.";
    case 203: return "You've stumbled on a city on the boundary between ice and lightning. You hear thunderous cracking; you can't tell which it is from. There's a tantalizing stream of energy that looks invigorating, but it's mixed with frost. You think you can time it right...";
    case 204: return "You stumble on a great forge, big enough for giants. The giant manning the forge comments on your flimsy armor. You can use this as an opportunity to practice some of the limited blacksmithing skills you have, or you can commission a piece from the Giant.";
    case 205: return "You enter a temple. There is an altar that reads, \"Offer of yourself and receive a bountiful blessing.\"";
    case 206:
      $health = &GetZone(1, "Health");
      if($health[0] > 1) return "A witch on the side of the road approaches you. 'No! I don't wish to fight you. I only wish to play a game.'";
      else return "A witch on the side of the road approaches you. 'No! I don't wish to fight you. I only wish to play a game. But it seems you have nothing to offer me, so I must take my leave.'";
    case 208: return "You meet a migrating trader, who holds up a small stone, but doesn't let you get a close look at it. \"Greetings Bounty Hunter, I have an amazing deal for you. Two gold pieces and this powerful stone can be yours.\"";
    case 210: return "The view is breathtaking, but the journey is precarious. You lose your footing and stumble, dropping your bag of coins over the edge.";
    case 211: return "The shop smells of smoke and flame. An incredibly short man gets on a box to look at you from beyond the counter.";
    case 212: return "Inside the dojo, a cloaked man stands on the far side of the room. \"Show me where your strength lies!\"";
    case 213: return "\"Hail, traveler. You look like you could use something powerful, and my pockets are awefully light at the moment. Care to trade?\"";
    case 214: return "\"Hello, traveler. I can see you have grown weary. Come, sit. You must have a great story to tell. Or perhaps you would like to hear one of mine? Maybe you just need some company.\"";
    case 215: return "The knight looks at you, smiles, and lowers his visor. \"Well friend, lets spar. Until you are tired, let us begin!\"";
    case 216: return "The lady with a radiant aura approaches you. She puts on a mask and suddenly she is you. Then, she puts on another mask and shifts into someone else entirely. \"Now, Which one do you prefer?\"";
    case 217: case 218: case 219: case 220: case 221: case 222: return "The chest is simple, but it should be easy enough to open.";
    case 223: case 224: case 225: case 226: case 227: case 228: return "The chest is ornate, elegant in design. It may be difficult, but it should be able to be opened.";
    case 229: return "Inside the tavern, there is a man in the corner rolling dice. \"Come on over here! I haven't lost yet!\"";
    case 230: return "You come across a small village. You wander through the market, investigating the various wares.";
    case 231: return "You are pulled closer to the shrine. The shrine speaks to you in a cool, twisted voice: \"Make an offering, or your soul is forfeit.\"";
    case 232: return "The mirror serves no purpose to be here. It's unsettling.";
    case 233: return "You visit your old friend's shack, only to see the weaponmaster's house burnt to a crisp. There may be some weapons inside, but it might be worth finding your old friend's body and laying them to rest.";
    case 234: return "You find a library twisted inside a tree. It may be worth searching through.";
    case 235: return "An old woman beckons you inside. She quickly seats you and sets a plate in front of you. The food looks gross and disgusting, as though it were cooked a year ago.";
    case 236: return "The sigil is beautiful where it sits.";
    case 237: return "The waters of the pool are clear and calm. It's as though the pool is waiting for you.";
    case 238: return "You know now that the pool was waiting for you, and it waits for you yet again, in a new place. You know it will wait for you wherever you should need it.";
    case 500: return "ERROR: There is currently a longstanding bug for the Rogulike Mode regarding the mini boss of the gamemode. We are currently unsure what causes it, and there are currently no active developers on the mode. To ensure you still get the best experience playing the gamemode, we have disabled the miniboss. Enjoy the rest of the mode, we should be back in time to continue making it the best it can be!";
    default: return "No encounter text.";
  }
}


function InitializeEncounter($player)
{
  $encounter = &GetZone($player, "Encounter");
  /*if($encounter->subphase == "ContinueLore")
  {
    InitializeLore($player);
    return;
  }*/
  //WriteFullEncounter();
  switch($encounter->encounterID)
  {
    case 001:
      AddDecisionQueue("BUTTONINPUT", $player, "Change_your_hero,Change_your_bounty,Change_your_difficulty,Begin_adventure");
      AddDecisionQueue("STARTADVENTURE", $player, "-");
      break;
    case 002:
      AddDecisionQueue("BUTTONINPUT", $player, "Dorinthea,Bravo,Lexi,Fai,Arakni");
      AddDecisionQueue("CHOOSEHERO", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "001-PickMode");
      break;
    case 003:
      AddDecisionQueue("BUTTONINPUT", $player, "Ira");
      AddDecisionQueue("CHOOSEADVENTURE", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "001-PickMode");
      break;
    case 004:
      AddDecisionQueue("BUTTONINPUT", $player, "Normal");
      AddDecisionQueue("CHOOSEDIFFICULTY", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "001-PickMode");
      break;
    case 005:
      $encounter->gold += 3;
      AddDecisionQueue("BUTTONINPUT", $player, GetBackgrounds($encounter->hero));
      AddDecisionQueue("BACKGROUND", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "006-PickMode");
      break;
    case 006:
      //$encounter->position = 7; //DON'T DELETE: I use this for easy hijacking into crossroad events to test crossroads
      AddDecisionQueue("CHOOSECARD", $player, GetRandomCards("Power,3"), "Power,3");
      //AddDecisionQueue("SETENCOUNTER", $player, "125-BeforeFight"); //DON'T DELETE: I use this for easy hijacking into the adventure to test new encounters
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 007:
      AddDecisionQueue("BUTTONINPUT", $player, "Rest,Reflect");
      AddDecisionQueue("CAMPFIRE", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 8:
      AddDecisionQueue("SHOP", $player, GetShop());
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 9:
      AddDecisionQueue("BUTTONINPUT", $player, GetNextEncounter());
      AddDecisionQueue("CROSSROADS", $player, "-");
      break;
    case 10:
      AddDecisionQueue("CHOOSECARD", $player, GetRandomCards("Power,3"), "Power,3");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;

    case 201:
      AddDecisionQueue("BUTTONINPUT", $player, "Loot,Pay_Respects");
      AddDecisionQueue("BATTLEFIELD", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 202:
      AddDecisionQueue("BUTTONINPUT", $player, "Search,Leave");
      AddDecisionQueue("LIBRARY", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 203:
      AddDecisionQueue("BUTTONINPUT", $player, "Enter_Stream,Leave");
      AddDecisionQueue("VOLTHAVEN", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 204:
      AddDecisionQueue("BUTTONINPUT", $player, "Spend_some_time_forging_equipment_for_yourself,Ask_the_blacksmith_to_make_you_a_piece_of_equipment,Leave");
      AddDecisionQueue("BLACKSMITH", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 205:
      AddDecisionQueue("BUTTONINPUT", $player, "Make_an_offering,Quietly_Pray,Leave");
      AddDecisionQueue("ENLIGHTENMENT", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 206:
      $health = &GetZone($player, "Health");
      if($health[0] > 1) AddDecisionQueue("BUTTONINPUT", $player, "Offer_her_1_life,Leave");
      else AddDecisionQueue("BUTTONINPUT", $player, "Leave");
      AddDecisionQueue("OLDHAG", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 207: //unused, entirely to test out removing cards
      AddDecisionQueue("REMOVEDECKCARD", $player, GetRandomDeckCard($player));
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 208:
      if($encounter->gold >= 2) AddDecisionQueue("BUTTONINPUT", $player, "Trade_1_gold_pieces_for_the_stone,Decline_his_offer_and_move_on");
      else AddDecisionQueue("BUTTONINPUT", $player, "Decline_his_offer_and_move_on");
      AddDecisionQueue("ROCKS", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 209:
      AddDecisionQueue("SHOP", $player, GetShop());
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 210:
      $health = &GetZone($player, "Health");
      if($health[0] >= 5) AddDecisionQueue("BUTTONINPUT", $player, "Its_best_to_leave_the_gold_behind,Attempt_to_retrieve_your_coins");
      else AddDecisionQueue("BUTTONINPUT", $player, "Its_best_to_leave_the_gold_behind");
      AddDecisionQueue("CLIFF", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 211:
      AddDecisionQueue("SHOP", $player, GetShop("Equipment,Equipment,Equipment,Equipment,Equipment,Equipment"), "Equipment,Equipment,Equipment,Equipment,Equipment,Equipment", "NoSubchoice");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 212:
      AddDecisionQueue("DUPLICATECARD", $player, GetRandomCards("Deck,4"), "Deck,4");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 213:
      AddDecisionQueue("SHOP", $player, GetShop("Power-4"), "Power-4", "NoSubchoice");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 214:
      AddDecisionQueue("BUTTONINPUT", $player, "Tell_him_your_story,Listen_to_his_story,Sit_peacefully,Leave");
      AddDecisionQueue("PEACEFULMONK", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 215:
      $health = &GetZone($player, "Health");
      if($health[0] >= 5) AddDecisionQueue("BUTTONINPUT", $player, "Spar_for_a_short_while,Spar_until_nightfall,Politely_decline");
      else AddDecisionQueue("BUTTONINPUT", $player, "Spar_for_a_short_while,Politely_decline");
      AddDecisionQueue("SPARRINGKNIGHT", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 216:
      AddDecisionQueue("BUTTONINPUT", $player, "Your_face,The_face_of_another");
      AddDecisionQueue("SHIYANASPEC", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 217:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_brown_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 218:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_white_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 219:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_blue_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 220:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_red_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 221:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_green_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 222:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_purple_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 223:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_ornate_brown_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 224:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_ornate_white_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 225:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_ornate_blue_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 226:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_ornate_red_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 227:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_ornate_green_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 228:
      AddDecisionQueue("BUTTONINPUT", $player, "Open_the_ornate_purple_chest,Leave");
      AddDecisionQueue("CHEST", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 229:
      AddDecisionQueue("BUTTONINPUT", $player, "Partake_in_some_entertainment,Leave");
      AddDecisionQueue("GAMBLER", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 230:
      AddDecisionQueue("SHOP", $player, GetShop("Class,Class,Talent,Equipment-Common,Equipment,Generic,Generic,Power-1"), "Class,Class,Talent,Equipment-Common,Equipment,Generic,Generic,Power-1", "NoSubchoice");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 231:
      AddDecisionQueue("REMOVEALLDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 232:
      AddDecisionQueue("BUTTONINPUT", $player, "Stare_into_the_mirror,Shatter_the_mirror,Leave");
      AddDecisionQueue("MIRROR", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 233:
      AddDecisionQueue("BUTTONINPUT", $player, "Take_what_you_can,Put_the_bodies_to_rest");
      AddDecisionQueue("WEAPONMASTER", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 234:
      AddDecisionQueue("BUTTONINPUT", $player, "Search_the_library,Donate_to_the_library,Leave");
      AddDecisionQueue("TWISTEDLIBRARY", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 235:
      $health = &GetZone($player, "Health");
      if($health[0] > 1) AddDecisionQueue("BUTTONINPUT", $player, "Take_a_bite,Save_some_for_later,Leave");
      else AddDecisionQueue("BUTTONINPUT", $player, "Save_some_for_later,Leave");
      AddDecisionQueue("COTTAGEWITCH", $player, "-", 1);
      break;
    case 236:
      AddDecisionQueue("BUTTONINPUT", $player, "Rest_at_the_sigil,Take_the_sigil");
      AddDecisionQueue("SIGILSOLACE", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 237:
      AddDecisionQueue("BUTTONINPUT", $player, "Cleanse_yourself_in_the_pool,Leave");
      AddDecisionQueue("CLEARPOOL", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 238:
      AddDecisionQueue("BUTTONINPUT", $player, "Let_the_waters_wash_over_you,Leave");
      AddDecisionQueue("CLEARPOOL", $player, "-");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    case 500:
      AddDecisionQueue("BUTTONINPUT", $player, "Leave");
      AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
      break;
    default: /*WriteLog("We Shouldn't Be Here");*/ break;
  }
}

/*function InitializeLore($player)
{
  $encounter = &GetZone(1, "Encounter");
  switch($encounter->encounterID)
  {

  }
}*/

function EncounterImage()
{
  $encounter = &GetZone(1, "Encounter");
  switch($encounter->encounterID)
  {
    case 001: case 002: case 003: case 004: case 005: case 006:
      return "ROGUELORE001_cropped.png";
    case 007:
      return "oasis_respite_red_cropped.png";
    case 8:
      $myDQ = &GetZone(1, "DecisionQueue");
      if($myDQ[0] == "SHOP"){
        return "hope_merchants_hood_cropped.png";
      }
      else { //This should just be the beggar/remove section of the shop
        return "zen_state_cropped.png";
      }
    case 9:
      return GetCrossroadsImage();
    case 10:
      return "talisman_of_recompense_yellow_cropped.png";

    case 101:
      return "stony_woottonhog_blue_cropped.png";
    case 102:
      return "ravenous_rabble_red_cropped.png";
    case 103:
      return "barraging_brawnhide_blue_cropped.png";
    case 104:
      return "shock_striker_blue_cropped.png";
    case 105:
      return "channel_mount_heroic_red_cropped.png";
    case 106:
      return "sic_em_shot_red_cropped.png";
    case 107:
      return "spellblade_strike_red_cropped.png";
    case 108:
      return "ira_crimson_haze_cropped.png";
    case 113:
      return "surging_strike_blue_cropped.png";
    case 114:
      return "slay_the_scholars_blue_cropped.png";
    case 115:
      return "crane_dance_red_cropped.png";
    case 117:
      return "break_tide_yellow_cropped.png";
    case 118:
      return "stir_the_wildwood_red_cropped.png";
    case 119:
      return "flood_of_force_yellow_cropped.png";
    case 120:
      return "combustible_courier_yellow_cropped.png";
    case 121:
      return "smash_with_big_tree_red_cropped.png";
    case 122:
      return "ghostly_visit_red_cropped.png";
    case 123:
      return "towering_titan_red_cropped.png";
    case 124:
      return "blessing_of_focus_red_cropped.png";
    case 125:
      return "clearing_bellow_blue_cropped.png";
    case 126:
      return "cash_in_yellow_cropped.png";
    case 127:
      return "freewheeling_renegades_red_cropped.png";
    case 128:
      return "water_glow_lanterns_red_cropped.png";
    case 129:
      return "cleave_red_cropped.png";
    case 130:
      return "brutal_assault_red_cropped.png";
    case 131:
      return "invoke_azvolai_red_cropped.png";

    case 201:
      return "scour_the_battlescape_red_cropped.png";
    case 202:
      return "sift_blue_cropped.png";
    case 203:
      return "pulse_of_volthaven_red_cropped.png";
    case 204:
      return "forged_for_war_yellow_cropped.png";
    case 205:
      return "seek_enlightenment_red_cropped.png";
    case 206:
      return "cash_in_yellow_cropped.png";
    case 208:
      return "trade_in_red_cropped.png";
    case 210:
      return "long_shot_red_cropped.png";
    case 211:
      return "visit_the_imperial_forge_red_cropped.png";
    case 212:
      return "yinti_yanti_red_cropped.png";
    case 213:
      return "helios_mitre_cropped.png";
    case 214:
      return "wax_on_red_cropped.png";
    case 215:
      return "en_garde_red_cropped.png";
    case 216:
      return "shiyana_diamond_gemini_cropped.png";
    case 217: case 218: case 219: case 220: case 221: case 222:
      return "powder_keg_blue_cropped.png";
    case 223: case 224: case 225: case 226: case 227: case 228:
      return "imperial_warhorn_red_cropped.png";
    case 229:
      return "gamblers_gloves_cropped.png";
    case 230:
      return "hope_merchants_hood_cropped.png";
    case 231:
      return "sigil_of_suffering_red_cropped.png";
    case 232:
      return "erase_face_red_cropped.png";
    case 233:
      return "scalding_rain_red_cropped.png";
    case 234:
      return "deep_rooted_evil_yellow_cropped.png";
    case 235:
      return "meat_and_greet_red_cropped.png";
    case 236:
      return "sigil_of_solace_red_cropped.png";
    case 237:
      return "read_the_ripples_red_cropped.png";
    case 238:
      return "read_the_ripples_red_cropped.png";

    case 500:
      return "clearing_bellow_blue_cropped.png";

    default: return "find_center_blue_cropped.png";
  }
}

function EncounterChoiceHeader(){
  $encounter = &GetZone(1, "Encounter");
  if($encounter->subphase = "ContineLore") return "";
  switch($encounter->encounterID){
    case 001:
      return "What will you do?";
    case 002:
      return "Choose a hero";
    case 003:
      return "Choose a finale";
    case 004:
      return "Choose a difficulty";
    case 005:
      return "Choose a Background";
    case 006:
      return "The man smiles and hands you your reward. You take it before making your way back out in search of your next bounty.";
    case 007:
      return "What will you do?";
    case 8:
      return "What purchases will you make?";
    case 9:
      return GetCrossroadsChoiceHeader();

    case 201:
      return "What will you do?";
    case 202:
      return "What will you do?";
    case 203:
      return "";
    case 204:
      return "What will you do?";
    case 205:
      return "What will you do?";
    case 206:
      return "What will you do?";
    case 208:
      return "Take the exchange?";
    case 210:
      return "What will you do?";
    case 211:
      return "What purchases will you make?";
    case 212:
      return "What will you show the man?";
    case 213:
      return "";
    case 214:
      return "What will you do?";
    case 215:
      return "What will you do?";
    case 216:
      return "Which do you prefer?";
    case 217: case 218: case 219: case 220: case 221: case 222:
      return "Will you open the chest?";
    case 223: case 224: case 225: case 226: case 227: case 228:
      return "Will you open the chest?";
    case 229:
      return "What will you do?";
    case 230:
      return "What purchases will you make?";
    case 231:
      return "What will you offer?";
    case 232:
      return "What will you do?";
    case 233:
      return "What will you do?";
    case 234:
      return "What will you do?";
    case 235:
      return "What will you do?";
    case 236:
      return "What will you do?";
    case 237:
      return "What will you do?";
    case 238:
      return "What will you do?";
    default: return "";
  }
}
?>
