<?php

declare(strict_types=1);

namespace App\Mailer\Module;

use App\Entity\Module\ChangeLog;
use App\Mailer\AbstractPoolMailer;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChangelogPoolMailer extends AbstractPoolMailer
{
    public function __construct(
        MailerInterface $mailer,
        private readonly PeopleRepository $peopleRepository,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct($mailer);
    }

    public function addChangelog(ChangeLog $changeLog): self
    {
        if (null === $changeLog->getModule()) {
            return $this;
        }

        $misPeople = $this->peopleRepository->findGroupsMembers(['ROLE_CIO', 'ROLE_DEV']);
        $notifiedPeople = [$changeLog->getModule()->getOperationalOwner(), $changeLog->getModule()->getKeyUser()];
        $moduleName = $changeLog->getModule()->getName();

        foreach (array_merge($misPeople, $notifiedPeople) as $people) {
            if (null === $people) {
                continue;
            }
            if (null !== $email = $this->getEmail($people->getId())) {
                $context = $email->getContext();

                $changelogs = $context['changelogs'] ?? [];
                if (!isset($changelogs[$moduleName])) {
                    $changelogs[$moduleName] = [];
                }
                $changelogs[$moduleName][] = $changeLog;
                $context['changelogs'] = $changelogs;

                $modules = $context['modules'] ?? [];
                if (!isset($modules[$moduleName])) {
                    $modules[$moduleName] = $changeLog->getModule();
                }
                $context['modules'] = $modules;
                $email->context($context);

                return $this;
            }

            $normalizedChangelog = $this->normalizer->normalize($changeLog, null, ['groups' => ['changelog', 'people', 'people_public']]);

            $email = (new TemplatedEmail())
                ->context(['changelogs' => [$moduleName => [$normalizedChangelog]], 'modules' => [$moduleName => $changeLog->getModule()]])
                ->to($people->getEmail())
            ;

            $this->addEmail($email, $people->getId());
        }

        return $this;
    }
}
