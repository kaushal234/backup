<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall;

use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TechnicianOnCallSurveyNotifier implements ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function send(TechnicianOnCallSurvey $survey)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);
        /** @var RecipientsFinder $recipientsFinder */
        $recipientsFinder = $this->container->get(RecipientsFinder::class);

        /** @var NormalizerInterface $normalizer */
        $normalizer = $this->container->get(NormalizerInterface::class);

        $email = (new TemplatedEmail())
            ->to(...$recipientsFinder->findSurveyTos($survey))
            ->subject($translator->trans('toc.subject.survey', [
                '%id%' => $survey->technicianOnCall->getId(),
                '%customer%' => $survey->technicianOnCall->customer->getName(),
                '%productName%' => $survey->technicianOnCall->equipmentRecord?->getProduct()?->getName() ?? 'N/A',
                '%serial%' => $survey->technicianOnCall->equipmentRecord?->getSerialNumber() ?? $survey->technicianOnCall->serialNumber,
            ], 'emails'))
            ->htmlTemplate('Emails/Service/TechnicianOnCall/survey.html.twig')
            ->context([
                'survey' => $normalizer->normalize($survey, null, ['groups' => ['toc:survey', ...TechnicianOnCall::ITEM_NORMALIZATION_GROUPS, 'people_detail', 'extranet_user_public']]),
            ])
        ;

        $this->container->get(MailerInterface::class)->send($email);
    }

    public function sendErrorTaskCreationEmail(TechnicianOnCallSurvey $survey, People $csd, array $messages = []): void
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);

        $email = (new TemplatedEmail())
            ->to($csd->getEmail())
            ->subject($translator->trans('toc.subject.task_survey_error', [
                '%id%' => $survey->getId(),
            ], 'emails'))
            ->htmlTemplate('Emails/Service/TechnicianOnCall/task_survey_error.html.twig')
            ->context([
                'survey' => $survey,
                'messages' => $messages,
            ])
        ;

        $this->container->get(MailerInterface::class)->send($email);
    }

    public static function getSubscribedServices(): array
    {
        return [
            MailerInterface::class,
            TranslatorInterface::class,
            RecipientsFinder::class,
            NormalizerInterface::class,
        ];
    }
}
