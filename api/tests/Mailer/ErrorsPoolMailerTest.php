<?php

declare(strict_types=1);

namespace App\Tests\Mailer;

use App\Entity\Directory\People;
use App\Mailer\ErrorsPoolMailer;
use Faker\Generator;
use Faker\Provider\en_US\Person;
use Faker\Provider\en_US\Text;
use Faker\Provider\Internet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ErrorsPoolMailerTest extends TestCase
{
    /**
     * @var int[]
     */
    final public const POOLS = ['c@rlephilip.pe' => 5, 'gr@assiotan.to' => 4];

    public function testAddingErrorsCreateMultipleEmails()
    {
        $errors = $this->getDummyErrors();

        /** @var MailerInterface|MockObject $mailerMock */
        $mailerMock = $this->getMockBuilder(MailerInterface::class)->disableOriginalConstructor()->getMock();

        $mailerMock
            ->expects(self::exactly(\count(self::POOLS)))
            ->method('send')
        ;

        $poolMailer = new ErrorsPoolMailer(
            $mailerMock
        );

        foreach ($errors as $error) {
            [$people, $message] = $error;
            $poolMailer->addError($people, $message);
        }

        self::assertSame(\count(self::POOLS), $poolMailer->countEmails());

        $k = 1;
        foreach (self::POOLS as $key => $amount) {
            $mail = $poolMailer->getEmail($k);

            self::assertNotNull($mail);

            $context = $mail->getContext();
            self::assertArrayHasKey('errors', $context);
            self::assertCount($amount, $context['errors']);

            $errors = $context['errors'];
            foreach ($errors as $error) {
                self::assertArrayHasKey('message', $error);
                self::assertArrayHasKey('link', $error);
            }

            self::assertInstanceOf(TemplatedEmail::class, $mail);
            self::assertSame('Emails/Generic/errors.html.twig', $mail->getHtmlTemplate());
            ++$k;
        }

        $poolMailer->send('subject', 'template');
    }

    private function getDummyErrors(): array
    {
        $faker = new Generator();
        $faker->addProvider(new Person($faker));
        $faker->addProvider(new Text($faker));
        $faker->addProvider(new Internet($faker));

        $errors = [];

        $k = 1;
        foreach (self::POOLS as $email => $amount) {
            for ($i = 1; $i <= $amount; ++$i) {
                $people = $this->getMockBuilder(People::class)->getMock();
                $people
                    ->method('getId')
                    ->willReturn($k);
                $people
                    ->method('getEmail')
                    ->willReturn($faker->email());

                $errors[] = [$people, $faker->realText(256)];
            }
            ++$k;
        }

        return $errors;
    }
}
