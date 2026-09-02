<?php

class imageBuilder extends imageBuilderCreate
{
	// CONSTANTS:
	const POSITION_NORMAL = 0;
	const POSITION_CENTER = 1;
	const POSITION_REVERSE = 2;
	const FLIP_XY = 100;
	const FLIP_X = 101;
	const FLIP_Y = 102;


	// PROPERTIES
	protected $_image, $_layers, $_cache;


	// MAGIC METHODS
	public function __construct($cache_name = NULL)
	{
		$this->_cache = false;

		$this->_layers = array();

		if (!empty($cache_name)) {
            $this->_setCache($cache_name);
        }

		if ($this->_cacheExists())
		{
			$this->_image = imageBuilderCreate::newFromFile($this->_cache);
		}
	}


	// CACHING:
	public function clearCache()
	{
		if (!$this->_cacheExists()) {
            return true;
        }

		return unlink($this->_cache);
	}

	protected function _setCache($cache_name)
	{
		if (!is_dir($dir = dirname($cache_name))) {
            throw new Exception('Cache dir does not exists');
        }

		if (!is_writable($dir)) {
            throw new Exception('Cache dir is not writable');
        }

		$this->_cache = self::_setFileName($cache_name);
	}

	protected function _cacheExists()
	{
        return $this->_cache && is_file($this->_cache) && is_readable($this->_cache) ? true : false;

    }

	protected function _doCache()
	{
		if ($this->_cacheExists()) {
            return;
        }

		imagepng($this->_image->getResource(), $this->_cache);
	}

	public function isCached()
	{
		return $this->_cacheExists();
	}


	// LAYERS
	public function addLayer(imageBuilderResource $obj)
	{
		return $this->_layers[] = $obj;
	}

	public function delLayer(imageBuilderResource $obj)
	{
		foreach ($this->_layers AS $key => $layer)
		{
			if ($layer->getId() == $obj->getId())
			{
				$layer->__destruct();
				unset($this->_layers[$key]);
				break;
			}
		}

		$this->_layers = array_values($this->_layers);

		return $this;
	}

	public function moveLayer(imageBuilderResource $obj, $new_index)
	{
		if ('reverse' === strtolower($new_index))
		{
			$this->_layers = array_reverse($this->_layers);

			return $this;
		}

		foreach ($this->_layers AS $idx => $layer)
		{
			if ($layer->getId() == $obj->getId())
			{
				$current_index = $idx;
				break;
			}
		}

		switch (strtolower($new_index))
		{
			case 'top':
			case 'beginning':
				$new_index = 0;
			break;
			case 'bottom':
			case 'end':
				$new_index = count($this->_layers) - 1;
			break;
			case 'up':
				$new_index = $current_index - 1;
			break;
			case 'down':
				$new_index = $current_index + 1;
			break;
			default:
				$new_index = $current_index + $new_index;
			break;
		}

		if ($new_index <> $current_index AND $new_index >= 0 AND $new_index < count($this->_layers))
		{
			$target = $this->_layers[$current_index];

			$slide = ($new_index - $current_index) / abs($new_index - $current_index);

			for ($idx = $current_index; $idx <> $new_index; $idx += $slide)
			{
				$this->_layers[$idx] = $this->_layers[$idx + $slide];
			}

			$this->_layers[$new_index] = $target;

			$this->_layers = array_values($this->_layers);
		}

		return $this;
	}


	// OUTPUT METHODS
	public function output()
	{
		$this->flatten();

		header('Content-type: image/png');

		return imagepng($this->_image->getResource());
	}

	public function save($filename)
	{
		$this->flatten();

		if (strpos($filename, '.') === false) {
            $filename = "$filename.png";
        }

		$ext = strtolower(substr($filename, strrpos($filename, '.')));

		$filename = self::_setFileName($filename);

		switch ($ext)
		{
			case 'jpg':
			case 'jpeg':
				return imagejpeg($this->_image->getResource(), $filename);
			case 'gif':
				return imagegif($this->_image->getResource(), $filename);
			case 'png':
				return imagepng($this->_image->getResource(), $filename);
			default:
				throw new Exception('Not a supported file image file type for save');
		}
	}

	public function getImage()
	{
		$this->flatten();

		return $this->_image;
	}

	public function flatten()
	{
		// Check if already flattened
		if (count($this->_layers) == 0)
		{
			return $this;
		}

		// Restack layers to process from bottom up
		$this->_layers = array_reverse($this->_layers);

		// Get finished size
		$width = $height = 1;

		foreach ($this->_layers AS $layer)
		{
			$x = $layer->getWidth();
			$y = $layer->getHeight();

			$w = $x + $layer->x;
			$h = $y + $layer->y;

			if ($w > $width) {
                $width = $w;
            }
			if ($h > $height) {
                $height = $h;
            }
		}

		// Generate fresh resource object
		$this->_image = imageBuilderCreate::newEmpty($width, $height);

		// Process layers
		foreach ($this->_layers AS $layer)
		{
			// Validate layer
			if (!$layer || !($layer instanceof imageBuilderResource)) {
                continue;
            }

			// Position layer
			$x = $layer->x;
			$y = $layer->y;

			if ($layer->posX == self::POSITION_CENTER) {
                $x = ($width - $layer->getWidth()) / 2;
            }
			if ($layer->posY == self::POSITION_CENTER) {
                $y = ($height - $layer->getHeight()) / 2;
            }

			if ($layer->posX == self::POSITION_REVERSE) {
                $x = $width - $layer->getWidth() - $layer->x;
            }
			if ($layer->posY == self::POSITION_REVERSE) {
                $y = $height - $layer->getHeight() - $layer->y;
            }

			// Merge layer
			imagecopy($this->_image->getResource(), $layer->getResource(), $x, $y, 0, 0, $layer->getWidth(), $layer->getHeight());

			// Close layer resource
			$this->delLayer($layer);
		}

		// Cache flattened image
		$this->_doCache();

		return $this;
	}


	// STATIC HELPER METHODS
	protected static function _calculateTextBox($font_size, $font_angle, $font_file, $text)
	{
		$box = imagettfbbox($font_size, $font_angle, $font_file, $text);
		if (!$box) {
            return false;
        }

		$min_x = min(array($box[0], $box[2], $box[4], $box[6]));
		$max_x = max(array($box[0], $box[2], $box[4], $box[6]));
		$min_y = min(array($box[1], $box[3], $box[5], $box[7]));
		$max_y = max(array($box[1], $box[3], $box[5], $box[7]));

		$width = $max_x - $min_x;
		$height = $max_y - $min_y;
		$left = abs($min_x) + $width;
		$top = abs($min_y) + $height;

		// To calculate the exact bounding box I write the text in a large image
		$img = imagecreatetruecolor($width << 2, $height << 2);
		$white = imagecolorallocate($img, 255, 255, 255);
		$black = imagecolorallocate($img, 0, 0, 0);
		imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);

		// For sure the text is completely in the image!
		imagettftext($img, $font_size, $font_angle, $left, $top, $white, $font_file, $text);

		// Start scanning (0=> black => empty)
		$rleft = $w4 = $width << 2;
		$rright = 0;
		$rbottom = 0;
		$rtop = $h4 = $height << 2;

		for ($x = 0; $x < $w4; $x++)
		{
			for ($y = 0; $y < $h4; $y++)
			{
				if (imagecolorat($img, $x, $y))
				{
					$rleft = min($rleft, $x);
					$rright = max($rright, $x);
					$rtop = min($rtop, $y);
					$rbottom = max($rbottom, $y);
				}
			}
		}

		// destroy img and serve the result
		imagedestroy($img);
		return array(
			"top" => $top - $rtop,
			"left" => $left - $rleft,
			"width" => $rright - $rleft + 1,
			"height" => $rbottom - $rtop + 1
		);
	}

	protected static function _setFileName($fileName): string
	{
		$dir = dirname($fileName);
		$file = basename($fileName);

		$file = strtolower($file);
		$file = str_replace(array('/','?','<','>',"\\",':','*','|','"'), '', $file);
		if (strpos($file, '.') === 0)
		{
			$file = substr($file, 1);
		}
		if (in_array($file, array('com1','com2','com3','com4','com5','com6','com7','com8','com9','lpt1','lpt2','lpt3','lpt4','lpt5','lpt6','lpt7','lpt8','lpt9','con','nul','prn')))
		{
			$file = '';
		}
		$file = substr($file, 0, 255);
		if (!strlen($file)) {
            throw new Exception('Invalid file name given');
        }

		return "{$dir}/{$file}";
	}

	public static function _parseRotate($angle)
	{
		$angle = abs($angle);

		if ($angle < 0) {
            $angle = 0;
        }
		if ($angle > 360) {
            $angle = 360;
        }

		return (float)$angle;
	}

	protected static function _rgb($hexidecimal)
	{
		$color = hexdec($hexidecimal);

		$r = ($color & 0xFF0000) >> 16;
		$g = ($color & 0x00FF00) >> 8;
		$b = ($color & 0x0000FF);

		return array('r' => $r, 'g' => $g, 'b' => $b);
	}

	protected static function _parseColorString($string)
	{
		$string = strtolower(trim((string)$string));

		if (strlen($string) == 3) {
            $string = $string[0].$string[0].$string[1].$string[1].$string[2].$string[2];
        }

		if (strlen($string) == 6 AND ctype_xdigit($string))
		{
			return self::_rgb($string);
		}

		return array('r' => 0, 'g' => 0, 'b' => 0);
	}

	protected static function _alphaCalc($percent)
	{
		$_alpha = round(abs(127 * ((float)$percent / 100) - 127));

		if ($_alpha < 0)
		{
			$_alpha = 0;
		}
		else if ($_alpha > 127)
		{
			$_alpha = 127;
		}

		return $_alpha;
	}
}


class imageBuilderCreate
{
	public static function newEmpty($width = 1, $height = 1)
	{
		$resource = imagecreatetruecolor($width, $height);

		imagesavealpha($resource, true);

		imagefill($resource, 0, 0, IMG_COLOR_TRANSPARENT);

		return self::_generate($resource);
	}

	public static function newFromFile($file)
	{
		return self::_generate(self::_createImageFromFile($file));
	}

	public static function newText($string, $size, $font, $angle = 0, $foreground_color = '000', $backgrund_color = IMG_COLOR_TRANSPARENT, $alpha = 100)
	{
		$box = imageBuilder::_calculateTextBox((float)$size, (float)$angle, $font, $string);

		$resource = imagecreatetruecolor($box['width'], $box['height']);

		if (IMG_COLOR_TRANSPARENT <> $backgrund_color)
		{
			$color_array = imageBuilder::_parseColorString($color);

			$backgrund_color = imagecolorallocatealpha($resource, $color_array['r'], $color_array['g'], $color_array['b'], imageBuilder::_alphaCalc($alpha));
		}

		imagefill($resource, 0, 0, $backgrund_color);

		if (IMG_COLOR_TRANSPARENT <> $foreground_color)
		{
			$color_array = imageBuilder::_parseColorString($foreground_color);

			$foreground_color = imagecolorallocatealpha($resource, $color_array['r'], $color_array['g'], $color_array['b'], imageBuilder::_alphaCalc($alpha));
		}

		imagettftext($resource, $size, $angle, $box['left'], $box['top'], $foreground_color, $font, $string);

		return self::_generate($resource);
	}

	protected static function _textWrap($string, $size, $font, $max_width)
	{
		$string = str_replace(array("\r\n", "\r", "\n"), "\n", $string);

		$lines = explode("\n", $string);

		if ($max_width <= 0) {
            return $lines;
        }

		$new_lines = array();

		foreach ($lines AS $line)
		{
			$words = explode(' ', $line);

			$str = array();

			foreach ($words AS $word)
			{
				$str[] = $word;

				if (count($str) == 1) {
                    continue;
                }

				$box = imagettfbbox($size, 0, $font, implode(' ', $str));

				if ($box[4] > $max_width)
				{
					$new_lines[] = implode(' ', $str);

					$str = array();
				}
			}

			$new_lines[] = implode(' ', $str);
		}

		return $new_lines;
	}


	public static function newMultiLineText($string, $size, $font, $align = 'left', $foreground_color = '000', $backgrund_color = IMG_COLOR_TRANSPARENT, $alpha = 100, $max_width = 0)
	{
		$lines = self::_textWrap($string, $size, $font, $max_width);

		$resource = imagecreatetruecolor(1, 1);

		if (IMG_COLOR_TRANSPARENT <> $backgrund_color)
		{
			$color_array = imageBuilder::_parseColorString($color);

			$backgrund_color = imagecolorallocatealpha($resource, $color_array['r'], $color_array['g'], $color_array['b'], imageBuilder::_alphaCalc($alpha));
		}

		imagefill($resource, 0, 0, $backgrund_color);

		if (IMG_COLOR_TRANSPARENT <> $foreground_color)
		{
			$color_array = imageBuilder::_parseColorString($foreground_color);

			$foreground_color = imagecolorallocatealpha($resource, $color_array['r'], $color_array['g'], $color_array['b'], imageBuilder::_alphaCalc($alpha));
		}

		$box = imagettfbbox($size, 0, $font, implode("\n", $lines));

		$width = $box[2] - $box[0];

		$height = $box[3] - $box[5];

		$line_height = $height / count($lines);

		foreach ($lines AS $idx => $line)
		{
			$box = imagettfbbox($size, 0, $font, $line);

			$w = $box[2] - $box[0];

			switch (strtolower($align))
			{
				case 'right':
					$x = $width - $w;
				break;
				case 'center':
					$x = ($width - $w) / 2;
				break;
				case 'left':
				default:
					$x = 0;
				break;
			}
			$x = ($width - $w) / 2;

			$y = $idx * $line_height;

			imagettftext($resource, $size, 0, $x, $y, $foreground_color, $font, $line);
		}

		return self::_generate($resource);
	}

	public static function newSolid($width, $height, $color = '000', $alpha = 100)
	{
		$resource = imagecreatetruecolor((int)$width, (int)$height);

		if (IMG_COLOR_TRANSPARENT <> $color)
		{
			$color_array = imageBuilder::_parseColorString($color);

			$color = imagecolorallocatealpha($resource, $color_array['r'], $color_array['g'], $color_array['b'], imageBuilder::_alphaCalc($alpha));
		}

		imagefill($resource, 0, 0, $color);

		return self::_generate($resource);
	}

	public static function newFromResource($resource)
	{
		return self::_generate($resource);
	}


	// HELPER METHODS
	protected static function _generate($resource)
	{
		return new imageBuilderResource($resource);
	}

	protected static function _createImageFromFile($file)
	{
		if (!file_exists($file) || !is_readable($file)) {
            throw new Exception('Unable to read image file \''.$file.'\'');
        }

        $info = getimagesize($file);
        switch ($info[2]) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($file);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($file);

            case IMAGETYPE_BMP:
                return imagecreatefrombmp($file);

            case IMAGETYPE_WBMP:
                return imagecreatefromwbmp($file);

            case IMAGETYPE_XBM:
                return imagecreatefromxbm($file);
            case IMAGETYPE_JPEG:
            default:
                return imagecreatefromjpeg($file);
        }
    }
}


Class imageBuilderResource
{
	// PROPERTIES
	protected $_resource, $_id, $_data;


	// MAGIC METHODS
	public function __construct($resource)
	{
		$this->_resource = $resource;

		$this->_id = uniqid();

		$this->_data = array(
			'x' => 0,
			'y' => 0,
			'posX' => imageBuilder::POSITION_NORMAL,
			'posY' => imageBuilder::POSITION_NORMAL
		);
	}

	public function __destruct()
	{
		if ($this->_resource) {
            imagedestroy($this->_resource);
        }

		$this->_resource = NULL;
	}

	public function __get($name)
	{
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
	}

	public function __toString()
	{
		return $this->getId();
	}


	// GENERAL METHODS
	public function getResource()
	{
		return $this->_resource;
	}

	public function getWidth()
	{
		return imagesx($this->_resource);
	}

	public function getHeight()
	{
		return imagesy($this->_resource);
	}

	public function getId()
	{
		return $this->_id;
	}


	// IMAGE MANIPULATION
	public function resize($maxWidth = NULL, $maxHeight = NULL)
	{
		if ((!isset($maxWidth) && !isset($maxHeight)) || (isset($maxWidth) && (int)$maxWidth <= 0) || (isset($maxHeight) && (int)$maxHeight <= 0)) {
            return $this;
        }

		$width = $this->getWidth();

		$height = $this->getHeight();

		if ($maxWidth !== NULL && $width > $maxWidth)
		{
			$ratio = $maxWidth / $width;
			$width = round($width * $ratio);
			$height = round($height * $ratio);
		}

		if ($maxHeight !== NULL && $height > $maxHeight)
		{
			$ratio = $maxHeight / $height;
			$width = round($width * $ratio);
			$height = round($height * $ratio);
		}

		$resource = imagecreatetruecolor($width, $height);

		imagefill($resource, 0, 0, IMG_COLOR_TRANSPARENT);

		imagecopyresampled($resource, $this->_resource, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

		imagedestroy($this->_resource);

		$this->_resource = NULL;

		$this->_resource = $resource;

		return $this;
	}

	public function crop($top = 0, $right = 0, $bottom = 0, $left = 0)
	{
		$top = ((int)$top < 0) ? 0 : (int)$top;
		$right = ((int)$right < 0) ? 0 : (int)$right;
		$bottom = ((int)$bottom < 0) ? 0 : (int)$bottom;
		$left = ((int)$left < 0) ? 0 : (int)$left;

		if (!$top && !$right && !$bottom && !$left) {
            return $this;
        }

		$width = $this->getWidth();

		$height = $this->getHeight();

		while ($top > $height) {
            $top--;
        }
		while ($right > $width) {
            $right--;
        }
		while ($bottom > $height - $top) {
            $bottom--;
        }
		while ($left > $width - $right) {
            $left--;
        }

		$width = $width - $right - $left;
		if ($width <= 0) {
            $width = 1;
        }

		$height = $height - $top - $bottom;
		if ($height <= 0) {
            $_height = 1;
        }

		$resource = imagecreatetruecolor($width, $height);

		imagefill($resource, 0, 0, IMG_COLOR_TRANSPARENT);

		imagecopy($resource, $this->_resource, 0, 0, $left, $top, $width, $height);

		imagedestroy($this->_resource);

		$this->_resource = NULL;

		$this->_resource = $resource;

		return $this;
	}

	public function flip($flipFlag = imageBuilder::FLIP_XY)
	{
		$width = $this->getWidth();

		$height = $this->getHeight();

		$src_x = 0;
		$src_y = 0;
		$src_width = $width;
		$src_height = $height;

		switch ($flipFlag)
		{
			case self::FLIP_XY:
				$src_x = $width - 1;
				$src_y = $height - 1;
				$src_width = -$width;
				$src_height = -$height;
			break;
			case self::FLIP_X:
				$src_x = $width - 1;
				$src_width = -$width;
			break;
			case self::FLIP_Y:
				$src_y = $height - 1;
				$src_height = -$height;
			break;
			default:
				return $this;
			break;
		}

		$resource = imagecreatetruecolor($width, $height);

		imagefill($resource, 0, 0, IMG_COLOR_TRANSPARENT);

		imagecopyresampled($resource, $this->_resource, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height);

		imagedestroy($this->_resource);

		$this->_resource = NULL;

		$this->_resource = $resource;

		return $this;
	}

	public function rotate($angle = 0)
	{
		$this->_resource = imagerotate($this->_resource, imageBuilder::_parseRotate($angle), IMG_COLOR_TRANSPARENT);

		return $this;
	}


	// POSITIONING (IN LAYER CONTEXT ONLY)
	public function positionX($position = 0, $positionFlag = imageBuilder::POSITION_NORMAL)
	{
		if ((int)$position < 0) {
            $position = 0;
        }

		if ($positionFlag == imageBuilder::POSITION_CENTER) {
            $position = 0;
        }

		$this->_data['x'] = (int)$position;
		$this->_data['posX'] = $positionFlag;

		return $this;
	}

	public function positionY($position = 0, $positionFlag = imageBuilder::POSITION_NORMAL)
	{
		if ((int)$position < 0) {
            $position = 0;
        }

		if ($positionFlag == imageBuilder::POSITION_CENTER) {
            $position = 0;
        }

		$this->_data['y'] = (int)$position;
		$this->_data['posY'] = $positionFlag;

		return $this;
	}
}

