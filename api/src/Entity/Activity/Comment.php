<?php

declare(strict_types=1);

namespace App\Entity\Activity;

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
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\CommentFileController;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Doctrine\Mapping\Attributes as App;
use App\Filter\ExtraCommentFilter;
use App\Repository\Common\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A user log activity.
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            openapi: new Operation(
                summary: 'Get list of comments for a resource',
                parameters: [
                    new Parameter(
                        name: 'resource',
                        in: 'query',
                        description: 'The IRI of the resource',
                        schema: ['type' => 'string'],
                        example: '/service/technician_on_calls/7',
                    ),
                ],
            ),
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('ACCESS_EXTRANET_USER')"
        ),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data'], 'jsonld'],
            outputFormats: ['jsonld'],
            controller: CommentFileController::class,
            openapi: new Operation(
                summary: 'Create a comment for a resource',
            ),
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('ACCESS_EXTRANET_USER')",
            securityPostDenormalize: "is_granted('COMMENT_WRITE_VOTER', object)",
            name: 'api_comments_post_collection',
        ),
        new Get(security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('ACCESS_EXTRANET_USER')"),
        new Get(
            uriTemplate: '/comments/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CommentFile::class),
                'id' => new Link(fromClass: Comment::class),
            ],
            defaults: ['parentProperty' => 'comment', 'class' => CommentFile::class],
            controller: DownloadController::class,
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER') or is_granted('ACCESS_EXTRANET_USER')",
            name: 'download_comment_file',
        ),
        new Delete(
            uriTemplate: '/comments/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: CommentFile::class),
                'id' => new Link(fromClass: Comment::class),
            ],
            defaults: ['parentProperty' => 'comment', 'class' => CommentFile::class],
            controller: DeleteController::class,
            security: 'object.getUser() === user',
            name: 'delete_comment_file',
        ),
    ],
    normalizationContext: ['groups' => ['activity', 'people_public', 'expose_legacy', 'file:light', 'file:description']],
    denormalizationContext: ['groups' => ['activity:write', 'public']],
)]
#[ApiFilter(SearchFilter::class, properties: ['resource' => 'exact', 'legacyId' => 'exact', 'discriminator' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['people_photo', 'file:light', 'activity:file']])]
#[ApiFilter(ExtraCommentFilter::class)]
#[App\Loggable]
#[Legacy\ExtraColumn(column: 'log_num', value: 1)]
class Comment extends Activity implements \Stringable
{
    final public const VENDOR_USER_COMMENTABLE = '_vendor_user_commentable';
    final public const EXTRANET_USER_COMMENTABLE = '_extranet_user_commentable';

    #[Groups(['activity:write'])]
    public ?string $fileDescription = null;

    #[ApiProperty(iris: ['https://schema.org/commentText'])]
    #[ORM\Column(name: 'message', type: 'text')]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['activity', 'activity:write'])]
    #[Legacy\Column(column: 'comment', transformer: Utf8ToHtmlEntities::class)]
    private string $message;

    /**
     * @var Collection<CommentFile>
     */
    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: 'App\Entity\Activity\CommentFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['activity', 'activity:file'])]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return Collection<CommentFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(CommentFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setComment($this);
        }

        return $this;
    }

    public function removeFile(CommentFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }
}
