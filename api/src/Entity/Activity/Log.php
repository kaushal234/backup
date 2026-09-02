<?php

declare(strict_types=1);

namespace App\Entity\Activity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Change;
use App\Entity\AuthorizedApplication;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\Templating;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user log activity.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\Common\LogRepository')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['activity', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['activity', 'public']],
)]
#[ApiFilter(SearchFilter::class, properties: ['resource' => 'exact', 'legacyId' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
#[Legacy\ExtraColumn(column: 'log_num', value: 0)]
class Log extends Activity implements \Stringable
{
    #[ApiProperty(iris: ['https://schema.org/actionStatus'])]
    #[ORM\Column(name: 'action', type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(callback: 'getActions')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    #[Groups(['activity'])]
    private string $action;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\AuthorizedApplication')]
    private ?AuthorizedApplication $authorizedApplication = null;

    #[ApiProperty(iris: ['https://schema.org/ItemList'])]
    #[ORM\Column(name: 'change_set', type: 'json')]
    #[Assert\Type(type: 'array')]
    #[Assert\NotBlank]
    #[Groups(['activity'])]
    #[Legacy\Column(column: 'comment', transformer: Templating::class, options: ['template' => '@Legacy/transform/logComment.txt.twig'])]
    private array $changeSet = [];

    public function __toString()
    {
        return $this->action.' ['.\count($this->changeSet).']';
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return Log
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @return Log
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    public function getAuthorizedApplication(): ?AuthorizedApplication
    {
        return $this->authorizedApplication;
    }

    public function setAuthorizedApplication(?AuthorizedApplication $authorizedApplication): self
    {
        $this->authorizedApplication = $authorizedApplication;

        return $this;
    }

    #[Groups(['activity'])]
    public function getFrom(): string
    {
        return (string) ($this->getUser() ?? $this->getAuthorizedApplication());
    }

    /**
     * Gets the list of available actions.
     *
     * @return array
     */
    public function getActions()
    {
        return [
            Change::ACTION_CREATE,
            Change::ACTION_UPDATE,
            Change::ACTION_DELETE,
        ];
    }
}
