<?php

declare(strict_types=1);

namespace App\Notifier\MIS\Project;

use App\Entity\Directory\People;
use App\Entity\MIS\Project\Project;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function send(Project $project, string $subject, array $changeSet = [], array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findTos($project)))
            ->subject(\sprintf('mis_project.%s.subject', $subject))
            ->htmlTemplate(\sprintf('Emails/MIS/Project/%s.html.twig', $subject))
            ->context($this->buildContext($project, [...$changeSet, ...$context]));

        $this->mailer->send($email);
    }

    private function buildContext(Project $project, array $context = []): array
    {
        if (isset($context['changeSet'])) {
            $changeSet = $context['changeSet'];
            foreach ($changeSet as $key => $value) {
                $translationPath = 'mis_project.change_set.'.$key;
                if ($translationPath !== $newKey = $this->translator->trans($translationPath, [], 'emails')) {
                    $changeSet[$newKey] = $value;
                    unset($changeSet[$key]);
                }
            }
            $context['changeSet'] = $changeSet;
        }

        return [
            ...$context,
            'id' => $project->getId(),
            'project_name' => $project->name,
            'comment' => $project->comment,
            'project' => $this->normalizer->normalize($project, null, ['groups' => Project::ITEM_NORMALIZATION_GROUPS]),
        ];
    }
}
