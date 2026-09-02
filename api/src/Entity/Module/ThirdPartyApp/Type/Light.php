<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp\Type;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\AccountReview;
use App\Entity\Module\ThirdPartyApp\SecurityLevel;
use App\Entity\Module\ThirdPartyApp\SecurityReview;
use App\Repository\Module\ThirdPartyApp\LightRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LightRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['module', 'people_public', 'people_photo', 'file:light', 'expose_legacy', 'department_list', 'type_assignee', 'third_party_app', 'business_unit', 'security_level', 'application']],
        ),
        new Post(
            security: "is_granted('FEATURE_MODULE_WRITE')",
        ),
        new Get(),
        new Put(
            security: "is_granted('FEATURE_MODULE_WRITE')",
        ),
    ],
    normalizationContext: ['groups' => ['module', 'module_detail', 'people_public', 'people_photo', 'file:light', 'expose_legacy', 'department_list', 'type_assignee', 'application', 'security_level', 'business_unit']],
    denormalizationContext: ['groups' => ['module_write']],
)]
class Light extends Module
{
    #[ORM\Column(type: 'integer')]
    public ?int $lastAccountReviewTaskId = null;

    #[ORM\Column(type: 'integer')]
    public ?int $lastSecurityReviewTaskId = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public bool $sso = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public bool $passwordPolicyApplied = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public bool $mfaUser = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public bool $mfaAdmin = false;

    // Security Audit Frequency is indicated in month
    #[ORM\Column(type: 'smallint')]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public int $securityReviewFrequency;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public ?\DateTimeInterface $securityReviewDateStart = null;

    // Account Audit Frequency is indicated in month
    #[ORM\Column(type: 'smallint')]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public int $accountReviewFrequency;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public ?\DateTimeInterface $accountReviewDateStart = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public ?int $availabilityClassification;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public ?int $integrityClassification;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    public ?int $confidentialityClassification;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'main_admin_id')]
    #[Groups(['module', 'module_write', 'module_light', 'people_public'])]
    protected ?People $mainAdmin = null;

    #[ORM\ManyToOne(targetEntity: SecurityLevel::class)]
    #[Groups(['module', 'module_write', 'module_light', 'module_detail'])]
    protected ?SecurityLevel $securityLevel = null;

    /**
     * @var Collection<SecurityReview>
     */
    #[ORM\OneToMany(targetEntity: SecurityReview::class, mappedBy: 'thirdPartyApp', cascade: ['all'])]
    private Collection $securityReviews;

    /**
     * @var Collection<AccountReview>
     */
    #[ORM\OneToMany(targetEntity: AccountReview::class, mappedBy: 'thirdPartyApp', cascade: ['all'])]
    private Collection $accountReviews;

    public function __construct()
    {
        parent::__construct();
        $this->securityReviews = new ArrayCollection();
        $this->accountReviews = new ArrayCollection();
    }

    public function getMainAdmin(): ?People
    {
        return $this->mainAdmin;
    }

    public function setMainAdmin(?People $mainAdmin): self
    {
        $this->mainAdmin = $mainAdmin;

        return $this;
    }

    public function getSecurityLevel(): ?SecurityLevel
    {
        return $this->securityLevel;
    }

    public function setSecurityLevel(?SecurityLevel $securityLevel): self
    {
        $this->securityLevel = $securityLevel;

        return $this;
    }

    /**
     * @return Collection<SecurityReview>
     */
    public function getSecurityReviews(): Collection
    {
        return $this->securityReviews;
    }

    public function setSecurityReviews(Collection $securityReviews): self
    {
        $this->securityReviews = $securityReviews;

        return $this;
    }

    public function addSecurityReview(SecurityReview $securityReview): self
    {
        if (!$this->securityReviews->contains($securityReview)) {
            $this->securityReviews->add($securityReview);
        }

        return $this;
    }

    public function removeSecurityReview(SecurityReview $securityReview): self
    {
        if ($this->securityReviews->contains($securityReview)) {
            $this->securityReviews->removeElement($securityReview);
        }

        return $this;
    }

    /**
     * @return Collection<AccountReview>
     */
    public function getAccountReviews(): Collection
    {
        return $this->accountReviews;
    }

    public function setAccountReviews(Collection $accountReviews): self
    {
        $this->accountReviews = $accountReviews;

        return $this;
    }

    public function addAccountReview(SecurityReview $accountReview): self
    {
        if (!$this->accountReviews->contains($accountReview)) {
            $this->accountReviews->add($accountReview);
        }

        return $this;
    }

    public function removeAccountReview(SecurityReview $accountReview): self
    {
        if ($this->accountReviews->contains($accountReview)) {
            $this->accountReviews->removeElement($accountReview);
        }

        return $this;
    }

    #[Groups(['module', 'module_write', 'module_light', 'people_public'])]
    public function isLastSecurityReviewOld(): bool
    {
        $lastSecurityReview = $this->getSecurityReviews()->last();

        if (!$lastSecurityReview) {
            return true;
        }

        $frequencyInMonths = $lastSecurityReview->thirdPartyApp->securityReviewFrequency;

        if (0 === $frequencyInMonths) {
            return true;
        }

        $nextReviewDate = (new \DateTime($lastSecurityReview->createdAt->format('Y-m-d H:i:s')))
            ->modify(\sprintf('+%d months', $frequencyInMonths));

        return $nextReviewDate < new \DateTime();
    }

    #[Groups(['module', 'module_write', 'module_light', 'people_public'])]
    public function isLastAccountReviewOld(): bool
    {
        $lastAccountReview = $this->getAccountReviews()->last();

        if (!$lastAccountReview) {
            return true;
        }

        $frequencyInMonths = $lastAccountReview->thirdPartyApp->accountReviewFrequency;

        if (0 === $frequencyInMonths) {
            return true;
        }

        $nextReviewDate = (new \DateTime($lastAccountReview->createdAt->format('Y-m-d H:i:s')))
            ->modify(\sprintf('+%d months', $frequencyInMonths));

        return $nextReviewDate < new \DateTime();
    }
}
