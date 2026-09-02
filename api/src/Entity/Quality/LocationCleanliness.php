<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/quality/cleanliness'),
        new Post(
            uriTemplate: '/quality/cleanliness',
            security: "is_granted('FEATURE_CLEANLINESS_WRITE', object)",
        ),
        new Get(uriTemplate: '/quality/cleanliness/{id}'),
        new Put(
            uriTemplate: '/quality/cleanliness/{id}',
            security: "is_granted('FEATURE_CLEANLINESS_WRITE')",
        ),
        new Delete(
            uriTemplate: '/quality/cleanliness/{id}',
            security: "is_granted('FEATURE_CLEANLINESS_WRITE', object)"
        ),
    ],
    normalizationContext: ['groups' => ['cleanliness', 'public', 'user', 'location']],
    denormalizationContext: ['groups' => ['cleanliness', 'public', 'user']],
)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_rating_per_month_per_location', columns: ['location_id', 'date'])]
#[UniqueEntity(fields: ['date', 'location'], message: 'This date is already defined for this location.', errorPath: 'location')]
#[ApiFilter(OrderFilter::class, properties: ['date' => 'DESC', 'id' => 'DESC'])]
#[ApiFilter(DateFilter::class, properties: ['date'])]
#[ApiFilter(BooleanFilter::class, properties: ['location.capability.factory'])]
#[ApiFilter(SearchFilter::class, properties: ['location' => 'exact'])]
#[App\Loggable(owner: 'location')]
class LocationCleanliness
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['cleanliness'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    #[Groups(['cleanliness'])]
    private ?Location $location = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 5)]
    #[Groups(['cleanliness', 'location'])]
    private float $rating;

    #[ORM\Column(type: 'date')]
    #[Assert\Range(max: 'now')]
    #[Assert\NotNull]
    #[Groups(['cleanliness'])]
    private \DateTimeInterface $date;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['cleanliness'])]
    #[Gedmo\Blameable(on: 'update')]
    private User $user;

    public function getId()
    {
        return $this->id;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return $this
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @return $this
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date->modify('first day of this month');

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
