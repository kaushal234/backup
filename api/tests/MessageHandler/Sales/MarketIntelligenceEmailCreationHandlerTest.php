<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Message\Sales\NotifyMarketIntelligenceCreate;
use App\MessageHandler\Sales\MarketIntelligenceEmailCreationHandler;
use App\Notifier\Sales\MarketIntelligence\MarketIntelligenceNotifier;
use App\Notifier\Sales\MarketIntelligence\RecipientsFinder;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MarketIntelligenceEmailCreationHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testHandlerWhenMimIsCreated(): void
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $notifierProphecy = $this->prophesize(MarketIntelligenceNotifier::class);
        $recipientsFinderProphecy = $this->prophesize(RecipientsFinder::class);
        $validatorProphecy = $this->prophesize(ValidatorInterface::class);
        $messageProphecy = $this->prophesize(NotifyMarketIntelligenceCreate::class);

        $constraint = new Email();
        $constraint->mode = 'strict';

        $messageProphecy->getResourceIri()->shouldBeCalledTimes(1)->willReturn('/foo/1');
        $messageProphecy->getUserIri()->shouldBeCalledTimes(1)->willReturn('/bar/1');
        $iriConverterProphecy->getResourceFromIri('/foo/1')->shouldBeCalledTimes(1)->willReturn($mim = new MarketIntelligence());
        $iriConverterProphecy->getResourceFromIri('/bar/1')->shouldBeCalledTimes(1)->willReturn($people = new People());

        $recipientsFinderProphecy->findRecipients($mim)->shouldBeCalledTimes(1)->willReturn([$recipient = (new People())->setEmail('test@tld.fr')]);
        $validatorProphecy->validate('test@tld.fr', Argument::type(Email::class))->shouldBeCalledTimes(1)->willReturn(new ConstraintViolationList());

        $notifierProphecy->sendCreationEmail($mim, $people, 'test@tld.fr')->shouldBeCalledTimes(1);

        $handler = new MarketIntelligenceEmailCreationHandler($iriConverterProphecy->reveal(), $notifierProphecy->reveal(), $recipientsFinderProphecy->reveal(), $validatorProphecy->reveal());
        $handler($messageProphecy->reveal());
    }
}
