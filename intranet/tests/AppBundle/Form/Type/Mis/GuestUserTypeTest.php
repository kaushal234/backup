<?php

declare(strict_types=1);

namespace AppBundle\Tests\Form\Type\Mis\GuestUser;

use AppBundle\Form\Type\Mis\GuestUser\GuestUserType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class GuestUserTypeTest extends TestCase
{
    use ProphecyTrait;

    public function testPreSetDataFillsDefaultsWhenAddingAndDataIsEmpty(): void
    {
        $listener = $this->extractPreSetDataListener([
            'add' => true,
            'default_business_unit' => 'default-bu',
            'default_premise' => 'default-premise',
        ]);

        $event = $this->createFormEvent([
            'businessUnit' => null,
            'premise' => '',
        ]);
        $event->setData(Argument::any())->shouldBeCalledOnce();

        $listener($event->reveal());
    }

    public function testPreSetDataDoesNotOverrideExistingValues(): void
    {
        $listener = $this->extractPreSetDataListener([
            'add' => true,
            'default_business_unit' => 'default-bu',
            'default_premise' => 'default-premise',
        ]);

        $submittedData = [
            'businessUnit' => 'existing-bu',
            'premise' => 'existing-premise',
        ];

        $event = $this->createFormEvent($submittedData);
        $event->setData([
            'businessUnit' => 'existing-bu',
            'premise' => 'existing-premise',
        ])->shouldBeCalledOnce();

        $listener($event->reveal());
    }

    public function testPreSetDataDoesNothingWhenNotInAddMode(): void
    {
        // Arrange
        $listener = $this->extractPreSetDataListener([
            'add' => false,
            'default_business_unit' => 'default-bu',
            'default_premise' => 'default-premise',
        ]);

        $event = $this->createFormEvent([
            'businessUnit' => null,
            'premise' => null,
        ]);
        $event->getData()->shouldNotBeCalled();
        $event->setData(Argument::any())->shouldNotBeCalled();

        // Act
        $listener($event->reveal());
    }

    /**
     * Builds a GuestUserType, calls buildForm() against a stubbed
     * FormBuilderInterface, and returns the closure registered
     * for FormEvents::PRE_SET_DATA so it can be tested in isolation.
     */
    private function extractPreSetDataListener(array $options): \Closure
    {
        $listener = null;

        $builderProphecy = $this->prophesize(FormBuilderInterface::class);
        $builderProphecy
            ->addEventListener(FormEvents::PRE_SET_DATA, Argument::type(\Closure::class))
            ->will(static function (array $args) use (&$listener, $builderProphecy) {
                $listener = $args[1];

                return $builderProphecy->reveal();
            });

        $builderProphecy->add(Argument::cetera())->willReturn($builderProphecy->reveal());
        $builderProphecy->remove(Argument::any())->willReturn($builderProphecy->reveal());

        $type = new GuestUserType();
        $type->buildForm($builderProphecy->reveal(), $options);

        $this->assertInstanceOf(\Closure::class, $listener, 'PRE_SET_DATA listener was not registered.');

        return $listener;
    }

    private function createFormEvent(array $data): \Prophecy\Prophecy\ObjectProphecy
    {
        $formProphecy = $this->prophesize(FormInterface::class);

        $eventProphecy = $this->prophesize(FormEvent::class);
        $eventProphecy->getForm()->willReturn($formProphecy->reveal());
        $eventProphecy->getData()->willReturn($data);

        return $eventProphecy;
    }
}
