<?php

declare(strict_types=1);

namespace App\Entity\MIS\TroubleTicket;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Module\Module;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
#[UniqueEntity(fields: 'name')]
#[ORM\UniqueConstraint(name: 'unique_name', columns: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: 'is_granted("FEATURE_APPLICATION_CREATE")'),
        new Get(),
        new Put(security: 'is_granted("FEATURE_APPLICATION_EDIT")'),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['application', 'module']],
    denormalizationContext: ['groups' => ['application:write']],
)]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'name' => 'partial',
])]
class Application
{
    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['application', 'application:write'])]
    public string $name;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Groups(['application', 'application:write'])]
    public int $jiraProjectId;

    /** @var Collection<Module> */
    #[ORM\OneToMany(mappedBy: 'application', targetEntity: Module::class)]
    private Collection $modules;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['application'])]
    private int $id;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<Module>
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Module $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules->add($module);
            $module->setApplication($this);
        }

        return $this;
    }

    public function removeModule(Module $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules->removeElement($module);
        }

        return $this;
    }
}
