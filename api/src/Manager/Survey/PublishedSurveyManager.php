<?php

declare(strict_types=1);

namespace App\Manager\Survey;

use App\Entity\Survey\Answer;
use App\Entity\Survey\Campaign;
use App\Entity\Survey\PublishedSurvey;
use Doctrine\ORM\EntityManagerInterface;

class PublishedSurveyManager
{
    private readonly EntityManagerInterface $em;
    private readonly PublishedSurveyFactory $surveyFactory;

    public function __construct(EntityManagerInterface $em, PublishedSurveyFactory $surveyFactory)
    {
        $this->em = $em;
        $this->surveyFactory = $surveyFactory;
    }

    public function isExpired(PublishedSurvey $publishedSurvey): bool
    {
        $survey = $publishedSurvey->getCampaign()->getModel();

        return null !== $survey->getExpirationDate() && $survey->getExpirationDate() < new \DateTime('now');
    }

    public function isClosed(PublishedSurvey $publishedSurvey): bool
    {
        return 0 === $publishedSurvey->getTotalRemainingItems();
    }

    public function isCompleted(PublishedSurvey $publishedSurvey): bool
    {
        return $publishedSurvey->getTotalItems() * $publishedSurvey->getCampaign()->getModel()->getRatingTypes()->count() === $publishedSurvey->getAnswers()->count();
    }

    public function createCampaign(SurveyPublicationModel $model): Campaign
    {
        $campaign = new Campaign();
        $campaign->setModel($model->getSurvey());
        $campaign->setDescription($model->getDescription());

        foreach ($model->getTargets() as $target) {
            $survey = $this->surveyFactory->createSurvey($target);

            $campaign->addSurvey($survey);
        }

        $this->em->persist($campaign);
        $this->em->flush();

        return $campaign;
    }

    public function sendCampaign(Campaign $campaign)
    {
        $campaign->setSentAt(new \DateTime());
        $this->em->persist($campaign);
        $this->em->flush();
    }

    public function processCampaignResults(Campaign $campaign): array
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('si.id AS itemId')
            ->addSelect('srt.id AS ratingTypeId')
            ->addSelect('COUNT(sa.id) as nbAnswers')
            ->addSelect('AVG(sa.value) as average')
            ->from(Answer::class, 'sa')
            ->join('sa.item', 'si')
            ->innerJoin('sa.ratingType', 'srt')
            ->join('sa.publishedSurvey', 'sp')
            ->where('sp.campaign = :campaign')
            ->groupBy('sa.item')
            ->addGroupBy('sa.ratingType')
        ;

        $qb->setParameter('campaign', $campaign);

        $data = $qb->getQuery()->getScalarResult();

        $results = array_fill_keys(array_column($data, 'itemId'), []);

        foreach ($data as $result) {
            $results[$result['itemId']][$result['ratingTypeId']] = [
                'nbAnswers' => (int) $result['nbAnswers'],
                'average' => (float) $result['average'],
            ];
        }

        return $results;
    }
}
