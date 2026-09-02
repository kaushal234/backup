<?php

declare(strict_types=1);

namespace App\ION\Resources;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * - Don't forget to create the ArrayCollection on your class constructor.
 * - For an API Resource you need to add the NormalizationGroup ion:text.
 */
trait TextByLanguagesTraits
{
    /**
     * @var Collection<IonTextByLanguage>
     */
    #[Groups(['ion:text'])]
    protected Collection $textByLanguages;

    public function getTextByLanguages(): Collection
    {
        return $this->textByLanguages;
    }

    /**
     * @param ArrayCollection|IonTextByLanguage[] $textByLanguages
     */
    public function setTextByLanguages(Collection $textByLanguages): self
    {
        $this->textByLanguages = $textByLanguages;

        return $this;
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
