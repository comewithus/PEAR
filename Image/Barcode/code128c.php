<?php

require_once 'Image/Barcode/code128.php';
require_once 'Image/Barcode.php';

/**
 * 
 * @author Yohan Boutin <yohan@comewithus.fr>
 * @since 15 May 2010
 * 
 *
 */
class Image_Barcode_code128c extends Image_Barcode_code128
{
	var $_type 				= 'code128c';
	var $_barcodeheight 	= 30;
	
	function getStartCode()
	{
		return '211232';
	}	

	function &draw($text, $imgtype = 'png')
    {

        // We start with the Code128 Start Code character.  We
        // initialize checksum to 104, rather than calculate it.
        // We then add the startcode to $allbars, the main string
        // containing the bar sizes for the entire code.
        $startcode	= $this->getStartCode();
        $checksum 	= 105;
        $allbars 	= $startcode;


        // Next, we read the $text string that was passed to the
        // method and for each character, we determine the bar
        // pattern and add it to the end of the $allbars string.
        // In addition, we continually add the character's value
        // to the checksum

        $bars = '';       
         
        //we separate the barecode with 2 chars
        $nbSequences = ceil(strlen($text) / 2);
        for($i = 1;$i<=$nbSequences;$i++) {
        	$start 	= ($i * 2) - 2;
        	$char 	= intval(substr( $text,$start,2));

            $checksum += ($char * $i);

            $bars = $this->code[$char];
            $allbars = $allbars . $bars;
        }


        // Then, Take the Mod 103 of the total to get the index
        // of the Code128 Check Character.  We get its bar
        // pattern and add it to $allbars in the next section.
        $checkdigit = $checksum % 103;
        $bars = $this->getNumCode($checkdigit);


        // Finally, we get the Stop Code pattern and put the
        // remaining pieces together.  We are left with the
        // string $allbars containing all of the bar widths
        // and can now think about writing it to the image.

        $stopcode = $this->getStopCode();
        $allbars = $allbars . $bars . $stopcode;

        //------------------------------------------------------//
        // Next, we will calculate the width of the resulting
        // bar code and size the image accordingly.

        // 10 Pixel "Quiet Zone" in front, and 10 Pixel
        // "Quiet Zone" at the end.
        $barcodewidth = 20;


        // We will read each of the characters (1,2,3,or 4) in
        // the $allbars string and add its width to the running
        // total $barcodewidth.  The height of the barcode is
        // calculated by taking the bar height plus the font height.

        for ($i=0; $i < strlen($allbars); ++$i) {
            $nval = $allbars[$i];
            $barcodewidth += ($nval * $this->_barwidth);
        }
        $barcodelongheight = (int) (imagefontheight($this->_font) / 2) + $this->_barcodeheight;


        // Then, we create the image, allocate the colors, and fill
        // the image with a nice, white background, ready for printing
        // our black bars and the text.

        $img = ImageCreate($barcodewidth, $barcodelongheight+ imagefontheight($this->_font)+1);
        $black = ImageColorAllocate($img, 0, 0, 0);
        $white = ImageColorAllocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);


        //------------------------------------------------------//
        // Finally, we write our text line centered across the
        // bottom and the bar patterns and display the image.


        // First, print the image, centered across the bottom.
        imagestring(
            $img,
            $this->_font,
            $barcodewidth / 2 - strlen($text) / 2 * (imagefontwidth($this->_font)),
            $this->_barcodeheight + imagefontheight($this->_font) / 2,
            $text,
            $black
        );

        // We set $xpos to 10 so we start bar printing after 
        // position 10 to simulate the 10 pixel "Quiet Zone"
        $xpos = 10;

        // We will now process each of the characters in the $allbars
        // array.  The number in each position is read and then alternating
        // black bars and spaces are drawn with the corresponding width.
        $bar = 1;
        for ($i=0; $i < strlen($allbars); ++$i) {
            $nval = $allbars[$i];
            $width = $nval * $this->_barwidth;

            if ($bar==1) {
                imagefilledrectangle($img, $xpos, 0, $xpos + $width-1, $barcodelongheight, $black);
                $xpos += $width;
                $bar = 0;
            } else {
                $xpos += $width;
                $bar = 1;
            }
        }

        return $img;
    } // function draw()
}