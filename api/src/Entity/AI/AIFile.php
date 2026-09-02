<?php

declare(strict_types=1);

namespace App\Entity\AI;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Controller\AI\FileDownloadController;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ai_files')]
#[ApiResource(
    shortName: 'AiFile',
    operations: [
        new Get(),
        new Get(
            uriTemplate: 'ai_files/{id}/download',
            controller: FileDownloadController::class,
            name: 'download_ai_file'
        ),
    ],
    normalizationContext: ['groups' => ['file', 'people_public']],
    security: 'user === object.getRequest().log.people',
)]
class AIFile extends File
{
    #[ORM\OneToOne(targetEntity: Request::class, inversedBy: 'file')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Request $request;

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }
}
