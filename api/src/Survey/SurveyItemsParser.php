<?php

declare(strict_types=1);

namespace App\Survey;

use App\Entity\Survey\PublishedSurvey;
use App\Repository\Survey\PublishedSurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SurveyItemsParser
{
    protected PublishedSurveyRepository $repository;

    public function __construct(PublishedSurveyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function processPublishedSurvey(PublishedSurvey $data)
    {
        $data->setTotalItems($data->getCampaign()->getModel()->getItems()->count());

        if (0 !== $data->getAnswers()->count()) {
            $ratingTypesNumber = $data->getTotalItems() * $data->getCampaign()->getModel()->getRatingTypes()->count();
            $data->setTotalItemsAnswered((int) floor(($data->getAnswers()->count() / $ratingTypesNumber) * $data->getTotalItems()));
        }

        $data->setTotalRemainingItems($data->getTotalItems() - $data->getTotalItemsAnswered());
    }

    public function parseAvailableItems(PublishedSurvey $data): PublishedSurvey
    {
        $this->processPublishedSurvey($data);

        $this->parseItemsCreatedAfterExpiration($data);
        $items = $data->getCampaign()->getModel()->getItems();

        foreach ($items as $item) {
            if ($this->repository->isItemFullyAnswered($data, $item)) {
                $items->removeElement($item);
            }
        }

        $cleanItems = $this->resetKeys($items);
        $data->getCampaign()->getModel()->setItems($cleanItems);

        return $data;
    }

    private function parseItemsCreatedAfterExpiration(PublishedSurvey $publishedSurvey): PublishedSurvey
    {
        $survey = $publishedSurvey->getCampaign()->getModel();
        if (null === $survey->getExpirationDate()) {
            return $publishedSurvey;
        }

        $items = $survey->getItems();
        foreach ($items as $item) {
            if ($item->getCreatedAt() > $survey->getExpirationDate()) {
                $survey->removeItem($item);
            }
        }

        $cleanItems = $this->resetKeys($items);
        $publishedSurvey->getCampaign()->getModel()->setItems($cleanItems);

        return $publishedSurvey;
    }

    private function resetKeys(Collection $collection)
    {
        $arrCleanItems = $collection->getValues();

        return new ArrayCollection($arrCleanItems);
    }
}
