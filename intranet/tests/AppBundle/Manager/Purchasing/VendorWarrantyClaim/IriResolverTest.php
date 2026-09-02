<?php

declare(strict_types=1);

namespace Tests\AppBundle\Manager\Purchasing\VendorWarrantyClaim;

use AppBundle\Controller\Purchasing\VendorWarrantyClaimController;
use AppBundle\Manager\Purchasing\VendorWarrantyClaim\IriResolver;
use PHPUnit\Framework\TestCase;

class IriResolverTest extends TestCase
{
    private IriResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new IriResolver();
    }

    public function testResolveReturnsNcrIri(): void
    {
        $vendorWarrantyClaim = [
            '@type' => 'NcrVendorWarrantyClaim',
        ];

        self::assertSame(
            VendorWarrantyClaimController::NCR_VENDOR_WARRANTY_CLAIM_URL,
            $this->resolver->resolve($vendorWarrantyClaim)
        );
    }

    public function testResolveReturnsWcIri(): void
    {
        $vendorWarrantyClaim = [
            '@type' => 'WcVendorWarrantyClaim',
        ];

        self::assertSame(
            VendorWarrantyClaimController::WC_VENDOR_WARRANTY_CLAIM_URL,
            $this->resolver->resolve($vendorWarrantyClaim)
        );
    }

    public function testResolveThrowsExceptionForUnsupportedType(): void
    {
        $vendorWarrantyClaim = [
            '@type' => 'UnknownType',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported VendorWarrantyClaim type "UnknownType"');

        $this->resolver->resolve($vendorWarrantyClaim);
    }

    public function testResolveThrowsExceptionWhenTypeIsMissing(): void
    {
        $vendorWarrantyClaim = [];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported VendorWarrantyClaim type "undefined"');

        $this->resolver->resolve($vendorWarrantyClaim);
    }
}
