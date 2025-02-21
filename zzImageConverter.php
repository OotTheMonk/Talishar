<?php

function CheckImage($setID, $cardID, $isDuplicate=false)
{
  $set = substr($setID, 0, 3);
  $number = substr($setID, 3);
  $filename = "./WebpImages/" . $cardID . ".webp";
  $filenameNew = "./New Cards/" . $cardID . ".webp";
  $cardImagesUploadedFolder = "../CardImages/media/uploaded/public/cardimages/english/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  $cardImagesMissingFolder = "../CardImages/media/missing/cardimages/english/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  if(!file_exists($filename) || !file_exists($cardImagesUploadedFolder))
  // if (!file_exists($cardImagesUploadedFolder))
  {
    //$imageURL = "https://fabrary.net/images/cards/" . $setID . ".webp";
    //$imageURL = "https://fabrary.net/images/cards/" . $setID . ".width-450.webp";
    if($isDuplicate) $imageURL= "https://d2h5owxb2ypf43.cloudfront.net/cards/" . $set . NormalizeCardBackID($number) . ".webp";
    else if($number >= 400 && $set == "UPR") $imageURL= "https://d2h5owxb2ypf43.cloudfront.net/cards/" . $set . NormalizeCardBackID($number) . "_A_Back.webp";
    else if($number >= 400 && $set == "DYN") $imageURL= "https://d2h5owxb2ypf43.cloudfront.net/cards/" . $set . NormalizeCardBackID($number) . "_Back.webp";
    else if($number >= 400) $imageURL= "https://d2h5owxb2ypf43.cloudfront.net/cards/" . $set . NormalizeCardBackID($number) . "_BACK.webp";
    else $imageURL= "https://d2h5owxb2ypf43.cloudfront.net/cards/" . $setID . ".webp";
    //$imageURL = "https://dhhim4ltzu1pj.cloudfront.net/media/images/" . $setID . "_yajPa8R.width-450.format-webp.webp";
    echo("Image for " . $cardID . " does not exist.<BR>");
    echo("Downloading image from $imageURL <BR>");
    $handler = fopen($filename, "w");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $imageURL);
    curl_setopt($ch, CURLOPT_FILE, $handler);
    curl_exec($ch);
    curl_close($ch);
    if(filesize($filename) < 10000) { unlink($filename); return; }
    if(file_exists($filename)) echo("Image for " . $cardID . " successfully retrieved.<BR>");
    if(file_exists($filename))
    {
      echo("Normalizing file size for " . $cardID . ".<BR>");
      $image = imagecreatefromwebp($filename);
      $image = imagescale($image, 450, 628);
      imagewebp($image, $filename);
      if(!file_exists($cardImagesUploadedFolder)) imagewebp($image, $cardImagesMissingFolder);
      if(!file_exists($filenameNew)) imagewebp($image, $filenameNew);
      // Free up memory
      imagedestroy($image);
    }
  }
  $concatFilename = "./concat/" . $cardID . ".webp";
  $cardSquaresUploadedFolder = "../CardImages/media/uploaded/public/cardsquares/english/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  $cardSquaresMissingFolder = "../CardImages/media/missing/cardsquares/english/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  if(!file_exists($concatFilename) || !file_exists($cardSquaresUploadedFolder))
  // if (!file_exists($cardSquaresUploadedFolder))
  {
    echo("Concat image for " . $cardID . " does not exist.<BR>");
    if(file_exists($filename))
    {
      echo("Attempting to convert image for " . $cardID . " to concat.<BR>");
      $image = imagecreatefromwebp($filename);
      $imageTop = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => 450, 'height' => 372]);
      $imageBottom = imagecrop($image, ['x' => 0, 'y' => 550, 'width' => 450, 'height' => 628]);

      $dest = imagecreatetruecolor(450, 450);
      imagecopy($dest, $imageTop, 0, 0, 0, 0, 450, 372);
      imagecopy($dest, $imageBottom, 0, 373, 0, 0, 450, 78);

      if(!file_exists($concatFilename)) imagewebp($dest, $concatFilename);
      if(!file_exists($cardSquaresUploadedFolder)) imagewebp($dest, $cardSquaresMissingFolder);
      // Free up memory
      imagedestroy($image);
      imagedestroy($dest);
      imagedestroy($imageTop);
      imagedestroy($imageBottom);
      if(file_exists($concatFilename)) echo("Image for " . $cardID . " successfully converted to concat.<BR>");
    }
  }
  $cropFilename = "./crops/" . $cardID . "_cropped.png";
  $cardCropsUploadedFolder = "../CardImages/media/uploaded/public/crops/" . $cardID . "_cropped.png"; // !! CardImages/ to be changed for your own folder name
  $cardCropsMissingFolder = "../CardImages/media/missing/crops/" . $cardID . "_cropped.png"; // !! CardImages/ to be changed for your own folder name
  if(!file_exists($cropFilename) || !file_exists($cardCropsUploadedFolder))
  // if (!file_exists($cardCropsUploadedFolder))
  {
    echo("Crop image for " . $cardID . " does not exist.<BR>");
    if(file_exists($filename))
    {
      echo("Attempting to convert image for " . $cardID . " to crops.<BR>");
      $image = imagecreatefromwebp($filename);
      $image = imagecrop($image, ['x' => 50, 'y' => 100, 'width' => 350, 'height' => 270]);
      if(!file_exists($cropFilename)) imagepng($image, $cropFilename);
      if(!file_exists($cardCropsUploadedFolder)) imagepng($image, $cardCropsMissingFolder);
      imagedestroy($image);
      if(file_exists($cropFilename)) echo("Image for " . $cardID . " successfully converted to crops.<BR>");
    }
  }
}

function NormalizeCardBackID($id)
{
  if($id < 400) return $id;
  $newId = $id - 400;
  $str = $newId;
  if($newId < 100) $str = "0" . $str;
  if($newId < 10) $str = "0" . $str;
  return $str;
}
