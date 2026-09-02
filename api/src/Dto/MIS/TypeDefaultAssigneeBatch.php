<?php

declare(strict_types=1);

namespace App\Dto\MIS;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\DataProcessor\MIS\TypeDefaultAssigneeDataProcessor;
use App\Entity\Module\Module;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/mis/type_default_updates',
            security: 'is_granted("FEATURE_TYPE_DEFAULT_ASSIGNEE_BATCH")',
            output: false,
            validate: false,
            processor: TypeDefaultAssigneeDataProcessor::class
        ),
    ],
    denormalizationContext: ['groups' => ['module_write', 'type_assignee:write']]
)]
class TypeDefaultAssigneeBatch
{
    #[Assert\Valid]
    #[Assert\Length(min: 1)]
    #[Groups(['module_write'])]
    private readonly Collection $modules;

    public function __construct()
    {
        $this->modules = new ArrayCollection();
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
        }

        return $this;
    }

    public function removeModule(Module $module): self
    {
        $this->modules->removeElement($module);

        return $this;
    }
}
