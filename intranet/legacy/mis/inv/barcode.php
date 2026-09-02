<?php
//include_once("Image/Barcode/upca.php");
include_once("Image/Barcode.php");
//$bar = new Image_Barcode_upca();
Image_Barcode::draw(str_pad($text, 8, "0", STR_PAD_LEFT),'Code39');
//Image_Barcode::draw($text,'Code39');
//Image_Barcode_upca::draw(str_pad($text, 14, "0", STR_PAD_LEFT));
?>