<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Action\Type;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Action\Type\TemplateActionType;
use AppBundle\Test\DataTable\Action\Type\ActionTypeTestCase;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ActionTypeInterface;

class TemplateActionTypeTest extends ActionTypeTestCase
{
    public function testString()
    {
        $action = $this->createAction([
            'template_path' => 'test_string_template_path',
            'template_vars' => [
                'var1' => 'test_array_template_vars',
            ],
            'translation_domain' => '',
        ]);

        $actionView = $this->createActionView(
            action: $action,
        );

        $this->assertSame('test_string_template_path', $actionView->vars['template_path']);
        $this->assertSame('test_array_template_vars', $actionView->vars['template_vars']['var1']);
    }

    public function testCallable()
    {
        $action = $this->createAction([
            'template_path' => static fn ($vars) => $vars['data']['test1'].' '.$vars['data']['test2'],
            'template_vars' => static fn ($vars) => [$vars['data']['test1'] => $vars['data']['test2']],
            'translation_domain' => '',
        ]);

        $actionView = $this->createActionView(
            action: $action,
            data: new ApiData([
                'test1' => 'oh putain',
                'test2' => 'laurent',
            ])
        );

        $this->assertSame('oh putain laurent', $actionView->vars['template_path']);
        $this->assertSame('laurent', $actionView->vars['template_vars']['oh putain']);
    }

    protected function getTestedActionType(): ActionTypeInterface
    {
        return new TemplateActionType();
    }

    protected function getAdditionalActionTypes(): array
    {
        return array_merge(
            [
                new ActionType(),
            ],
            parent::getAdditionalActionTypes(),
        );
    }
}
