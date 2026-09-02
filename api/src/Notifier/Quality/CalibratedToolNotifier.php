<?php

declare(strict_types=1);

namespace App\Notifier\Quality;

use App\Entity\Directory\People;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CalibratedToolNotifier
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly MailerInterface $mailer,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    /**
     * @param array|Tool[] $tools
     */
    public function sendEmail(array $tools, string $to): void
    {
        $ccs = [];
        foreach ($tools as $tool) {
            if (Tool::EXPIRED === $tool->getStatus()) {
                $businessUnit = $tool->getLocationArea()->getSupervisor()->getBusinessUnit();

                if (null !== $businessUnit) {
                    $ccs = [
                        ...$ccs,
                        ...$this->peopleRepository->findGroupMembers('ROLE_QAM', $businessUnit->getLocation()),
                        ...$this->peopleRepository->findGroupMembers('ROLE_QA', $businessUnit->getLocation()),
                    ];
                }
            }
        }

        $email = (new TemplatedEmail())
            ->to($to)
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $ccs))
            ->subject('tool.subject')
            ->htmlTemplate('Emails/Quality/CalibratedTools/tool_status_notification.html.twig')
            ->context($this->buildContext($tools));

        $this->mailer->send($email);
    }

    private function buildContext(array $tools): array
    {
        return [
            'tools' => $this->normalizer->normalize($tools, null, ['groups' => ['tool']]),
        ];
    }
}
