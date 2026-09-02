<?php

declare(strict_types=1);

namespace App\Manager\Survey;

use App\Entity\Survey\Survey;
use App\Entity\SurveyTargetInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class SurveyPublicationModel
{
    #[Assert\NotNull]
    #[Assert\Length(max: 255, maxMessage: "Description can't be longer than 255 characters")]
    protected string $description;

    #[Assert\NotNull]
    protected Survey $survey;

    #[Assert\Count(min: 1)]
    protected ArrayCollection $targets;

    public function __construct()
    {
        $this->targets = new ArrayCollection();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSurvey(Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function getTargets(): ArrayCollection
    {
        return $this->targets;
    }

    /**
     * @return $this
     */
    public function addTarget(SurveyTargetInterface $target): self
    {
        $this->targets->add($target);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeTarget(SurveyTargetInterface $target): self
    {
        $this->targets->removeElement($target);

        return $this;
    }
}
