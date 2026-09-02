<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Message\Sales\NotifyMarketIntelligenceComment;
use App\MessageHandler\Sales\MarketIntelligenceEmailCommentHandler;
use App\Notifier\Sales\MarketIntelligence\MarketIntelligenceNotifier;
use App\Notifier\Sales\MarketIntelligence\RecipientsFinder;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MarketIntelligenceEmailCommentHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testHandlerWhenMimIsCommented(): void
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $notifierProphecy = $this->prophesize(MarketIntelligenceNotifier::class);
        $recipientsFinderProphecy = $this->prophesize(RecipientsFinder::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $messageProphecy = $this->prophesize(NotifyMarketIntelligenceComment::class);

        $messageProphecy->getResourceIri()->shouldBeCalledTimes(1)->willReturn('/foo/1');
        $messageProphecy->getUserIri()->shouldBeCalledTimes(1)->willReturn('/bar/1');
        $messageProphecy->getComment()->shouldBeCalledTimes(1)->willReturn('coucou');
        $iriConverterProphecy->getResourceFromIri('/foo/1')->shouldBeCalledTimes(1)->willReturn($mim = new MarketIntelligence());
        $iriConverterProphecy->getResourceFromIri('/bar/1')->shouldBeCalledTimes(1)->willReturn($people = new People());

        $recipientsFinderProphecy->findRecipients($mim)->shouldBeCalledTimes(1)->willReturn([$recipient = (new People())->setEmail('test@tld.fr')]);
        $validatorProphecy->validate('test@tld.fr', Argument::type(Email::class))->shouldBeCalledTimes(1)->willReturn(new ConstraintViolationList());

        $notifierProphecy->sendCommentEmail($mim, $people, 'test@tld.fr', 'coucou')->shouldBeCalledTimes(1);

        $handler = new MarketIntelligenceEmailCommentHandler($iriConverterProphecy->reveal(), $notifierProphecy->reveal(), $recipientsFinderProphecy->reveal(), $validatorProphecy->reveal());
        $handler($messageProphecy->reveal());
    }
}
