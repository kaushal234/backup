<?php

declare(strict_types=1);

namespace App\ION\Resources;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

class IonText
{
    #[Groups(['ion:text'])]
    public string $code;

    #[Groups(['ion:text'])]
    public ?string $site = null;

    /**
     * @var Collection<IonTextByLanguage>
     */
    #[Groups(['ion:text'])]
    protected Collection $textByLanguages;

    public function __construct()
    {
        $this->textByLanguages = new ArrayCollection();
    }

    /**
     * @return Collection<IonTextByLanguage>
     */
    public function getTextByLanguages(): Collection
    {
        return $this->textByLanguages;
    }

    public function addTextByLanguage(IonTextByLanguage $textByLanguage): self
    {
        $this->textByLanguages->add($textByLanguage);

        return $this;
    }

    public function removeTextByLanguage(IonTextByLanguage $textByLanguage): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
