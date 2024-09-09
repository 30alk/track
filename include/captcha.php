<?php

	@session_start();
	$width = 131;
	$height = 36;
	$font_size = 20;
    $font =$_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/novin-track-order/assets/fonts/font.ttf';
	$font = realpath($font);
	$chars_length = 5;

	$captcha_characters = '1234567890';

	$image = imagecreatetruecolor($width, $height);
	$bg_color = imagecolorallocate($image, 12, 180, 10);
	$font_color = imagecolorallocate($image, 205, 245, 255);
	imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

	//background vert-line
	$vert_line = round($width/5);
	$color = imagecolorallocate($image, 205, 215, 140);
	for($i=0; $i < $vert_line; $i++) {
		imageline($image, -30*$i, $height, 40*$i, 10, $color);
	}

	$xw = ($width/$chars_length);
	$x = 0;
	$font_gap = $xw/2-$font_size/2;
	$digit = '';
	for($i = 0; $i < $chars_length; $i++) {
		$letter = $captcha_characters[rand(0, strlen($captcha_characters)-1)];
		$digit .= $letter;
		if ($i == 0) {
			$x = 0;
		}else {
			$x = $xw*$i;
		}
		imagettftext($image, $font_size, rand(-20,20), $x+$font_gap, rand(22, $height-5), $font_color, $font, $letter);
	}

	// record token in session variable
	$_SESSION['novin-code'] = strtolower($digit);

	// display image
	header('Content-Type: image/png');
	imagepng($image);
	imagedestroy($image);
?>
