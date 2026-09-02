<?php

declare(strict_types=1);

namespace App\Tests\Notifier\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Dto\Service\TechnicianOnCallEmailInput;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallNotifier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallNotifierTest extends TestCase
{
    public function testSendIntranetEmailNormalizesTocBeforeDispatch(): void
    {
        $toc = new TechnicianOnCall();
        $normalizedToc = ['id' => 1, 'createdBy' => ['firstname' => 'Rahul', 'lastname' => 'PARAKKATT']];

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('normalize')
            ->with(
                $toc,
                null,
                ['groups' => [...TechnicianOnCall::ITEM_NORMALIZATION_GROUPS, 'people_detail']]
            )
            ->willReturn($normalizedToc);

        $currentUser = $this->createMock(People::class);
        $currentUser->method('getEmail')->willReturn('sender@example.com');

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($currentUser);

        $capturedEmail = null;
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(static function ($email) use (&$capturedEmail): void {
                $capturedEmail = $email;
            });

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getIriFromResource')->willReturn('/api/technician_on_calls/1');

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $notifier = new TechnicianOnCallNotifier(
            $mailer,
            $normalizer,
            $security,
            $entityManager,
            $iriConverter,
            [],
            $this->createMock(LoggerInterface::class),
            $translator,
        );

        $to = $this->createMock(People::class);
        $to->method('getEmail')->willReturn('recipient@example.com');

        $input = new TechnicianOnCallEmailInput();
        $input->subject = 'TOC #217460';
        $input->message = 'hello';
        $input->to = $to;

        $notifier->sendIntranetEmail($input, $toc, ['message' => 'hello']);

        self::assertInstanceOf(TemplatedEmail::class, $capturedEmail);
        self::assertSame($normalizedToc, $capturedEmail->getContext()['technicianOnCall']);
        self::assertNotInstanceOf(TechnicianOnCall::class, $capturedEmail->getContext()['technicianOnCall']);
    }
}
