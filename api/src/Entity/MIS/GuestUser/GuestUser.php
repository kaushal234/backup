<?php

declare(strict_types=1);

namespace App\Entity\MIS\GuestUser;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\Directory\Premise;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\User;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use App\Repository\MIS\GuestUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntity(fields: ['email'], message: 'This value is already used by another Guest User.')]
#[ORM\Entity(repositoryClass: GuestUserRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
        ),
        new Get(),
        new Post(),
        new Put(
            denormalizationContext: ['groups' => ['guest:edit']],
            security: 'is_granted("GUEST_USER_UPDATE_VOTER", object)'
        ),
    ],
    normalizationContext: ['groups' => ['guest', 'people_public', 'premise', 'position', 'business_unit_public', 'update_task:light', 'module_light']],
    denormalizationContext: ['groups' => ['guest:create']],
)]
#[App\Loggable]
#[ApiFilter(OrderFilter::class, properties: ['firstname', 'lastname', 'businessUnit.name', 'position.code', 'id', 'premise.name', 'supervisor.lastname'])]
#[ApiFilter(BooleanFilter::class, properties: ['disabled', 'hidden'])]
#[ApiFilter(SearchFilter::class, properties: ['premise', 'businessUnit', 'position', 'supervisor'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['firstname' => 'partial', 'lastname' => 'partial', 'email' => 'partial', 'username' => 'partial'])]
#[ApiFilter(ColumnsFilter::class)]
class GuestUser extends User implements \Stringable
{
    #[Groups(['guest'])]
    protected ?Position $position = null;

    #[Assert\NotNull]
    #[Groups(['guest', 'guest:create', 'guest:edit'])]
    protected ?BusinessUnit $businessUnit = null;

    #[Groups(['guest', 'guest:create'])]
    #[Assert\NotNull]
    protected ?\DateTimeInterface $enableAt = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Blameable(on: 'create')]
    #[Groups(['guest', 'guest:create', 'guest:edit'])]
    #[Transferable(handler: 'handler.transfer.supervisor')]
    private People $supervisor;

    #[ORM\ManyToOne(targetEntity: Premise::class)]
    #[Groups(['guest', 'guest:create', 'guest:edit'])]
    #[Transferable(conditions: ['disabled' => false, 'hidden' => false], manager: 'manager.premise')]
    private ?Premise $premise = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['guest', 'guest:create', 'guest:edit'])]
    #[Assert\Range(min: 'now', max: '+ 1 year')]
    private \DateTimeInterface $plannedDisableAt;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[Groups(['guest', 'guest:create', 'guest:edit'])]
    private bool $needsCollaborationAccess = false;

    /**
     * @var Collection<Extended>
     */
    #[ORM\ManyToMany(targetEntity: Extended::class)]
    #[ORM\JoinTable(name: 'guest_user_extended')]
    #[Groups(['guest', 'guest:create', 'guest:edit'])]
    private Collection $modules;

    public function __construct()
    {
        parent::__construct();

        $this->modules = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getLastname().', '.$this->getFirstname();
    }

    public function getSupervisor(): People
    {
        return $this->supervisor;
    }

    public function setSupervisor(People $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    public function getPremise(): Premise
    {
        return $this->premise;
    }

    public function setPremise(Premise $premise): self
    {
        $this->premise = $premise;

        return $this;
    }

    public function getPlannedDisableAt(): ?\DateTimeInterface
    {
        return $this->plannedDisableAt;
    }

    public function setPlannedDisableAt(\DateTimeInterface $plannedDisableAt): self
    {
        $this->plannedDisableAt = $plannedDisableAt;

        return $this;
    }

    public function getNeedsCollaborationAccess(): bool
    {
        return $this->needsCollaborationAccess;
    }

    public function setNeedsCollaborationAccess(bool $needsCollaborationAccess): self
    {
        $this->needsCollaborationAccess = $needsCollaborationAccess;

        return $this;
    }

    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(Extended $module): self
    {
        if (!$this->modules->contains($module)) {
            $this->modules->add($module);
        }

        return $this;
    }

    public function removeModule(Extended $module): self
    {
        if ($this->modules->contains($module)) {
            $this->modules->removeElement($module);
        }

        return $this;
    }

    #[Groups(['guest'])]
    public function getFullName(): string
    {
        return mb_trim(\sprintf('%s %s', $this->getLastname(), $this->getFirstname()));
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (!$this->needsCollaborationAccess && $this->modules->isEmpty()) {
            $context
                ->buildViolation('At least one module must be selected when collaboration access is not required.')
                ->atPath('modules')
                ->addViolation();
        }
    }
}
