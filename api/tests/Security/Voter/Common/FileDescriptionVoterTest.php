<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\Common;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationFile;
use App\Security\Voter\Common\FileDescriptionVoter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FileDescriptionVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider faqFileProvider
     */
    public function testVoteOnFirstArticleQualificationFile(bool $isGranted, int $expectedVote)
    {
        $faq = $this->prophesize(FirstArticleQualification::class)->reveal();

        $faqFile = new FirstArticleQualificationFile();
        $faqFile->setFirstArticleQualification($faq);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $tokenProphecy = $this->prophesize(TokenInterface::class);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FAQ_DELETE_FILE_VOTER', $faq)->shouldBeCalledTimes(1)->willReturn($isGranted);

        $voter = new FileDescriptionVoter($serviceLocatorProphecy->reveal());

        $result = $voter->vote($tokenProphecy->reveal(), $faqFile, ['DESCRIPTION_FILE_VOTER']);

        self::assertSame($expectedVote, $result);
    }

    public function faqFileProvider(): iterable
    {
        yield 'User allowed to delete FAQ files can edit their description' => [true, Voter::ACCESS_GRANTED];
        yield 'User not allowed to delete FAQ files cannot edit their description' => [false, Voter::ACCESS_DENIED];
    }
}
