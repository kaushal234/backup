<?php

declare(strict_types=1);

namespace App\Twig\VendorWarrantyClaim;

use App\DataTransferObject\VendorWarrantyClaim\AddSupplierCorrectiveActionRequest;
use App\Form\VendorWarrantyClaim\SupplierCorrectiveActionRequestType;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormSupplierCorrectiveActionRequestExtension extends AbstractExtension
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('create_scar_from_vwc_form', $this->createCommentForm(...)),
        ];
    }

    public function createCommentForm(WCVendorWarrantyClaim|NCRVendorWarrantyClaim $claim): FormView
    {
        $url = $this->urlGenerator->generate('vendor-warranty-claim:scar:add', ['module' => $claim->getModule(), 'id' => $claim->id]);

        $builder = $this->factory->createBuilder(SupplierCorrectiveActionRequestType::class, new AddSupplierCorrectiveActionRequest());
        $builder->setAction($url);

        return $builder->getForm()->createView();
    }
}
