<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use ApiBundle\Client;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent(name: 'NavItem:CopyEmail', template: 'components/NavBar/CopyEmail.html.twig')]
class CopyEmail extends NavItem
{
    public const LIMIT = 200;

    public string $subject;
    public string $type;

    private ?string $emails = null;
    private int $total = 0;

    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function mount(string $subject, string $type): void
    {
        $this->subject = $subject;
        $this->type = $type;
    }

    #[ExposeInTemplate('emails')]
    public function getEmails(): string
    {
        $this->load();

        return $this->emails;
    }

    #[ExposeInTemplate('capped')]
    public function isCapped(): bool
    {
        $this->load();

        return $this->total > self::LIMIT;
    }

    #[ExposeInTemplate('message')]
    public function getMessage(): string
    {
        $this->load();

        if ($this->total > self::LIMIT) {
            return $this->translator->trans(
                'directory.people.messages.warning.copy_limit_warning',
                [
                    '%type%' => $this->type,
                    '%count%' => $this->total,
                    '%limit%' => self::LIMIT,
                ],
                'directory'
            );
        }

        return $this->translator->trans(
            'directory.people.messages.success.copy_emails',
            ['%count%' => $this->total],
            'directory'
        );
    }

    private function load(): void
    {
        if (null !== $this->emails) {
            return;
        }

        $filter = match ($this->type) {
            'group' => ['acls.group' => $this->subject],
            'position' => ['position' => $this->subject],
            'premise' => ['premise' => $this->subject],
            default => [],
        };

        $people = $this->client->findBy(
            'people/search',
            [
                ...$filter,
                'disabled' => false,
                'hidden' => false,
                'itemsPerPage' => self::LIMIT,
            ],
            [
                'lastname' => 'ASC',
                'firstname' => 'ASC',
            ]
        );

        $emails = [];
        foreach ($people as $person) {
            if (!empty($person['email'])) {
                $emails[] = $person['email'];
            }
        }

        $this->emails = implode('; ', $emails);
        $this->total = $people->pagination->getTotalItems();
    }
}
