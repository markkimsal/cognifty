<?php
/*
 * This program is distributed under the following license in the hopes 
 * that it will be usefull but expressly disclaiming all implied or 
 * explicit warranties to the extent that the law allows. 
 *
 * You are not allowed to remove or bypass this page and subsequently 
 * re-distribute or re-install this application.  If you do you are in 
 * violation of this copyright license.
 *
 * License:
 * Copyright (c) 2007-2008 Mark Kimsal
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are 
 * met:
 *   Redistributions of source code must retain the above copyright notice, 
 *     this list of conditions and the following disclaimer.
 *   Redistributions in binary form must reproduce the above copyright 
 *     notice, this list of conditions and the following disclaimer in the 
 *     documentation and/or other materials provided with the distribution.
 *   Neither the name of the Metrofindings.com nor the names of its 
 *     contributors may be used to endorse or promote products derived from 
 *     this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 *  Represent an abstract identicon concept.
 *
 *  @abstract
 */
class Cgn_Vis_Identicon {

	public $id      = NULL;
	public $sizew   = -1;
	public $sizeh   = -1;
	public $error   = '';

	public $brush    = NULL;
	public $canvas   = NULL;

	public function __construct($id, $sw, $sh) {
		$this->id    = $id;
		$this->sizew = $sw;
		$this->sizeh = $sh;
	}

	public function setBrush($b) {
		$this->brush = $b;
	}

	public function setCanvas($c) {
		$this->canvas = $c;
	}


	/**
	 * Perform the necessary drawing steps
	 */
	public function buildIcon() {
	}


	/**
	 * Return the binary, or string representation of the icon
	 */
	public function getIcon() {
		return NULL;
	}

	/**
	 * Determine if the image is already created, based on the ID
	 *
	 * @return bool  true if the image exists 
	 */
	public function checkCache() {
		return Cgn_Vis_Identicon::realCheckCache($this->id);
	}

	/**
	 * Determine if the image is already created, based on the ID
	 *
	 * This method is static, the checkCache method is simply a 
	 * wrapper to allow for easier access through object context.
	 *
	 * @return bool  true if the image exists 
	 */
	public static function realCheckCache($id) {
		return false;
	}
}

/**
 *  A visual identicon based on colors and geometric shapes
 *
 */
class Cgn_Vis_Identicon_Geometry extends Cgn_Vis_Identicon {

	public $brush     = NULL;
	public $canvas    = NULL;

	public $blocks    = -1;
	public $blockSize = 80;
	public $sympad    = array();
	public $rotpad    = array();

	//pre-calculated dimensions
	public $qrt       = 0;
	public $hlf       = 0;
	public $dia       = 0;
	public $hfd       = 0;

	public function __construct($id, $sw, $sh) {
		parent::__construct($id, $sw, $sh);
		$this->setBrush(Cgn_Vis_Identicon_Brush::brushGd());
		try {
			$this->setCanvas(Cgn_Vis_Identicon_Canvas::canvasGd($sw, $sh));
		} catch (Exception $e) {
			$this->error = 'Cannot create GD Canvas';
		}

		$this->setBlocks(16);
		//pre-calculate a few common dimensions
		$this->qrt=$this->blockSize/4;
		$this->hlf=$this->blockSize/2;
		$this->dia=sqrt($this->hlf*$this->hlf+$this->hlf*$this->hlf);
		$this->hfd=$this->dia/2;
	}

	/**
	 * Create a symmetry pad which will indicate 
	 * where certain "blocks" are possitioned on our icon
	 */
	public function setBlocks($b) {
		$this->blocks = $b;

		//FIXME widescreen -  this relies on perfectly square icons
		$this->blockSize = $this->sizew / ceil(sqrt($b));

		//create the symmetry pad 
		//in a 4x4 (16 block) icon, the map will look like this
		/*
			-------------------------
			|  3  |  2  |  2  |  3  |
			-------------------------
			|  2  |  0  |  0  |  2  |
			-------------------------
			|  2  |  0  |  0  |  2  |
			-------------------------
			|  3  |  2  |  2  |  3  |
			-------------------------
		 */

		//FIXME widescreen -  this relies on perfectly square icons
		$onedimblock = sqrt($this->blocks);

		//clever algorithm thanks to
		// Scott Sherrill-Mix
		// http://scott.sherrillmix.com/blog/
		for ($x=0; $x < $onedimblock; $x++ ) {
		for ($y=0; $y < $onedimblock; $y++ ) {
			$index=array(floor(abs(($onedimblock-1)/2-$x)),floor(abs(($onedimblock-1)/2-$y)));
			sort($index);
			$index[1]*=ceil($onedimblock/2);
			$index=array_sum($index);

			$this->sympad[$x][$y] = $index;


		}
		}

		//create the rotation pad 
		//in a 4x4 (16 block) icon, the map will look like this
		/*
			-------------------------
			|  0  |  0  | 270 | 270 |
			-------------------------
			|  0  |  0  | 270 | 270 |
			-------------------------
			|  90 |  90 | 180 | 180 |
			-------------------------
			|  90 |  90 | 180 | 180 |
			-------------------------
		 */

		//clever algorithm thanks to
		// Scott Sherrill-Mix
		// http://scott.sherrillmix.com/blog/

		for ($i=0; $i < $onedimblock; $i++ ) {
		for ($j=0; $j < $onedimblock; $j++ ) {
			if (floor(($onedimblock-1)/2-$i)>=0&floor(($onedimblock-1)/2-$j)>=0&($j>=$i|$onedimblock%2==0)){
				$inversei=$onedimblock-1-$i;
				$inversej=$onedimblock-1-$j;
				$symmetrics=array(array($i,$j),array($inversej,$i),array($inversei,$inversej),array($j,$inversei));

				$this->rotpad[$i][$j]               = 0;
				$this->rotpad[$inversej][$i]        = 270;
				$this->rotpad[$inversei][$inversej] = 180;
				$this->rotpad[$j][$inversei]        = 90;
			}
		}
		}
	}

	public function randomGlyphMap() {
    	$glyphseed = hexdec(substr(sha1($this->id),0,8));
		srand($glyphseed);
		$onedimblock = sqrt($this->blocks);
		for($x = 0; $x < $onedimblock; $x++) {
		for($y = 0; $y < $onedimblock; $y++) {
			//FIXME, update when more blocks are ready
			$this->glyphMap[ $this->sympad[$x][$y] ] = rand(0,8);
		}
		}
	}


	public function randomStrokeColor() {
    	$colorseed = hexdec(substr(sha1($this->id),8,8));
		srand($colorseed);
		$r = rand(20, 240);
		$g = rand(20, 240);
		$b = rand(20, 240);
		$this->canvas->setStrokeColor(array($r, $g, $b));
	}

	public function buildIcon() {
		if ($this->checkCache()) {
			return;
		}

		$this->randomGlyphMap();
		$this->randomStrokeColor();

		//FIXME widescreen -  this relies on perfectly square icons
		$onedimblock = sqrt($this->blocks);
		for ($x=0; $x < $onedimblock; $x++ ) {
		for ($y=0; $y < $onedimblock; $y++ ) {

			$center = array($this->hlf+$this->blockSize*$x,$this->hlf+$this->blockSize*$y);
			$glyphIdx = $this->glyphMap[  $this->sympad[$x][$y] ];
			$rotation = $this->rotpad[$x][$y];
			$points = $this->getGlyphPoints($glyphIdx, $center, $rotation);
			$this->brush->paintPoly($points, $this->canvas);
		}}
//		$this->brush->paintLine(45, 65, $this->canvas);
	}

	/**
	 * Return an array of x1, x2, y1, y2 based on the 
	 * current canvas size.
	 */
	public function getGlyphPoints($idx=0, $center, $rotation=0) {
		//Point instructions are given in 
		//size-independant radians
		$c = $this->canvas;
		switch ($idx) {
		case 0:
			//0 rectangular half block
			$geom = array(
				array(90,$this->hlf),
				array(135,$this->dia),
				array(225,$this->dia),
				array(270,$this->hlf)
			);
			/*
		case 0:
			//0 side-trapezoid
			$geom=array(
				array(90,$this->hlf),
				array(135,$this->hfd),
				array(225,$this->hfd),
				array(270,$this->hlf));
			break;
			 */
		case 1:
			//1 full block
			$geom=array(
				array(45,$this->dia),
				array(135,$this->dia),
				array(225,$this->dia),
				array(315,$this->dia)
			);
			break;
		case 2:
			//2 lower-half triangle
			$geom=array(
				array(45,$this->dia),
				array(135,$this->dia),
				array(225,$this->dia)
			);
			break;

		case 3:
			//3 top triangle
			$geom =array(array(90,$this->hlf),array(225,$this->dia),array(315,$this->dia));
			break;

		case 4:
			//4 diamond
			$geom =array(array(0,$this->hlf),array(90,$this->hlf),array(180,$this->hlf),array(270,$this->hlf));
			break;

		case 5:
			//5 stretched diamond
			$geom =array(array(0,$this->hlf),array(135,$this->dia),array(270,$this->hlf),array(315,$this->dia));
			break;

		case 6:
			// 6 triple triangle
			$geom = array(
				array(array(0,$this->qrt),array(90,$this->hlf),array(180,$this->qrt)), 
				array(array(0,$this->qrt),array(315,$this->dia),array(270,$this->hlf)), 
				array(array(270,$this->hlf),array(180,$this->qrt),array(225,$this->dia))
			);
			break;
		case 7:
			//7 pointer
			$geom = array(array(0,$this->hlf),array(135,$this->dia),array(270,$this->hlf));
			break;
		case 8:
			//8 center square
			$geom = array(array(45,$this->hfd),array(135,$this->hfd),array(225,$this->hfd),array(315,$this->hfd));
			break;
		}

		if (is_array($geom[0][0])) { //then it's an array of points (two shapes)
			$multishape = array('multi' => true);
			foreach ($geom as $_g) 
			$multishape['points'][] =  $this->vector2Point($_g, $center, $rotation);
			return $multishape;
		} else {
			return $this->vector2Point($geom, $center, $rotation);
		}
	}

	/**
	 * Translate an array of [degree, length] pairs into x,y coords.
	 */
	public function vector2Point($geom, $center, $rotation=0) {
		$cx=$center[0];
		$cy=$center[1];
		$output = array();
		while($thispoint=array_pop($geom)){
			$degree = $thispoint[0];
			$len    = $thispoint[1];
			$y=round($cy+sin(deg2rad($degree+$rotation))*$len);
			$x=round($cx+cos(deg2rad($degree+$rotation))*$len);
			array_push($output,$x,$y);
		}
		return $output;

		return array(
			0, 0,
			64, 64,
			0, 64,
			64, 0,
		);
	}

	/**
	 * return a binary representation of the image
	 */
	public function getIcon() {
		if ($this->checkCache()) {
			return;
		}

		@ob_end_clean();
		ob_start();
		imagepng($this->canvas->gd);
		$bin = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($this->canvas->gd);
		return $bin;
	}
}

/**
 * An abstract class which holds drawning instructions for GD or SVG.
 */
class Cgn_Vis_Identicon_Brush {

	public static function brushGd() {
		return new Cgn_Vis_Identicon_Brush_Gd();
	}
}

/**
 * Paint with GD calls onto a GD Canvas
 */
class Cgn_Vis_Identicon_Brush_Gd extends Cgn_Vis_Identicon_Brush {

	public static function paintLine($angle, $len, $canvas) {
		imageline($canvas->gd, 0,0, 64, 64, $canvas->getStrokeColor());
	}

	public static function paintPoly($points, $canvas) {

		$pt = floor(count($points)/2);
		if (isset($points['multi'])) {
			foreach ($points['points'] as $_points) {
				$pt = floor(count($_points)/2);
				imagefilledpolygon($canvas->gd, $_points,  $pt, $canvas->getStrokeColor());
			}
		} else {
			imagefilledpolygon($canvas->gd, $points,  $pt, $canvas->getStrokeColor());
		}

		//imagepolygon($canvas->gd, $points, $pt,  $canvas->getStrokeColor());
	}
}

/**
 * A Canvas is passed to drawing operations of the Brush class.
 */
class Cgn_Vis_Identicon_Canvas {

	public $w = -1;
	public $h = -1;

	public $bgc = array(255, 255, 255);
	public $stc = array(0,     0,   0);
	public $flc = array(240, 250, 200);

	public static function canvasGd($w, $h) {
		return new Cgn_Vis_Identicon_Canvas_Gd($w, $h);
	}

}


/**
 * A canvas class which represents a GD image handle.
 */
class Cgn_Vis_Identicon_Canvas_Gd extends Cgn_Vis_Identicon_Canvas {

	public $gd = NULL;
	public $colors = array();


	public function __construct($w, $h) {
		$this->w = $w;
		$this->h = $h;

		if (!function_exists('imagecreatetruecolor')) {
			throw Exception ('no GD library found');
		}
		$this->gd = imagecreatetruecolor($this->w,$this->h);	
		$key = implode('-', $this->bgc);
		$this->colors[$key] = imagecolorallocate($this->gd, $this->bgc[0], $this->bgc[1], $this->bgc[2] );
		$this->colors['bgc'] = $this->colors[$key];

		$key = implode('-', $this->stc);
		$this->colors[$key] = imagecolorallocate($this->gd, $this->stc[0], $this->stc[1], $this->stc[2] );
		$this->colors['stc'] = $this->colors[$key];


		$key = implode('-', $this->flc);
		$this->colors[$key] = imagecolorallocate($this->gd, $this->flc[0], $this->flc[1], $this->flc[2] );
		$this->colors['flc'] = $this->colors[$key];

		//clear BG
		imagefilledrectangle($this->gd, 0, 0, $this->w, $this->h, $this->colors['bgc']);
	}


	/**
	 * return a color resource, make one if none found
	 */
	public function getStrokeColor() {
		return $this->colors['stc'];
	}

	/**
	 * return a color resource, make one if none found
	 */
	public function getBgColor() {
		return $this->colors['bgc'];
	}

	/**
	 * Set the stroke color 'stc'
	 *
	 * @return bool   TRUE if color was already allocated, FALSE if new
	 */
	public function setStrokeColor($rgb) {
		$key = implode('-', $rgb);
		if (isset($this->colors[$key])) {
			$this->colors['stc'] = $this->colors[$key];
			return TRUE;
		}

		$this->colors[$key] = imagecolorallocate($this->gd, $rgb[0], $rgb[1], $rgb[2] );
		$this->colors['stc'] = $this->colors[$key];
		return FALSE;
	}
}


/**
 * A canvas class which represents an SVG XML document.
 */
class Cgn_Vis_Identicon_Canvas_Svg extends Cgn_Vis_Identicon_Canvas {
}

//main 
if ( strpos( __FILE__, substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'],'/') )) !== FALSE) {
	$icon = new Cgn_Vis_Identicon_Geometry(md5('alskdjfls l23kj4l3o
        	e.com'), 128, 128);
	$icon->buildIcon();
//	var_dump($icon);
	header('Content-type: image/png');
	echo $icon->getIcon();
}
