<?php

declare(strict_types=1);

namespace App\DataProcessor;

trait RealClassNameTrait
{
    public function getRealClassName($data)
    {
        $className = $data::class;

        if (false !== $positionCg = mb_strrpos($className, '\\__CG__\\')) {
            return mb_substr($className, $positionCg + 8);
        }

        return $className;
    }
}
