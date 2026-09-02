<?php

declare(strict_types=1);

namespace App\Entity\Materials;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'evendors_news_files')]
class EvendorsNewsFile extends File
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write'])]
    protected bool $public = true;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Materials\EvendorsNews', inversedBy: 'files')]
    private ?EvendorsNews $evendorsNews = null;

    public function getEvendorsNews(): EvendorsNews
    {
        return $this->evendorsNews;
    }

    public function setEvendorsNews(EvendorsNews $news): self
    {
        $this->evendorsNews = $news;

        return $this;
    }
}
