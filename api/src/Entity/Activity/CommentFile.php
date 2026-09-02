<?php

declare(strict_types=1);

namespace App\Entity\Activity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'comment_files')]
#[App\Loggable(owner: 'comment', ownerRelation: 'files')]
class CommentFile extends File
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write', 'file:description'])]
    protected bool $public = true;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Activity\Comment', inversedBy: 'files')]
    private ?Comment $comment = null;

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
