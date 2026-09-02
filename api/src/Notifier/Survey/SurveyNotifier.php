<?php

declare(strict_types=1);

namespace App\Notifier\Survey;

use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Survey\PublishedSurvey;
use App\Manager\Directory\PeopleManager;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SurveyNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $parameters,
        private readonly NormalizerInterface $normalizer,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    public function notifyCreation(PublishedSurvey $publishedSurvey): void
    {
        $email = (new TemplatedEmail())
            ->to($publishedSurvey->getTarget()->getEmail())
            ->subject('survey.new_publish.subject')
            ->htmlTemplate('Emails/Survey/survey_notification.html.twig')
            ->context($this->buildContext($publishedSurvey));

        $this->mailer->send($email);
    }

    public function notifyCompletion(PublishedSurvey $publishedSurvey): void
    {
        /** @var People[] $ccs */
        $ccs = [];
        /** @var ExtranetUser $target */
        $target = $publishedSurvey->getTarget();
        foreach ($target->getExtranetUserAcls() as $acl) {
            $crt = $acl->getCrt();
            if (null !== $asm = $crt->getSalesRepresentative()) {
                $ccs[] = $asm;
                if (null !== $evp = $asm->getSupervisor()) {
                    $ccs[] = $evp;
                    /** @var People $subordinate */
                    foreach ($this->peopleRepository->getSubordinates($evp, 2) as $subordinate) {
                        if (PeopleManager::hasGroup($subordinate, 'ROLE_SAM')) {
                            $ccs[] = $subordinate;
                        }
                    }
                }
            }
            if (null !== $partsRepresentative = $crt->getPartsRepresentative()) {
                $ccs[] = $partsRepresentative;
            }
            if (null !== $serviceRepresentative = $crt->getServiceRepresentative()) {
                $ccs[] = $serviceRepresentative;
            }
            $ccs = [
                ...$ccs,
                ...$this->peopleRepository->findGroupMembers('ROLE_GCOO'),
                ...$this->peopleRepository->findGroupMembers('ROLE_GCEO'),
                ...$this->peopleRepository->findGroupMembers('ROLE_GPID'),
                ...$this->peopleRepository->findGroupMembers('ROLE_CSD'),
                ...$this->peopleRepository->findGroupMembers('ROLE_GTD'),
                ...$this->peopleRepository->findGroupMembers('ROLE_CEO', $crt->getErpLocation()),
            ];
        }

        $email = (new TemplatedEmail())
            ->to($publishedSurvey->getCampaign()->getCreatedBy()->getEmail())
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $ccs))
            ->subject('survey.completed.subject')
            ->htmlTemplate('Emails/Survey/survey_completed.html.twig')
            ->context($this->buildContext($publishedSurvey));

        $this->mailer->send($email);
    }

    private function buildContext(PublishedSurvey $publishedSurvey, $context = []): array
    {
        return $context + [
            'publishedSurvey' => $this->normalizer->normalize($publishedSurvey, null, [
                'groups' => ['survey', 'survey_target_detail', 'campaign', 'published_detail', 'user', 'people_photo', 'file:light', 'people_public', 'extranet_user', 'user_profile'],
            ]),
            'survey_host' => $this->parameters->get('survey.host'),
        ];
    }
}
