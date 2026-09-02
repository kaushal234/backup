<?php

declare(strict_types=1);

namespace App\Traits\Quality\CalibratedTools;

use Symfony\Component\Serializer\Attribute\Groups;

trait CountToolsTrait
{
    /**
     * @return int
     */
    #[Groups(['location_areas', 'location_areas_detail', 'tool_type', 'tool_type_detail'])]
    public function getToolsCount()
    {
        return $this->getTools()->count();
    }
}
