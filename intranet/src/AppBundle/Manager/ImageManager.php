<?php

declare(strict_types=1);

namespace AppBundle\Manager;

class ImageManager
{
    /**
     * @param string $path
     * @param int    $width
     *
     * @return string
     */
    public function resize($path, $width)
    {
        $name = md5($path.$width);
        $finalpath = sys_get_temp_dir().'/'.$name;

        if (!file_exists($finalpath)) {
            $imageData = file_get_contents($path);

            [$sourceWidth, $sourceHeight] = getimagesizefromstring($imageData);
            $sourceImage = imagecreatefromstring($imageData);

            // New size
            $ratio = $width / $sourceWidth;
            $sampledWidth = (int) ceil($ratio * $sourceWidth);
            $sampledHeight = (int) ceil($ratio * $sourceHeight);

            // Thumb image
            $thumb = imagecreatetruecolor($sampledWidth, $sampledHeight);
            imagecopyresampled($thumb, $sourceImage,
                0, 0, 0, 0,
                $sampledWidth, $sampledHeight,
                $sourceWidth, $sourceHeight
            );

            // Output
            imagejpeg($thumb, $finalpath, 100);
        }

        return $finalpath;
    }
}
