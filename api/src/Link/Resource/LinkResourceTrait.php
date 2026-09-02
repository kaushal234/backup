<?php

declare(strict_types=1);

namespace App\Link\Resource;

use App\Link\QueryBuilder\GraphQLMutationBuilder;
use Symfony\Component\Serializer\Attribute\Groups;

trait LinkResourceTrait
{
    #[Groups([GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP])]
    protected ?int $linkId = null;

    public function getLinkId(): ?int
    {
        return $this->linkId;
    }

    public function setLinkId(?int $linkId): self
    {
        $this->linkId = $linkId;

        return $this;
    }
}
