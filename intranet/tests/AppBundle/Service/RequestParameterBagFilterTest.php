<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\Service;

use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Request\RequestParameterBagFilter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class RequestParameterBagFilterTest extends TestCase
{
    use ProphecyTrait;

    public function testRequestParameterBagFilter()
    {
        $response = new HydraCollection(
            [
                'hydra:member' => [],
                'hydra:search' => [
                    'hydra:mapping' => [
                        ['variable' => 'propertyFilterable'],
                        ['variable' => 'propertyAssociativeArray[key]'],
                        ['variable' => 'propertyAssociativeArrayAlsoFilterable[goodKey]'],
                    ],
                ],
            ]
        );
        $parameters = [
            'propertyFilterable' => 1,
            'propertyNotFilterable' => 2,
            'propertyAssociativeArray' => ['key' => 'value'],
            'propertyAssociativeArrayAlsoFilterable' => ['badKey' => 'value'],
        ];
        $parametersExpected = [
            'propertyFilterable' => 1,
            'propertyAssociativeArray' => ['key' => 'value'],
        ];

        $requestParameterBagFilter = new RequestParameterBagFilter();
        $parametersFiltered = $requestParameterBagFilter->filter($parameters, $response);
        self::assertSame($parametersExpected, $parametersFiltered);
    }
}
