<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders;

use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use Doctrine\ORM\EntityManagerInterface;

class TocNewCommentEmailBuilder extends AbstractTocEmailBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return \in_array($subject, [
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_INTERNAL,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_BY_CUST,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_EXTERNAL,
        ], true);
    }

    protected function buildContext(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $technicianOnCall,
        array $context,
    ): array {
        $context = parent::buildContext($subject, $technicianOnCall, $context);

        $criteria = ['resource' => $context['comment']->getResource()];

        if ($subject->isExternal()) {
            $criteria['public'] = true;
        }

        $context['previousComments'] = $this->normalizer->normalize(
            $this->entityManager->getRepository(Comment::class)->findBy($criteria, ['createdAt' => 'DESC'], 5, 1),
            null,
            ['groups' => ['activity', 'people_public']],
        );

        return $context;
    }

    protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array
    {
        $params = [
            '%openDays%' => $technicianOnCall->getOpenDays(),
            '%sso%' => $technicianOnCall->equipmentRecord?->getSalesOrganisation()->getName() ?? '---',
        ];

        if (!$subject->isExternal()) {
            $params['%customer%'] = $technicianOnCall->customer->getName();
            $params['%indiceFactor%'] = $technicianOnCall->indiceFactor;
        }

        return $params;
    }
}
