<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'third_party_app_security_review_files')]
#[App\Loggable(owner: 'securityReview', ownerRelation: 'files')]
class SecurityReviewFile extends File
{
    #[ORM\ManyToOne(targetEntity: SecurityReview::class, inversedBy: 'files')]
    private ?SecurityReview $securityReview = null;

    public function getSecurityReview(): ?SecurityReview
    {
        return $this->securityReview;
    }

    public function setSecurityReview(?SecurityReview $securityReview): self
    {
        $this->securityReview = $securityReview;

        return $this;
    }
}
