<?php

declare(strict_types=1);

namespace App\Tests\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Sales\SalesForecast;
use App\Serializer\ContextBuilder\SalesForecastContextBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SalesForecastContextBuilderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider securityContextProvider
     */
    public function testContextVariesBasedOnGrantedFeatures($feature, $groups)
    {
        $serializerContextBuilderProphecy = $this->prophesize(SerializerContextBuilderInterface::class);
        $authorizationCheckerProphecy = $this->prophesize(AuthorizationCheckerInterface::class);

        $request = new Request();

        $serializerContextBuilderProphecy->createFromRequest($request, false, null)->shouldBeCalledTimes(1)->willReturn([
            'resource_class' => SalesForecast::class,
            'groups' => [],
        ]);

        foreach (['FEATURE_SALES_FORECAST_ADMIN_EDIT', 'MOO_SFR', 'FEATURE_SALES_FORECAST_RESTRICTED_EDIT', 'FEATURE_SALES_FORECAST_FACTORY_EDIT'] as $f) {
            $authorizationCheckerProphecy->isGranted($f)->shouldBeCalledTimes(1)->willReturn($f === $feature);

            if ($f === $feature) {
                break;
            }
        }

        $builder = new SalesForecastContextBuilder($serializerContextBuilderProphecy->reveal(), $authorizationCheckerProphecy->reveal());

        $context = $builder->createFromRequest($request, false);

        self::assertSame($groups, $context['groups']);
    }

    public function securityContextProvider()
    {
        yield 'User is a SFR admin' => [
            'FEATURE_SALES_FORECAST_ADMIN_EDIT',
            ['sales_forecast_admin_edit'],
        ];

        yield 'User is a SFR MOO' => [
            'MOO_SFR',
            ['sales_forecast_admin_edit'],
        ];

        yield 'User is a ASM' => [
            'FEATURE_SALES_FORECAST_RESTRICTED_EDIT',
            ['sales_forecast_restricted_edit'],
        ];

        yield 'User is a PSM' => [
            'FEATURE_SALES_FORECAST_FACTORY_EDIT',
            ['sales_forecast_factory_edit'],
        ];
    }
}
