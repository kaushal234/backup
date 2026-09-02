<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Action\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\ShowButtonActionType;
use AppBundle\Test\DataTable\Action\Type\ActionTypeTestCase;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionTypeInterface;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\LinkActionType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ShowButtonActionTypeTest extends ActionTypeTestCase
{
    protected ?UrlGeneratorInterface $urlGenerator = null;
    protected ?PropertyAccessorInterface $propertyAccessor = null;

    public function testRoute()
    {
        $this->urlGenerator = $this->createUrlGenerator();
        $this->urlGenerator->method('generate')->willReturn('/kittens/42');

        $this->propertyAccessor = $this->createPropertyAccessor();
        $this->propertyAccessor->method('getValue')->willReturn('42');

        $action = $this->createAction([
            'route' => 'test_route_one_param',
            'translation_domain' => '',
        ]);

        $actionView = $this->createActionView(
            action: $action,
            data: new ApiData(['id' => '42'])
        );

        $this->assertSame('/kittens/42', $actionView->vars['href']);
    }

    protected function getTestedActionType(): ActionTypeInterface
    {
        return new ShowButtonActionType(
            urlGenerator: $this->urlGenerator,
            propertyAccessor: $this->propertyAccessor,
        );
    }

    protected function getAdditionalActionTypes(): array
    {
        return array_merge(
            [
                new LinkActionType(),
                new ButtonActionType(),
                new ActionType(),
            ],
            parent::getAdditionalActionTypes(),
        );
    }

    protected function createUrlGenerator(): MockObject&UrlGeneratorInterface
    {
        return $this->createMock(UrlGeneratorInterface::class);
    }

    protected function createPropertyAccessor(): MockObject&PropertyAccessorInterface
    {
        return $this->createMock(PropertyAccessorInterface::class);
    }
}
