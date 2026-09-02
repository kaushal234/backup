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
            uriTemplate: '/{thirdPartyAppId}/security_reviews',
            uriVariables: [
                'thirdPartyAppId' => new Link(
                    toProperty: 'thirdPartyApp',
                    fromClass: Light::class,
                ),
            ],
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', request)",
        ),
        new Post(
            uriTemplate: '/security_reviews/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => SecurityReviewFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
            deserialize: false,
            name: 'upload_module_security_review_file',
        ),
        new Delete(
            uriTemplate: '/security_reviews/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: SecurityReviewFile::class),
                'id' => new Link(fromClass: SecurityReview::class),
            ],
            defaults: ['parentProperty' => 'securityReview', 'class' => SecurityReviewFile::class],
            controller: DeleteController::class,
            name: 'delete_module_security_review_file',
        ),
        new Get(
            uriTemplate: '/security_reviews/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: SecurityReviewFile::class),
                'id' => new Link(fromClass: SecurityReview::class),
            ],
            defaults: ['parentProperty' => 'securityReview', 'class' => SecurityReviewFile::class],
            controller: DownloadController::class,
            security: "is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object.thirdPartyApp)",
            name: 'download_module_security_review_file',
        ),
    ],
    routePrefix: '/modules/third_party_app',
    normalizationContext: ['groups' => ['security_review:read', 'people_public', 'file', 'security_level']],
    denormalizationContext: ['groups' => ['security_review:write']],
    security: "is_granted('FEATURE_MODULE_WRITE')",
)]
#[ORM\Table(name: 'third_party_app_security_review')]
#[ApiFilter(SearchFilter::class, properties: ['thirdPartyApp'])]
class SecurityReview
{
    #[ORM\ManyToOne(targetEntity: Light::class, inversedBy: 'securityReviews')]
    #[Groups(['security_review:read', 'security_review:write'])]
    #[Assert\NotNull]
    public Light $thirdPartyApp;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['security_review:read'])]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['security_review:read', 'security_review:write'])]
    public ?string $securityLevelComment = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['security_review:read', 'security_review:write'])]
    public bool $isPasswordPolicyApplied = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['security_review:read', 'security_review:write'])]
    public bool $isMFAAdminApplied = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['security_review:read', 'security_review:write'])]
    public bool $isMFAUserApplied = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['security_review:read', 'security_review:write'])]
    public ?string $comment;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['security_review:read'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'create')]
    #[Groups(['security_review:read'])]
    private People $createdBy;

    #[ORM\ManyToOne(targetEntity: SecurityLevel::class)]
    #[Groups(['security_review:read', 'security_review:write'])]
    private ?SecurityLevel $securityLevel = null;

    #[ORM\OneToMany(mappedBy: 'securityReview', targetEntity: SecurityReviewFile::class, cascade: ['persist'], orphanRemoval: true)]
    #[Assert\Count(max: 1)]
    private Collection $files;

    #[Groups(['security_review:read', 'security_review:write'])]
    public function getSecurityLevel(): ?SecurityLevel
    {
        return $this->securityLevel;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setSecurityLevel(?SecurityLevel $securityLevel): self
    {
        $this->securityLevel = $securityLevel;

        return $this;
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
     * @return Collection<SecurityReviewFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(SecurityReviewFile $file): self
    {
        $file->setSecurityReview($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(SecurityReviewFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    #[Groups(['security_review:read'])]
    public function getMainFile(): ?SecurityReviewFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }
}
