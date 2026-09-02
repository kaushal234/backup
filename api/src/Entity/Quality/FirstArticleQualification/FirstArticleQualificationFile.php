<?php

declare(strict_types=1);

namespace App\Entity\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'first_article_qualifications_files')]
#[App\Loggable(owner: 'firstArticleQualification', ownerRelation: 'files')]
class FirstArticleQualificationFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualification', inversedBy: 'files')]
    private ?FirstArticleQualification $firstArticleQualification = null;

    public function getFirstArticleQualification(): FirstArticleQualification
    {
        return $this->firstArticleQualification;
    }

    /**
     * @return $this
     */
    public function setFirstArticleQualification(FirstArticleQualification $firstArticleQualification): self
    {
        $this->firstArticleQualification = $firstArticleQualification;

        return $this;
    }
}
