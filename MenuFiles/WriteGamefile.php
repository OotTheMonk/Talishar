<?php

  //$filename = "./Games/" . $gameName . "/GameFile.txt";
  //$gameFileWriteHandler = fopen($filename, "w");

  if(!function_exists("WriteGameFile"))
  {
    function WriteGameFile()
    {
      global $gameFileHandler;
      global $p1Data, $p2Data, $gameStatus, $format, $visibility, $firstPlayerChooser, $firstPlayer, $p1Key, $p2Key, $p1uid, $p2uid, $p1id, $p2id, $p1Karma, $p2Karma, $p1GreenRating, $p2GreenRating, $p1RedRating, $p2RedRating;
      global $gameDescription, $hostIP, $p1IsPatron, $p2IsPatron, $p1DeckLink, $p2DeckLink, $karmaRestriction, $p1PlayerRating, $p2PlayerRating;
      global $p1IsChallengeActive, $p2IsChallengeActive, $joinerIP;
      rewind($gameFileHandler);
      fwrite($gameFileHandler, implode(" ", $p1Data) . "\r\n");
      fwrite($gameFileHandler, implode(" ", $p2Data) . "\r\n");
      fwrite($gameFileHandler, $gameStatus . "\r\n");
      fwrite($gameFileHandler, $format . "\r\n");
      fwrite($gameFileHandler, $visibility . "\r\n");
      fwrite($gameFileHandler, $firstPlayerChooser . "\r\n");
      fwrite($gameFileHandler, $firstPlayer . "\r\n");
      fwrite($gameFileHandler, $p1Key . "\r\n");
      fwrite($gameFileHandler, $p2Key . "\r\n");
      fwrite($gameFileHandler, $p1uid . "\r\n");
      fwrite($gameFileHandler, $p2uid . "\r\n");
      fwrite($gameFileHandler, $p1id . "\r\n");
      fwrite($gameFileHandler, $p2id . "\r\n");
      fwrite($gameFileHandler, $p1Karma . "\r\n");
      fwrite($gameFileHandler, $p2Karma . "\r\n");
      fwrite($gameFileHandler, $p1GreenRating . "\r\n"); // Rating start of the game
      fwrite($gameFileHandler, $p2GreenRating . "\r\n"); // Rating start of the game
      fwrite($gameFileHandler, $p1RedRating . "\r\n"); // Rating start of the game
      fwrite($gameFileHandler, $p2RedRating . "\r\n"); // Rating start of the game
      fwrite($gameFileHandler, $p1PlayerRating . "\r\n");  //Player Rating - 0 = not rated, 1 = green (positive), 2 = red (negative)
      fwrite($gameFileHandler, $p2PlayerRating . "\r\n");  //Player Rating - 0 = not rated, 1 = green (positive), 2 = red (negative)
      fwrite($gameFileHandler, $gameDescription . "\r\n");
      fwrite($gameFileHandler, $hostIP . "\r\n");
      fwrite($gameFileHandler, $p1IsPatron . "\r\n");
      fwrite($gameFileHandler, $p2IsPatron . "\r\n");
      fwrite($gameFileHandler, $p1DeckLink . "\r\n");
      fwrite($gameFileHandler, $p2DeckLink . "\r\n");
      fwrite($gameFileHandler, $p1IsChallengeActive . "\r\n");
      fwrite($gameFileHandler, $p2IsChallengeActive . "\r\n");
      fwrite($gameFileHandler, $joinerIP . "\r\n");
      fwrite($gameFileHandler, $karmaRestriction . "\r\n");
      fclose($gameFileHandler);
    }
  }

?>
