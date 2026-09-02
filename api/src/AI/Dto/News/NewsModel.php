<?php

declare(strict_types=1);

namespace App\AI\Dto\News;

use App\AI\Dto\Directory\DepartmentModel;
use App\AI\Dto\Directory\DivisionModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Directory\PremiseModel;

final readonly class NewsModel
{
    public function __construct(
        public string $title,
        public string $content,
        public string $contentShort,
        public ?string $bannerText,
        public bool $majorIncident,
        public \DateTimeInterface $date,
        public bool $banner,
        public ?CategoryModel $category,
        public ?PeopleModel $people,
        public ?DepartmentModel $department,
        public ?DivisionModel $division,
        public ?PremiseModel $premise,
    ) {
    }
}
