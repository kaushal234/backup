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
#[ORM\Table(name: 'third_party_app_account_review_files')]
#[App\Loggable(owner: 'accountReview', ownerRelation: 'files')]
class AccountReviewFile extends File
{
    #[ORM\ManyToOne(targetEntity: AccountReview::class, inversedBy: 'files')]
    private ?AccountReview $accountReview = null;

    public function getAccountReview(): ?AccountReview
    {
        return $this->accountReview;
    }

    public function setAccountReview(?AccountReview $accountReview): self
    {
        $this->accountReview = $accountReview;

        return $this;
    }
}
