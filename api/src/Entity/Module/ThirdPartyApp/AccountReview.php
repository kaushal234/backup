<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Entity\Directory\People;
use App\Entity\Module\ThirdPartyApp\Type\Light;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            // By pass security of resource to directly use voter in securityPostDenormalize
            security: 'true',
            securityPostDenormalize: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
        ),
        new Get(
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
        ),
        new GetCollection(
            uriTemplate: '/{thirdPartyAppId}/account_reviews',
            uriVariables: [
                'thirdPartyAppId' => new Link(
                    toProperty: 'thirdPartyApp',
                    fromClass: Light::class,
                ),
            ],
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', request)",
        ),
        new Post(
            uriTemplate: '/account_reviews/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => AccountReviewFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
            deserialize: false,
            name: 'upload_module_account_review_file',
        ),
        new Delete(
            uriTemplate: '/account_reviews/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: AccountReviewFile::class),
                'id' => new Link(fromClass: AccountReview::class),
            ],
            defaults: ['parentProperty' => 'accountReview', 'class' => AccountReviewFile::class],
            controller: DeleteController::class,
            name: 'delete_module_account_review_file',
        ),
        new Get(
            uriTemplate: '/account_reviews/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: AccountReviewFile::class),
                'id' => new Link(fromClass: AccountReview::class),
            ],
            defaults: ['parentProperty' => 'accountReview', 'class' => AccountReviewFile::class],
            controller: DownloadController::class,
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
            name: 'download_module_account_review_file',
        ),
    ],
    routePrefix: '/modules/third_party_app',
    normalizationContext: ['groups' => ['account_review:read', 'people_public', 'file', 'security_level']],
    denormalizationContext: ['groups' => ['account_review:write']],
    security: "is_granted('FEATURE_MODULE_WRITE')",
)]
#[ORM\Table(name: 'third_party_app_account_review')]
#[ApiFilter(SearchFilter::class, properties: ['thirdPartyApp'])]
class AccountReview
{
    #[ORM\ManyToOne(targetEntity: Light::class, inversedBy: 'accountReviews')]
    #[Groups(['account_review:read', 'account_review:write'])]
    #[Assert\NotNull]
    public Light $thirdPartyApp;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['account_review:read'])]
    public \DateTimeInterface $createdAt;

    // Number of Admin users at beginning of the review.
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?int $countStartAdminUsers = null;

    // Number of Admin users at the end of the review.
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?int $countEndAdminUsers = null;

    // Main reason of discrepancy for admins account
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?string $adminsComment = null;

    // Confirm the admin accounts are relevant, and there is no useless admin account granted.
    #[ORM\Column(type: 'boolean')]
    #[Groups(['account_review:read', 'account_review:write'])]
    public bool $adminAccountsConfirmed = false;

    // Number of actual users in third party app.
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?int $countAppUsers = null;

    // Number of Members (confirmed users) at beginning of the review
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?int $countStartMembers = null;

    // Number of confirmed accounts and actual accounts at the end of the review
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?int $countEndMembers = null;

    // Number of accounts disabled during the review
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?int $countDisabledAccounts = null;

    // Main reason of discrepancy for disabling account
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?string $disabledAccountsComment = null;

    // Number of accounts activated during the review
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?int $countEnabledAccounts = null;

    // Main reason of discrepancy for enabling account
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['account_review:read', 'account_review:write'])]
    public ?string $enabledAccountsComment = null;

    // Confirm that all accounts granted in the 3rd party App are relevant and in line with the confirmed users in the Intranet.
    #[ORM\Column(type: 'boolean')]
    #[Groups(['account_review:read', 'account_review:write'])]
    public bool $userAccountsConfirmed = false;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['account_review:read'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'create')]
    #[Groups(['account_review:read'])]
    private People $createdBy;

    #[ORM\OneToMany(mappedBy: 'accountReview', targetEntity: AccountReviewFile::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $files;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedBy(): People
    {
        return $this->createdBy;
    }

    public function setCreatedBy(People $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection<AccountReviewFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(AccountReviewFile $file): self
    {
        $file->setAccountReview($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(AccountReviewFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    #[Groups(['account_review:read'])]
    public function getMainFile(): ?AccountReviewFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }
}
