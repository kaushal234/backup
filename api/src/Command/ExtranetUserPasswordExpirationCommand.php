<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(
    name: 'api:user:extranet_password_expiration_notification',
    description: 'Send mail to extranet users when password will be expired.'
)]
class ExtranetUserPasswordExpirationCommand extends Command
{
    private const array NOTICE_DAYS = [30, 7, 1];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $recipients = $this->entityManager->getRepository(ExtranetUser::class)->findAll();
        $today = new \DateTimeImmutable('today');

        foreach ($recipients as $recipient) {
            if ($recipient->isDisabled() || $recipient->isHidden()) {
                continue;
            }

            if (null === $recipient->getEmail()) {
                continue;
            }

            if ($recipient->isPasswordExpired()) {
                continue;
            }

            $expirationDate = $recipient->getPasswordExpirationDate();
            if (null === $expirationDate) {
                continue;
            }

            $expiration = \DateTimeImmutable::createFromInterface(
                \DateTime::createFromFormat('Y-m-d', $expirationDate->format('Y-m-d')) ?: $expirationDate
            );

            $interval = $today->diff($expiration);
            if (1 === $interval->invert) {
                continue;
            }

            $daysRemaining = (int) $interval->days;

            if (!\in_array($daysRemaining, self::NOTICE_DAYS, true)) {
                continue;
            }

            $email = (new TemplatedEmail())
                ->from('noreply@tld-gse.com')
                ->to($recipient->getEmail())
                ->subject('Expiration Password Reminder')
                ->htmlTemplate('Emails/User/password_expiration.html.twig')
                ->context([
                    'user' => $recipient,
                    'days_remaining' => $daysRemaining,
                    'expiration_date' => $expiration->format('Y-m-d'),
                ]);

            try {
                $this->mailer->send($email);
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
