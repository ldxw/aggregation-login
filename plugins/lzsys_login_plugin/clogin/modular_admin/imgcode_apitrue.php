<?php

$api_config = $plugin->api_config();
$addons_config = $plugin->addons_config();
$asset_data = $plugin->asset_data();
$post = $plugin->ide->php->post();
$get = $plugin->ide->php->get();
header("Content-Type: image/png");
$image = imagecreatetruecolor(100, 30);
$color = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 20, 20, $color);
$vcode = "";
for ($i = 0; $i < 4; $i++) {
	$fontSize = 8;
	$x = rand(5, 10) + $i * 100 / 4;
	$y = rand(5, 15);
	$data = "123456789";
	$string = substr($data, rand(0, strlen($data)), 1);
	$vcode .= $string;
	$color = imagecolorallocate($image, rand(0, 120), rand(0, 120), rand(0, 120));
	imagestring($image, $fontSize, $x, $y, $string, $color);
}
$_SESSION["imgcode"] = $vcode;
for ($i = 0; $i < 200; $i++) {
	$pointColor = imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(100, 255));
	imagesetpixel($image, rand(0, 100), rand(0, 30), $pointColor);
}
for ($i = 0; $i < 2; $i++) {
	$linePoint = imagecolorallocate($image, rand(150, 255), rand(150, 255), rand(150, 255));
	imageline($image, rand(10, 50), rand(10, 20), rand(80, 90), rand(15, 25), $linePoint);
}
imagepng($image);
imagedestroy($image);