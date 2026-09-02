<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use App\Repository\Support\ManualDocumentFileRepository;
use App\Validator\Constraints as TLDAssert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity(repositoryClass: ManualDocumentFileRepository::class)]
#[ORM\Table(name: 'manual_document_files')]
#[App\Loggable(owner: 'manualDocument', ownerRelation: 'files')]
#[TLDAssert\ManualDocumentFile(groups: [Manual::CRITICAL_VALIDATION_GROUP], payload: ['severity' => 'critical'])]
#[TLDAssert\ManualDocumentFile(groups: [Manual::NONCRITICAL_VALIDATION_GROUP], payload: ['severity' => 'warning'])]
class ManualDocumentFile extends File
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write'])]
    protected bool $public = true;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\ManualDocument', inversedBy: 'files')]
    private ?ManualDocument $manualDocument = null;

    public function getManualDocument(): ManualDocument
    {
        return $this->manualDocument;
    }

    public function setManualDocument(ManualDocument $manualDocument): self
    {
        $this->manualDocument = $manualDocument;

        return $this;
    }
}
