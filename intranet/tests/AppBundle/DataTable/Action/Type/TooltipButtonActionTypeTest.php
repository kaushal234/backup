<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Action\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\TooltipButtonActionType;
use AppBundle\Test\DataTable\Action\Type\ActionTypeTestCase;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionTypeInterface;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\LinkActionType;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class TooltipButtonActionTypeTest extends ActionTypeTestCase
{
    public function testSimple()
    {
        $action = $this->createAction([
            'tooltip_title' => 'test tooltip title',
            'translation_domain' => '',
        ]);

        $actionView = $this->createActionView(
            action: $action,
            data: new ApiData([])
        );

        $this->assertSame('test tooltip title', $actionView->vars['tooltip_title']);
        $this->assertSame('top', $actionView->vars['tooltip_placement']);
    }

    public function testCallback()
    {
        $action = $this->createAction([
            'tooltip_placement' => 'bottom',
            'tooltip_title' => static function (ApiData $data) {
                return \sprintf('test title with data %s', $data['id']);
            },
            'translation_domain' => '',
        ]);

        $actionView = $this->createActionView(
            action: $action,
            data: new ApiData(['id' => 42])
        );

        $this->assertSame('test title with data 42', $actionView->vars['tooltip_title']);
        $this->assertSame('bottom', $actionView->vars['tooltip_placement']);
    }

    public function testInvalidTooltipPlacement()
    {
        $this->expectException(InvalidOptionsException::class);

        $action = $this->createAction([
            'tooltip_placement' => 'wrong placement',
            'tooltip_title' => 'test',
            'translation_domain' => '',
        ]);

        $actionView = $this->createActionView(
            action: $action,
            data: new ApiData([])
        );
    }

    protected function getTestedActionType(): ActionTypeInterface
    {
        return new TooltipButtonActionType();
    }

    protected function getAdditionalActionTypes(): array
    {
        return array_merge(
            [
                new ButtonActionType(),
                new LinkActionType(),
                new ActionType(),
            ],
            parent::getAdditionalActionTypes(),
        );
    }
}
