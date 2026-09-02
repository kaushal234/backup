<?php

declare(strict_types=1);

namespace App\Entity\News;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'news_files')]
#[App\Loggable(owner: 'news', ownerRelation: 'files')]
class NewsFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\News\News', inversedBy: 'files')]
    private ?News $news = null;

    public function getNews(): News
    {
        return $this->news;
    }

    public function setNews(News $news): self
    {
        $this->news = $news;

        return $this;
    }
}
