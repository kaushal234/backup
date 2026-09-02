<?php

declare(strict_types=1);

namespace App\Twig\VendorWarrantyClaim;

use App\DataTransferObject\VendorWarrantyClaim\EditVendorWarrantyClaim;
use App\Form\VendorWarrantyClaim\VendorWarrantyClaimType;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormEditVendorWarrantyClaimExtension extends AbstractExtension
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('edit_vwc_form', $this->createCommentForm(...)),
        ];
    }

    public function createCommentForm(WCVendorWarrantyClaim|NCRVendorWarrantyClaim $claim): FormView
    {
        $url = $this->urlGenerator->generate('vendor-warranty-claim:edit', ['module' => $claim->getModule(), 'id' => $claim->id]);

        $builder = $this->factory->createBuilder(VendorWarrantyClaimType::class, new EditVendorWarrantyClaim());
        $builder->setAction($url);

        return $builder->getForm()->createView();
    }
}
