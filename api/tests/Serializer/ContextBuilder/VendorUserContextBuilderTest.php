<?php

declare(strict_types=1);

namespace App\Tests\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Purchasing\VendorUser;
use App\Serializer\ContextBuilder\VendorUserContextBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class VendorUserContextBuilderTest extends TestCase
{
    /**
     * @dataProvider provideSecurityIsGrantedAndResourceClassValues
     */
    public function testCreateFromRequest(bool $isGrantedVendorUserWrite, bool $normalization, Operation $operation, array $expectedGroups): void
    {
        $decoratedBuilder = $this->createMock(SerializerContextBuilderInterface::class);
        $security = $this->createMock(Security::class);
        $contextBuilder = new VendorUserContextBuilder($decoratedBuilder, $security);

        $request = new Request();
        $request->attributes->set('_api_operation', $operation);
        $context = ['resource_class' => VendorUser::class, AbstractNormalizer::GROUPS => ['default_group']];
        $security->method('isGranted')->willReturn($isGrantedVendorUserWrite);

        $decoratedBuilder->method('createFromRequest')->willReturn($context);

        $resultContext = $contextBuilder->createFromRequest($request, $normalization);

        $this->assertSame($expectedGroups, $resultContext['groups']);
    }

    public function provideSecurityIsGrantedAndResourceClassValues(): iterable
    {
        yield 'admin edit' => [true, false, new Put(), ['vendor_user:write_admin']];
        yield 'admin view item' => [true, true, new Get(), ['vendor_user']];
        yield 'not admin view item' => [false, true, new Get(), ['default_group']];
        yield 'not admin edit' => [false, false, new Put(), ['default_group']];
        yield 'not put operation value' => [true, false, new Post(), ['default_group']];
        yield 'not item_operation_name operation name' => [true, false, new GetCollection(), ['default_group']];
    }
}
