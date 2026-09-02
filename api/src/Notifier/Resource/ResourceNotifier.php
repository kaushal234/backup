<?php

declare(strict_types=1);

namespace App\Notifier\Resource;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\Emails\ResourceEmail;
use App\Emailable\EmailMetadataFactory;
use App\Emailable\EmailMetadataInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Mailer\MailerInterface;

class ResourceNotifier
{
    /**
     * ResourceMailer constructor.
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly IriConverterInterface $iriConverter,
        private readonly EmailMetadataFactory $emailableAdapterFactory,
    ) {
    }

    public function sendEmail(ResourceEmail $resourceEmail, ?string $from = null): void
    {
        $emailable = $this->getResource($resourceEmail->getIri());
        $email = new TemplatedEmail();
        if (null !== $from) {
            $email->from($from);
        }

        $email
            ->to(...array_map(static fn (string $recipient) => $recipient, $resourceEmail->getTo()))
            ->cc(...array_map(static fn (string $recipient) => $recipient, $resourceEmail->getCc()))
            ->bcc(...array_map(static fn (string $recipient) => $recipient, $resourceEmail->getBcc()))
            ->subject($emailable->getEmailSubject())
            ->htmlTemplate('Emails/ResourceMail/resource.html.twig')
            ->context(array_merge(['data' => $emailable->getEmailData(), 'translation_domain' => $emailable->getTranslationDomain()], $resourceEmail->getContext()));

        $this->mailer->send($email);
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    private function getResource(string $iri): EmailMetadataInterface
    {
        try {
            $resource = $this->iriConverter->getResourceFromIri($iri);
            $emailable = $this->emailableAdapterFactory->getAdapter($resource);
        } catch (\Exception $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage(), $exception);
        }

        return $emailable;
    }
}
