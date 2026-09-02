<?php

declare(strict_types=1);

namespace App\Entity\Module;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\People;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
        ),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['change_log_edit']],
            security: "is_granted('FEATURE_CHANGE_LOG_EDIT')",
        ),
    ],
    normalizationContext: ['groups' => ['changelog', 'module', 'people_public']]
)]
#[ORM\Table('change_logs')]
#[ApiFilter(OrderFilter::class, properties: ['date', 'module.name', 'type', 'message', 'module.operationalOwner.lastname', 'author.lastname', 'ticket'])]
#[ApiFilter(NumericFilter::class, properties: ['ticket'])]
#[ApiFilter(SearchFilter::class, properties: ['module' => 'exact', 'module.operationalOwner' => 'exact', 'author' => 'exact', 'message' => 'partial', 'type' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['date'])]
#[ApiFilter(ColumnsFilter::class)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['date' => 'partial', 'module.name' => 'partial', 'type' => 'partial', 'message' => 'partial', 'module.operationalOwner.lastname' => 'partial', 'author.lastname' => 'partial', 'author.firstname' => 'partial', 'ticket' => 'partial'])]
class ChangeLog
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'hash', type: 'string', unique: true)]
    private string $hash;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Module\Module')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['changelog', 'changelog_detail'])]
    private ?Module $module = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['changelog'])]
    private People $author;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['changelog'])]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['changelog', 'change_log_edit'])]
    #[Assert\NotBlank]
    private string $message;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['changelog'])]
    private ?int $ticket = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['changelog'])]
    private ?string $type = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return $this
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    /**
     * @return $this
     */
    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getAuthor(): People
    {
        return $this->author;
    }

    /**
     * @param People $author
     *
     * @return $this
     */
    public function setAuthor($author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return $this
     */
    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getTicket(): ?int
    {
        return $this->ticket;
    }

    /**
     * @return $this
     */
    public function setTicket(?int $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
