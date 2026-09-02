<?php

declare(strict_types=1);

namespace App\Entity\News;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\Department;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * News.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\News\NewsRepository')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Put(security: "is_granted('FEATURE_NEWS_WRITE')"),
        new Post(security: "is_granted('FEATURE_NEWS_WRITE')"),
        new Post(
            uriTemplate: '/news/{id}/picture',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => NewsFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_NEWS_WRITE')",
            read: true,
            deserialize: false,
            name: 'upload_news_picture',
        ),
        new Delete(security: "is_granted('FEATURE_NEWS_WRITE')"),
        new Delete(
            uriTemplate: '/news/{id}/picture/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: NewsFile::class),
                'id' => new Link(fromClass: News::class),
            ],
            defaults: ['parentProperty' => 'news', 'class' => NewsFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_NEWS_WRITE')",
            name: 'delete_news_picture',
        ),
        new Get(),
        new Get(
            uriTemplate: '/news/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: NewsFile::class),
                'id' => new Link(fromClass: News::class),
            ],
            defaults: ['parentProperty' => 'news', 'class' => NewsFile::class],
            controller: DownloadController::class,
            name: 'download_news_picture',
        ),
    ],
    normalizationContext: ['groups' => ['news', 'people_public', 'expose_legacy', 'file', 'premise', 'division', 'department', 'address']],
    denormalizationContext: ['groups' => ['news_write']],
)]
#[ORM\Table(name: 'news')]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['date' => 'DESC', 'banner' => 'DESC'])]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'content' => 'partial', 'category' => 'exact', 'legacyId' => 'exact', 'department', 'division', 'premise'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['title' => 'partial', 'content' => 'partial', 'category' => 'exact'])]
#[Legacy\Synchronize(table: 'internal_news')]
class News implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Department')]
    #[Groups(['news', 'news_write'])]
    public ?Department $department = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Division')]
    #[Groups(['news', 'news_write'])]
    public ?Division $division = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Premise')]
    #[Groups(['news', 'news_write'])]
    public ?Premise $premise = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['news'])]
    private int $id;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['news', 'news_write'])]
    #[Legacy\Column(column: 'title', transformer: Utf8ToHtmlEntities::class)]
    private string $title;

    #[ORM\Column(name: 'content', type: 'text')]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['news', 'news_write'])]
    #[Legacy\Column(column: 'en', transformer: Utf8ToHtmlEntities::class)]
    private string $content;

    #[ORM\Column(name: 'banner_text', type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 210)]
    #[Groups(['news', 'news_write'])]
    private ?string $bannerText = null;

    #[ORM\Column(name: 'major_incident', type: 'boolean', options: ['default' => false])]
    #[Groups(['news', 'news_write'])]
    private bool $majorIncident = false;

    #[ORM\Column(name: 'date', type: 'datetime')]
    #[Assert\Type('DateTimeInterface')]
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[Groups(['news', 'news_write'])]
    #[Legacy\Column(column: 'date', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    private \DateTimeInterface $date;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\News\NewsCategory', inversedBy: 'news')]
    #[Assert\Valid]
    #[Groups(['news', 'news_write'])]
    #[Legacy\Column(column: 'cat', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?NewsCategory $category = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Groups(['news', 'news_write'])]
    #[Transferable(handler: 'handler.news.author')]
    private ?People $people = null;

    /**
     * @var Collection<NewsFile>
     */
    #[ORM\OneToMany(mappedBy: 'news', targetEntity: 'App\Entity\News\NewsFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['news'])]
    #[Assert\Count(max: 3)]
    private Collection $files;

    #[ORM\Column(name: 'banner', type: 'boolean')]
    #[Groups(['news', 'news_write'])]
    private bool $banner = false;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->files = new ArrayCollection();
    }

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
     * Set title.
     *
     * @param string $title
     *
     * @return News
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return News
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    #[Groups(['news'])]
    public function getContentShort()
    {
        $raw = mb_trim(html_entity_decode(strip_tags(preg_replace('#\s\s+#', ' ', str_replace('<br>', ' ', $this->content)))));
        switch (true) {
            case mb_strlen($raw) > 75:
            default:
                return mb_substr($raw, 0, ($pos = mb_strpos($raw, ' ', 75)) ? $pos : 75).' (…)';
            case mb_strlen($raw) < 75:
                return $raw;
        }
    }

    /**
     * Set date.
     *
     * @return News
     */
    public function setDate(\DateTimeInterface $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     */
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * Set category.
     *
     * @return News
     */
    public function setCategory(?NewsCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): ?NewsCategory
    {
        return $this->category;
    }

    /**
     * @return People|null
     */
    public function getPeople()
    {
        return $this->people;
    }

    public function setPeople(People $people)
    {
        $this->people = $people;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(NewsFile $file): self
    {
        $file->setNews($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(NewsFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    public function isBanner(): bool
    {
        return $this->banner;
    }

    public function setBanner(bool $banner): self
    {
        $this->banner = $banner;

        return $this;
    }

    public function getBannerText(): ?string
    {
        return $this->bannerText;
    }

    public function setBannerText(?string $bannerText): void
    {
        $this->bannerText = $bannerText;
    }

    public function isMajorIncident(): bool
    {
        return $this->majorIncident;
    }

    public function setMajorIncident(bool $majorIncident): void
    {
        $this->majorIncident = $majorIncident;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->banner && empty($this->bannerText)) {
            $context->buildViolation('Banner text cannot be blank if banner is checked.')
                ->atPath('bannerText')
                ->addViolation();
        }
    }
}
