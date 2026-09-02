<?php

declare(strict_types=1);

namespace App\Entity\News;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Category.
 */
#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_NEWS_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_NEWS_WRITE')"),
        new Delete(security: "is_granted('FEATURE_NEWS_WRITE')"),
    ],
    normalizationContext: ['groups' => ['news', 'expose_legacy']],
    denormalizationContext: ['groups' => ['news']],
)]
#[ORM\Table(name: 'news_category')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['legacyId' => 'exact'])]
#[Legacy\Synchronize(table: 'lists')]
#[Legacy\ExtraColumn(column: 'parent_id', value: 0)]
#[Legacy\ExtraColumn(column: 'list_name', value: 'categories')]
#[Legacy\ExtraColumn(column: 'list_key', value: '')]
class NewsCategory implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['news'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['news'])]
    #[Legacy\Column(column: 'list_item')]
    private string $name;

    /**
     * @var Collection<News>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: 'App\Entity\News\News')]
    private Collection $news;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return NewsCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Collection<News>
     */
    public function getNews()
    {
        return $this->news;
    }
}
