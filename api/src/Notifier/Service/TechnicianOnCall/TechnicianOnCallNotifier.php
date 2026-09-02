<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\Service\TechnicianOnCallEmailInput;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\Builders\TechnicianOnCallEmailBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallNotifier
{
    /**
     * @param iterable<TechnicianOnCallEmailBuilderInterface> $builders
     */
    public function __construct(
        private MailerInterface $mailer,
        private NormalizerInterface $normalizer,
        private Security $security,
        private EntityManagerInterface $entityManager,
        private IriConverterInterface $iriConverter,
        #[AutowireIterator('app.toc.email.builder')]
        private iterable $builders,
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param TechnicianOnCallMailSubject[] $subjects
     *
     * @throws TransportExceptionInterface|ExceptionInterface
     */
    public function sendEmails(array $subjects, TechnicianOnCall $technicianOnCall, array $context = []): void
    {
        foreach ($subjects as $subject) {
            $this->sendEmail($subject, $technicianOnCall, $context);
        }
    }

    /**
     * @throws TransportExceptionInterface|ExceptionInterface
     */
    public function sendEmail(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall, array $context = []): void
    {
        $context['technicianOnCall'] = $this->normalizer->normalize($technicianOnCall, null, ['groups' => [...TechnicianOnCall::ITEM_NORMALIZATION_GROUPS, 'people_detail', 'people_public', 'extranet_user_public']]);
        $context['previousComments'] = $this->normalizer->normalize($context['previousComments'] ?? [], null, ['groups' => ['activity', 'people_public']]);
        foreach ($this->builders as $builder) {
            if (!$builder->supports($subject)) {
                continue;
            }

            $email = $builder->build($subject, $technicianOnCall, $context);

            if (empty($email->getTo()) && empty($email->getCc()) && empty($email->getBcc())) {
                $this->logger->error('TOC email skipped: no valid recipients found.', [
                    'subject' => $subject->value,
                    'toc_id' => $technicianOnCall->getId(),
                ]);

                return;
            }

            $this->mailer->send($email);

            return;
        }

        throw new \LogicException(\sprintf('No email builder found for subject "%s"', $subject->value));
    }

    public function sendIntranetEmail(
        TechnicianOnCallEmailInput $input,
        TechnicianOnCall $technicianOnCall,
        array $context = [],
    ): void {
        $context['technicianOnCall'] = $this->normalizer->normalize($technicianOnCall, null, ['groups' => [...TechnicianOnCall::ITEM_NORMALIZATION_GROUPS, 'people_detail']]);

        /** @var People $currentUser */
        $currentUser = $this->security->getUser();

        $email = (new TemplatedEmail())
            ->from($currentUser->getEmail())
            ->to($input->to->getEmail())
            ->subject(\sprintf('TOC#%s : %s', $technicianOnCall->getId(), $input->subject))
            ->htmlTemplate('Emails/Service/TechnicianOnCall/toc_intranet_mail.html.twig')
            ->context($context)
            ->addCc(...array_map(static fn ($cc) => $cc->getEmail(), $input->getCcs()->toArray()))
            ->addCc($currentUser->getEmail())
            ->addBcc(...array_map(static fn ($bcc) => $bcc->getEmail(), $input->getBccs()->toArray()))
        ;

        $this->mailer->send($email);

        $comment = (new Comment())
            ->setMessage($this->translator->trans('toc.intranet_email_comment', [
                '%message%' => $input->message ?? '',
                '%tos%' => implode(', ', array_map(static fn ($address) => $address->toString(), [...$email->getTo(), ...$email->getCc()])),
            ], 'emails'))
            ->setResource($this->iriConverter->getIriFromResource($technicianOnCall))
            ->setUser($currentUser)
            ->setPublic(false)
        ;

        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}
