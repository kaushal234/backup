<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter\Quality;

use App\Entity\Directory\People;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Security\Voter\Quality\FirstArticleQualification\FirstArticleQualificationDeleteFileVoter;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FirstArticleQualificationDeleteFileVoterTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider voterProvider
     */
    public function testVoter(People $people, FirstArticleQualification $faq, int $vote, ?string $feature = null)
    {
        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $securityProphecy = $this->prophesize(Security::class);
        $tokenProphecy = $this->prophesize(TokenInterface::class);
        $tokenProphecy->getUser()->willReturn($people);

        $serviceLocatorProphecy->get(Security::class)->shouldBeCalledTimes(1)->willReturn($securityProphecy->reveal());
        $securityProphecy->isGranted('FEATURE_FAQ_DELETE_FILE')->shouldBeCalledTimes(1)->willReturn('FEATURE_FAQ_DELETE_FILE' === $feature);

        $firstArticleQualificationDeleteFileVoter = new FirstArticleQualificationDeleteFileVoter($serviceLocatorProphecy->reveal());

        $result = $firstArticleQualificationDeleteFileVoter->vote($tokenProphecy->reveal(), $faq, ['FAQ_DELETE_FILE_VOTER']);

        self::assertSame($vote, $result);
    }

    public function voterProvider()
    {
        $people = new People();
        $owner = new People();
        $poster = new People();
        $member = new People();
        $buyer = new People();

        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        yield 'User has the feature to delete file' => [
            $people,
            $faqProphecy->reveal(),
            Voter::ACCESS_GRANTED,
            'FEATURE_FAQ_DELETE_FILE',
        ];

        $faqProphecy = $this->prophesize(FirstArticleQualification::class);
        $faqProphecy->getOwner()->shouldBeCalledTimes(1)->willReturn($owner);
        $faqProphecy->getPoster()->shouldNotBeCalled();
        yield 'Owner can delete file' => [
            $owner,
            $faqProphecy->reveal(),
            Voter::ACCESS_GRANTED,
        ];

        $faqProphecy->getPoster()->shouldBeCalledTimes(1)->WillReturn($poster);
        $faqProphecy->getBuyer()->shouldNotBeCalled();
        yield 'Poster can delete file' => [
            $poster,
            $faqProphecy->reveal(),
            Voter::ACCESS_GRANTED,
        ];

        $faqProphecy->getBuyer()->shouldBeCalledTimes(1)->WillReturn($buyer);
        $faqProphecy->getMembers()->shouldNotBeCalled();
        yield 'Buyer can delete file' => [
            $buyer,
            $faqProphecy->reveal(),
            Voter::ACCESS_GRANTED,
        ];

        $faqProphecy->getMembers()->shouldBeCalledTimes(1)->willReturn(new ArrayCollection([$member]));
        yield 'Member can delete file' => [
            $member,
            $faqProphecy->reveal(),
            Voter::ACCESS_GRANTED,
        ];

        yield 'Random people without feature cannot delete file' => [
            $people,
            $faqProphecy->reveal(),
            Voter::ACCESS_DENIED,
        ];
    }
}
