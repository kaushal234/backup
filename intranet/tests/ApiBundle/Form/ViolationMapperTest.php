<?php

declare(strict_types=1);

namespace Tests\ApiBundle\Form;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ViolationMapperTest extends TestCase
{
    use ProphecyTrait;

    public function testThatExceptionWithoutViolationsAreHandled()
    {
        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);

        $exception = $this->getException(['hydra:title' => 'Title', 'hydra:description' => 'Description']);

        $formProphecy = $this->prophesize(FormInterface::class);

        $formProphecy->addError(new FormError('Title: Description'))->shouldBeCalledTimes(1)->shouldBeCalledTimes(1)->willReturn($formProphecy->reveal());

        $mapper = new ViolationMapper($accessorProphecy->reveal());

        $mapper->mapToForm($exception, $formProphecy->reveal());
    }

    public function testThatExceptionWithViolationsAreMapped()
    {
        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);

        $exception = $this->getException(['violations' => [
            [
                'propertyPath' => 'collection[2].property',
                'message' => 'We come in peace',
            ],
            [
                'propertyPath' => 'simpleProperty',
                'message' => 'in a bottle',
            ],
        ]]);

        $formProphecy = $this->prophesize(FormInterface::class);
        $form = $formProphecy->reveal();

        $accessorProphecy->isReadable($form, '[collection][2][property]')->shouldBeCalledTimes(1)->willReturn(true);
        $formElementProphecy = $this->prophesize(FormInterface::class);
        $formElementProphecy->addError(new FormError('We come in peace'))->shouldBeCalledTimes(1)->shouldBeCalledTimes(1)->willReturn($formProphecy->reveal());
        $accessorProphecy->getValue($form, '[collection][2][property]')->shouldBeCalledTimes(1)->willReturn($formElementProphecy);

        $accessorProphecy->isReadable($form, '[simpleProperty]')->shouldBeCalledTimes(1)->willReturn(true);
        $formElementProphecy = $this->prophesize(FormInterface::class);
        $formElementProphecy->addError(new FormError('in a bottle'))->shouldBeCalledTimes(1)->shouldBeCalledTimes(1)->willReturn($formProphecy->reveal());
        $accessorProphecy->getValue($form, '[simpleProperty]')->shouldBeCalledTimes(1)->willReturn($formElementProphecy);

        $mapper = new ViolationMapper($accessorProphecy->reveal());

        $mapper->mapToForm($exception, $form);
    }

    public function testThatExceptionWithViolationsMappingIsCustomizable()
    {
        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);

        $exception = $this->getException(['violations' => [
            [
                'propertyPath' => 'collection[2].property',
                'message' => 'We come in peace',
            ],
            [
                'propertyPath' => 'simpleProperty',
                'message' => 'in a bottle',
            ],
        ]]);

        $formProphecy = $this->prophesize(FormInterface::class);
        $form = $formProphecy->reveal();

        $accessorProphecy->isReadable($form, '[this_is_not_the_property_you_are_looking_for]')->shouldBeCalledTimes(1)->willReturn(true);
        $formElementProphecy = $this->prophesize(FormInterface::class);
        $formElementProphecy->addError(new FormError('We come in peace'))->shouldBeCalledTimes(1)->shouldBeCalledTimes(1)->willReturn($formProphecy->reveal());
        $accessorProphecy->getValue($form, '[this_is_not_the_property_you_are_looking_for]')->shouldBeCalledTimes(1)->willReturn($formElementProphecy);

        $accessorProphecy->isReadable($form, '[complex][42][property]')->shouldBeCalledTimes(1)->willReturn(true);
        $formElementProphecy = $this->prophesize(FormInterface::class);
        $formElementProphecy->addError(new FormError('in a bottle'))->shouldBeCalledTimes(1)->shouldBeCalledTimes(1)->willReturn($formProphecy->reveal());
        $accessorProphecy->getValue($form, '[complex][42][property]')->shouldBeCalledTimes(1)->willReturn($formElementProphecy);

        $mapper = new ViolationMapper($accessorProphecy->reveal());

        $mapper->mapToForm($exception, $form, [
            'collection[2].property' => 'this_is_not_the_property_you_are_looking_for',
            'simpleProperty' => 'complex[42].property',
        ]);
    }

    private function getException(array $responseBody)
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode($responseBody),
                [
                    'response_headers' => ['content-type' => 'application/json'],
                    'http_code' => 400,
                ]),
        ]);

        $securityProphecy = $this->prophesize(Security::class);
        $eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->shouldBeCalledOnce()->willReturn('');
        $kernel->isDebug()->shouldBeCalledOnce()->willReturn(false);
        $client = new Client($securityProphecy->reveal(), $eventDispatcherProphecy->reveal(), $kernel->reveal(), $httpClient, [
            'base_uri' => 'https://haproxy:8080',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        try {
            $reponse = $client->request('');
            $reponse->getContent();

            $this->fail();
        } catch (ClientException $e) {
            return $e;
        }
    }
}
