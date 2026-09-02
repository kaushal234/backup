<?php

declare(strict_types=1);

namespace App\Entity\Service\SurveyCustomerServiceRecord;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Action\NotFoundAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity('name')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            controller: NotFoundAction::class,
            output: false,
            read: false,
        ),
    ],
    routePrefix: 'service',
)]
class QuestionSurveyCustomerServiceRecord implements \Stringable
{
    public const BOOLEAN_TYPE = 'boolean';
    public const RATING_TYPE = 'rating';
    public const CHOICE_TYPE = 'choice';

    public const RATING_QUESTIONS = ['aspect', 'conformity', 'operational'];

    #[ORM\Column(type: 'string', length: 55, unique: true)]
    #[Groups(['customer_service_record:answer'])]
    #[Assert\Unique]
    public string $name;

    #[ORM\Column(type: 'text', length: 50)]
    #[Assert\Choice(choices: [self::BOOLEAN_TYPE, self::RATING_TYPE, self::CHOICE_TYPE])]
    #[Groups(['customer_service_record:answer'])]
    public string $type;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
