<?php

function getLost() {
    Header('Location:index.html');
    die();
}

if (!isset($_GET['rgb'])) {
    getLost();
}

$comps = explode(',',$_GET['rgb']);
if (sizeof($comps) != 3) {
    getLost();
}

foreach ($comps as $comp) {
    if (!is_numeric($comp) || $comp < 0 || $comp > 255) {
        getLost();
    }
    else {
        $comp = (int)$comp;
    }
}

//----------REAL STUFF-----------//

$template_location = "template.xml";

// TINTS AND TONES

$lcomps = array();   //light
$dcomps = array();   //dark

$lfac = 0.5;    //lightness factor
$dfac = 0.4;    //darkness factor

for ($i = 0; $i < 3; $i++) {
    $lcomps[$i] = floor($comps[$i] + (255 - $comps[$i]) * $lfac);  //tint
    $dcomps[$i] = floor($comps[$i] * (1 - $dfac));                  //shade
}

// BORDER RESOURCES

$topimg = imagecreatetruecolor(1,28);
$cornerimg = imagecreatetruecolor(5,28);

$dcoltop = imagecolorallocate($topimg, $dcomps[0], $dcomps[1], $dcomps[2]);
$dcolcorner = imagecolorallocate($cornerimg, $dcomps[0], $dcomps[1], $dcomps[2]);

imagefill($topimg, 0, 0, $dcoltop);
imagefill($cornerimg, 0, 0, $dcolcorner);

ob_start(); //output buffering.
    imagepng($topimg); //This will normally output the image, but because of ob_start(), it won't.
    $topimgcontents = ob_get_contents(); //Instead, output above is saved to $contents
ob_end_clean(); //End the output buffer.

ob_start(); //output buffering.
    imagepng($cornerimg); //This will normally output the image, but because of ob_start(), it won't.
    $cornerimgcontents = ob_get_contents(); //Instead, output above is saved to $contents
ob_end_clean(); //End the output buffer.

$topb64 = base64_encode($topimgcontents);
$cornerb64 = base64_encode($cornerimgcontents);

// CHECK TICK

$tckimg = imagecreatetruecolor(7, 7);
$tickcol = imagecolorallocate($tckimg, $comps[0], $comps[1], $comps[2]);
$tickcollow = imagecolorallocatealpha($tckimg, $comps[0], $comps[1], $comps[2], 70);
$black = imagecolorallocate($tckimg, 0, 0, 0);
imagecolortransparent($tckimg, $black);

imagesetpixel($tckimg, 0, 3, $tickcol);
imagesetpixel($tckimg, 1, 4, $tickcol);
imagesetpixel($tckimg, 2, 5, $tickcol);
imagesetpixel($tckimg, 3, 4, $tickcol);
imagesetpixel($tckimg, 4, 3, $tickcol);
imagesetpixel($tckimg, 5, 2, $tickcol);
imagesetpixel($tckimg, 6, 1, $tickcol);

imagesetpixel($tckimg, 1, 3, $tickcollow);
imagesetpixel($tckimg, 2, 4, $tickcollow);
imagesetpixel($tckimg, 3, 3, $tickcollow);
imagesetpixel($tckimg, 4, 2, $tickcollow);
imagesetpixel($tckimg, 5, 1, $tickcollow);
imagesetpixel($tckimg, 6, 0, $tickcollow);

imagesetpixel($tckimg, 0, 4, $tickcollow);
imagesetpixel($tckimg, 1, 5, $tickcollow);
imagesetpixel($tckimg, 2, 6, $tickcollow);
imagesetpixel($tckimg, 3, 5, $tickcollow);
imagesetpixel($tckimg, 4, 4, $tickcollow);
imagesetpixel($tckimg, 5, 3, $tickcollow);
imagesetpixel($tckimg, 6, 2, $tickcollow);

ob_start(); //output buffering.
    imagepng($tckimg); //This will normally output the image, but because of ob_start(), it won't.
    $tckimgcontents = ob_get_contents(); //Instead, output above is saved to $contents
ob_end_clean(); //End the output buffer.

$tckb64 = base64_encode($tckimgcontents);

// TEMPLATE FORMATTING

$template = file_get_contents($template_location);
$template = str_replace("{0}",implode(',',$comps),$template);
$template = str_replace("{1}",implode(',',$lcomps),$template);
$template = str_replace("{2}",implode(',',$dcomps),$template);
$template = str_replace("{3}",$topb64,$template);
$template = str_replace("{4}",$cornerb64,$template);
$template = str_replace("{5}",$cornerb64,$template);
$template = str_replace("{7}",$tckb64,$template);

if (isset($_GET['v']) && $_GET['v'] == 2) {
    $template = str_replace("{6}","Bg-High",$template);
}
else {
    $template = str_replace("{6}","Highlight",$template);
}

// OUTPUT

header("Content-Type: text/xml");
header('Content-disposition: attachment; filename="DarkMOD.xml"');
echo $template;

?>
