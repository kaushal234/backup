<?php

declare(strict_types=1);

namespace App\Entity\Module;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ModuleMigrationStep.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['module']],
    denormalizationContext: ['groups' => ['module_write']],
)]
#[ORM\Table(name: 'module_migration_steps')]
class ModuleMigrationStep
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['module'])]
    private int $id;

    #[ORM\Column(name: 'short_desc', type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    #[Groups(['module', 'module_write'])]
    private string $shortDesc;

    #[ORM\Column(name: 'full_desc', type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[Groups(['module', 'module_write'])]
    private ?string $fullDesc = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShortDesc()
    {
        return $this->shortDesc;
    }

    /**
     * @param string $shortDesc
     *
     * @return $this
     */
    public function setShortDesc($shortDesc)
    {
        $this->shortDesc = $shortDesc;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullDesc()
    {
        return $this->fullDesc;
    }

    /**
     * @param string $fullDesc
     *
     * @return $this
     */
    public function setFullDesc($fullDesc)
    {
        $this->fullDesc = $fullDesc;

        return $this;
    }
}
