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
	public $debug   = FALSE;

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

	public function __construct($id, $sw, $sh, $b=16) {
		parent::__construct($id, $sw, $sh);
		$this->setBrush(Cgn_Vis_Identicon_Brush::brushGd());
		try {
			$this->setCanvas(Cgn_Vis_Identicon_Canvas::canvasGd($sw, $sh));
		} catch (Exception $e) {
			$this->error = 'Cannot create GD Canvas';
		}

		$this->blocks = $b;
		$this->setBlocks($b);
	}

	/**
	 * Create a symmetry pad which will indicate 
	 * where certain "blocks" are possitioned on our icon
	 */
	public function setBlocks($b) {
		$this->blocks = $b;

		//FIXME widescreen -  this relies on perfectly square icons
		$this->blockSize = $this->sizew / ceil(sqrt($b));

		//pre-calculate a few common dimensions
		$this->qrt=$this->blockSize/4;
		$this->hlf=$this->blockSize/2;
		$this->dia=sqrt($this->hlf*$this->hlf+$this->hlf*$this->hlf);
		$this->hfd=$this->dia/2;


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
    	$shapeseed = hexdec(substr(sha1($this->id),-8));
		srand($shapeseed);
		$shapeMax = rand(10, 30);
		$shapeMax = 30;

		$onedimblock = sqrt($this->blocks);
		for($x = 0; $x < $onedimblock; $x++) {
			for($y = 0; $y < $onedimblock; $y++) {
				//FIXME, update when more blocks are ready
				$this->glyphMap[ $this->sympad[$x][$y] ] = rand(0, $shapeMax);
			}
		}
	}


	public function randomStrokeColor() {
    	$colorseed = hexdec(substr(sha1($this->id),8,8));
		srand($colorseed);
		$r = rand(10, 230);
		$g = rand(10, 230);
		$b = rand(10, 230);
		$this->canvas->setStrokeColor(array($r, $g, $b));
	}

	public function getRandomRotation() { 
		if ($this->debug) { return 0; }
    	$rotate = hexdec(substr(sha1($this->id),0, 8));
		if ($rotate & 7 ) {
			return 90;
		}

		if ($rotate & 4 ) {
			return 270;
		}
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
			if (isset($points['ellipse'])) {
				$this->brush->paintEllipse($points, $this->canvas);
			} else {
				$this->brush->paintPoly($points, $this->canvas);
			}
		}
		}
//		$this->brush->paintLine(45, 65, $this->canvas);
	}

	public function buildDebugIcon() {

		$this->debug = TRUE;
		$this->setBlocks(64);
		//FIXME widescreen -  this relies on perfectly square icons
		$onedimblock = sqrt($this->blocks) - 1;
		$glyphIdx = 0;
		for ($y=0; $y < $onedimblock; $y++ ) {
		for ($x=0; $x < $onedimblock; $x++ ) {

			$center = array($this->hlf+$this->blockSize*$x + ($x*2) ,$this->hlf+$this->blockSize*$y +($y*2));
			$rotation = 0;
			$points = $this->getGlyphPoints($glyphIdx, $center, $rotation);
			$glyphIdx++;
			if (isset($points['ellipse'])) {
				$this->brush->paintEllipse($points, $this->canvas);
			} else {
				$this->brush->paintPoly($points, $this->canvas);
			}
		}
		}

		//draw red lines in between every glyph
		$this->canvas->setStrokeColor(array(200, 10, 10));
		for ($x=1; $x < $onedimblock; $x++ ) {
			$rotation = 0;
			$points = array();
			$points[0] = ($this->blockSize*$x + ($x*2));
			$points[1] = (0);
			$points[2] = ($this->blockSize*$x + ($x*2));
			$points[3] = ($this->sizeh);
			$this->brush->paintLine($points, $this->canvas);
		}

		for ($y=1; $y < $onedimblock; $y++ ) {
			$rotation = 0;
			$points = array();
			$points[0] = (0);
			$points[1] = ($this->blockSize*$y + (($y)*2)-1);
			$points[2] = ($this->sizew);
			$points[3] = ($this->blockSize*$y + (($y)*2)-1);
			$this->brush->paintLine($points, $this->canvas);
		}
	}


	/**
	 * Return an array of x1, x2, y1, y2 based on the 
	 * current canvas size.
	 */
	public function getGlyphPoints($idx=0, $center, $rotation=0) {
		//Point instructions are given in 
		//size-independant radians
		/*
		       degree chart
			------------------
			|225    270   315|
			|                |
			|180            0|
			|                |
			|135     90    45|
			------------------
		 */
		$rotatable=FALSE;
		switch ($idx) {
		case 0:
			//0 rectangular half block
			$geom = array(
				array(90,$this->hlf),
				array(135,$this->dia),
				array(225,$this->dia),
				array(270,$this->hlf)
			);
			$rotatable=TRUE;
			break;
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
			$rotatable=TRUE;
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
			$geom = array('multi'=>true, 'points'=>array(
				array(array(0,$this->qrt),array(90,$this->hlf),array(180,$this->qrt)), 
				array(array(0,$this->qrt),array(315,$this->dia),array(270,$this->hlf)), 
				array(array(270,$this->hlf),array(180,$this->qrt),array(225,$this->dia))
			));
			$rotatable=TRUE;
			break;
		case 7:
			//7 pointer
			$geom = array(array(0,$this->hlf),array(135,$this->dia),array(270,$this->hlf));
			$rotatable=TRUE;
			break;
		case 8:
			//8 center square
			$geom = array(array(45,$this->hfd),array(135,$this->hfd),array(225,$this->hfd),array(315,$this->hfd));
			break;

		case 9:
			//9 double triangle stairs
			$geom = array('multi'=>true, 'points'=>array(
				array(array(180,$this->hlf),array(225,$this->dia),array(0,0)), 
				array(array(45,$this->dia),array(90,$this->hlf),array(0,0))
			));
			$rotatable=TRUE;
			break;

		case 10:
			//10 notched square 
			$geom = 
			array(array(90,$this->hlf),array(135,$this->dia),array(180,$this->hlf),array(135, $this->hfd), array(0,0));
			$rotatable=TRUE;
			break;

		case 11:
			//11 quarter triangle out
			$geom = array(array(0,$this->hlf),array(180,$this->hlf),array(270,$this->hlf));
			break;

		case 12:
			//12 quarter triangle in
			$geom = 
			array(array(315,$this->dia),array(225,$this->dia),array(0,0));
			break;

		case 13:
			//13 eighth triangle in
			$geom = 
			array(array(90,$this->hlf),array(180,$this->hlf),array(0,0));
			break;


		case 14:
			//14 eighth triangle out
			$geom = array(array(90,$this->hlf),array(135,$this->dia),array(180,$this->hlf));
			break;

		case 15:
			//15 double corner square
			$geom = array('multi'=>true, 'points'=>array(
				array(array(90,$this->hlf),array(135,$this->dia),array(180,$this->hlf),array(0,0)), 
				array(array(0,$this->hlf),array(315,$this->dia),array(270,$this->hlf),array(0,0))
			));
			break;

		case 16:
			//16 double quarter triangle in

			$geom = array('multi'=>true, 'points'=>array(
				array(array(315,$this->dia),array(225,$this->dia),array(0,0)), 
				array(array(45,$this->dia),array(135,$this->dia),array(0,0))
			));
			break;

		case 17:
			//17 tall quarter triangle
			$geom = array(array(90,$this->hlf),array(135,$this->dia),array(225,$this->dia));
			break;

		case 18:
			//18 double tall quarter triangle
			$geom = array('multi'=>true, 'points'=>array(
				array(array(90,$this->hlf),array(135,$this->dia),array(225,$this->dia)), 
				array(array(45,$this->dia),array(90,$this->hlf),array(270,$this->hlf))
			));
			break;

		case 19://21 triple triangle diagonal
			$geom = array('multi'=>true, 'points'=>array(
				array(array(180,$this->hlf),array(225,$this->dia),array(0,0)), 
				array(array(45,$this->dia),array(90,$this->hlf),array(0,0)), 
				array(array(0,$this->hlf),array(0,0),array(270,$this->hlf))
			));
			$rotatable=TRUE;
			break;

		case 20:
			//22 double triangle flat
			$geom = array('multi'=>true, 'points'=>array(
				array(array(0,$this->qrt),array(315,$this->dia),array(270,$this->hlf)), 
				array(array(270,$this->hlf),array(180,$this->qrt),array(225,$this->dia))
			));
			$rotatable=TRUE;
			break;


		case 21:
			//23 opposite 8th triangles
			$geom = array('multi'=>true, 'points'=>array(
				array(array(0,$this->qrt),array(45,$this->dia),array(315,$this->dia)), 
				array(array(180,$this->qrt),array(135,$this->dia),array(225,$this->dia))
			));
			$rotatable=TRUE;
			break;

		case 22:
			//24 opposite 8th triangles + diamond
			$geom = array('multi'=>true, 'points'=>array(
				array(array(0,$this->qrt),array(45,$this->dia),array(315,$this->dia)), 
				array(array(180,$this->qrt),array(135,$this->dia),array(225,$this->dia)), 
				array(array(180,$this->qrt),array(90,$this->hlf),array(0,$this->qrt),array(270,$this->hlf))
			));
			$rotatable=TRUE;
			break;

		case 23:
			//23 double cirlce with hole
			$geom = array('ellipse'=>true, 
				'points'=> array(
					array(
					'cc'=>array(array(270,$this->qrt)),
					'w'=>$this->hlf,
					'h'=>$this->hlf,
					'cuth'=>$this->qrt,
					'cutw'=>$this->qrt
					),

					array(
					'cc'=>array(array(90,$this->qrt)),
					'w'=>$this->hlf,
					'h'=>$this->hlf,

					'cuth'=>$this->qrt,
					'cutw'=>$this->qrt
					),

				)
			);
			break;

		case 24:
			//24 REW Triangles
			$geom = array('multi'=>TRUE, 'points'=>array(
				array(array(45,$this->dia),array(315,$this->dia),array(0,0)), 
				array(array(180,$this->hlf),array(270,$this->hlf),array(90,$this->hlf))
			));
			$rotatable=TRUE;
			break;

		case 25:
			//25 FF Triangles
			$geom = array('multi'=>TRUE, 'points'=>array(
				array(array(0,$this->hlf),array(270,$this->hlf),array(90,$this->hlf)),
				array(array(0,0),array(135,$this->dia),array(225,$this->dia))
			));
			$rotatable=TRUE;
			break;

		case 26:
			//26 4 opposite 8th triangles (forms an X)
			$geom = array('multi'=>TRUE, 'points'=>array(
				array(array(0,$this->qrt),array(45,$this->dia),array(315,$this->dia)), 
				array(array(180,$this->qrt),array(135,$this->dia),array(225,$this->dia)), 
				array(array(270,$this->qrt),array(225,$this->dia),array(315,$this->dia)),
				array(array(90,$this->qrt),array(135,$this->dia),array(45,$this->dia))
			));
			break;

		case 27:
			//27 4 opposite 8th triangles with tiny diamond
			$geom = array('multi'=>TRUE, 'points'=>array(
				array(array(0,$this->qrt),array(45,$this->dia),array(315,$this->dia)), 
				array(array(180,$this->qrt),array(135,$this->dia),array(225,$this->dia)), 
				array(array(270,$this->qrt),array(225,$this->dia),array(315,$this->dia)),
				array(array(90,$this->qrt),array(135,$this->dia),array(45,$this->dia)),
				array(array(0,$this->qrt),array(90,$this->qrt),array(180,$this->qrt),array(270,$this->qrt))
			));
			break;

		case 28:
			//28 2 opposite corner triangles
			$geom = array('multi'=>TRUE, 'points'=>array(
				array(array(225,$this->dia),array(270,$this->hlf),array(180,$this->hlf)), 
				array(array(45,$this->dia),array(90,$this->hlf),array(0,$this->hlf)), 
			));
			$rotatable=TRUE;
		break;


		case 29:
			//29 diagonal double cirlce with hole
			$geom = array('ellipse'=>true, 
				'points'=> array(
					array(
					'cc'=>array(array(135,$this->hfd)),
					'w'=>$this->hlf,
					'h'=>$this->hlf,
					'cuth'=>$this->qrt,
					'cutw'=>$this->qrt
					),

					array(
					'cc'=>array(array(315,$this->hfd)),
					'w'=>$this->hlf,
					'h'=>$this->hlf,
					'cuth'=>$this->qrt,
					'cutw'=>$this->qrt
					),

				)
			);
			break;

		case 30:
			//30 double triangle down, right
			$geom = array('multi'=>true, 'points'=>array(
				array(array(0,0),array(330,$this->qrt*2),array(270,$this->hlf)), 
				array(array(270,$this->hlf),array(180,$this->qrt),array(225,$this->dia))
			));
			$rotatable=TRUE;
			break;


		case 99:
			//9 double triangle diagonal
			$geom = 0;
			break;

		case 99:
			//9 double triangle diagonal
			$geom = 0;
			break;

		default:
			$geom = array();
		}

		if ($rotatable) {
			$rotation += $this->getRandomRotation();
		}

		if (isset($geom['multi']) && is_array($geom['points'][0])) { //then it's an array of points (two shapes)
			$multishape = array();
			$multishape['multi'] = TRUE;
			foreach ($geom['points'] as $_g) 
			$multishape['points'][] =  $this->vector2Point($_g, $center, $rotation);

			return $multishape;
		} else if (isset($geom['ellipse']) && is_array($geom['points'][0])) {
			$multishape = array();
			$multishape['ellipse'] = TRUE;
			foreach ($geom['points'] as $_idx => $_g) {
				$multishape['points'][$_idx]['cc'] =  $this->vector2Point($_g['cc'], $center, $rotation);
				$multishape['points'][$_idx]['w'] =  $_g['w'];
				$multishape['points'][$_idx]['h'] =  $_g['h'];
				if (isset($_g['cutw'])) $multishape['points'][$_idx]['cutw'] =  $_g['cutw'];
				if (isset($_g['cuth'])) $multishape['points'][$_idx]['cuth'] =  $_g['cuth'];
			}
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

	public $paintShadow = false;
	public $offsetShadow = 1;

	public static function brushGd() {
		return new Cgn_Vis_Identicon_Brush_Gd();
	}
}

/**
 * Paint with GD calls onto a GD Canvas
 */
class Cgn_Vis_Identicon_Brush_Gd extends Cgn_Vis_Identicon_Brush {

	public function paintLine($points, $canvas) {
		$x1 = $points[0];
		$y1 = $points[1];
		$x2 = $points[2];
		$y2 = $points[3];
		imageline($canvas->gd, $x1, $y1, $x2, $y2, $canvas->getStrokeColor());
	}

	public function paintPoly($points, $canvas) {

		$pt = floor(count($points)/2);
		if (isset($points['multi'])) {
			foreach ($points['points'] as $_points) {
				$pt = floor(count($_points)/2);
				if ($pt < 3) { continue; }

				if ($this->paintShadow) {
					$shadow = array();
					foreach ($_points as $_k =>$_p) {
						$shadow[$_k] = $_p + ceil($this->offsetShadow);
					}
					imagefilledpolygon($canvas->gd, $shadow,  $pt, $canvas->getShadowColor());
				}

				imagefilledpolygon($canvas->gd, $_points,  $pt, $canvas->getStrokeColor());
			}
		} else {
			if ($pt < 3) { return; }

			if ($this->paintShadow) {
				$shadow = array();
				foreach ($points as $_k =>$_p) {
					$shadow[$_k] = $_p + ceil($this->offsetShadow);
				}
				imagefilledpolygon($canvas->gd, $shadow,  $pt, $canvas->getShadowColor());
			}

			imagefilledpolygon($canvas->gd, $points,  $pt, $canvas->getStrokeColor());
		}

		//imagepolygon($canvas->gd, $points, $pt,  $canvas->getStrokeColor());
	}

	public function paintEllipse($shapes, $canvas) {
		foreach ($shapes['points'] as $_points) {
			$oldStroke = -1;
			if ($this->paintShadow) {
				imagefilledellipse($canvas->gd, $_points['cc'][0]+$this->offsetShadow, 
					$_points['cc'][1]+$this->offsetShadow, 
					$_points['w'], $_points['h'],   $canvas->getShadowColor());
			}

			imagefilledellipse($canvas->gd, $_points['cc'][0], $_points['cc'][1], $_points['w'], $_points['h'],   $canvas->getStrokeColor());

			if (isset($_points['cutw'])) {
				imagefilledellipse($canvas->gd, $_points['cc'][0], $_points['cc'][1], 
					$_points['cutw'], $_points['cuth'],   $canvas->getBgColor());

				if ($this->paintShadow) {
					if (function_exists('imageantialias'))
						imageantialias( $canvas->gd , true );
					for($qs=0; $qs< $this->offsetShadow; $qs+=.2) {
					imagearc($canvas->gd, $_points['cc'][0] + $qs, 
						$_points['cc'][1] + $qs, 
						$_points['cutw'], $_points['cuth'],  160+($qs*14), 290-($qs*14), $canvas->getShadowColor());

					/*
					imagearc($canvas->gd, $_points['cc'][0]+$this->offsetShadow, 
						$_points['cc'][1]+$this->offsetShadow, 
						$_points['cutw'], $_points['cuth'],  190, 260, $canvas->getShadowColor());
					 */
					}
				}
				if (function_exists('imageantialias'))
					imageantialias( $canvas->gd , false );

			}
		}
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
	public $swc = array(95,   95,  95);

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

		$key = implode('-', $this->swc);
		$this->colors[$key] = imagecolorallocate($this->gd, $this->swc[0], $this->swc[1], $this->swc[2] );
		$this->colors['swc'] = $this->colors[$key];


		//clear BG
		imagefilledrectangle($this->gd, 0, 0, $this->w, $this->h, $this->colors['bgc']);
	}


	/**
	 * return a color resource
	 */
	public function getStrokeColor() {
		return $this->colors['stc'];
	}

	/**
	 * return a color resource
	 */
	public function getShadowColor() {
		return $this->colors['swc'];
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
if ( strpos( __FILE__, substr($_SERVER['PHP_SELF'], -1 *strrpos($_SERVER['PHP_SELF'],'/') )) !== FALSE) {
	$blueSeed= md5('alskdjfls l23kj4l3oe.com');
	$redSeed= md5('affs3o');
	$greySeed= md5('2034lkj lkj0 2/k q/a#?@294');
	$purpleSeed= md5('203n!@#4lkj lkj0 2/k q/a#?@294');
	$icon = new Cgn_Vis_Identicon_Geometry(md5(microtime(1)), 128, 128, 36);
//	$icon = new Cgn_Vis_Identicon_Geometry($blueSeed, 128, 128, 36);
	$icon->buildIcon();
//	$icon->buildDebugIcon();
	header('Content-type: image/png');
	echo $icon->getIcon();
}
