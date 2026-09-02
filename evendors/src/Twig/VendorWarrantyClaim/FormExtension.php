<?php

declare(strict_types=1);

namespace App\Twig\VendorWarrantyClaim;

use App\DataTransferObject\VendorWarrantyClaim\AddVendorWarrantyClaimComment;
use App\Form\VendorWarrantyClaim\CommentType;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormExtension extends AbstractExtension
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('create_vwc_comment_add_form', $this->createCommentForm(...)),
        ];
    }

    public function createCommentForm(WCVendorWarrantyClaim|NCRVendorWarrantyClaim $claim): FormView
    {
        $url = $this->urlGenerator->generate('vendor-warranty-claim:comment', ['module' => $claim->getModule(), 'id' => $claim->id]);

        $builder = $this->factory->createBuilder(CommentType::class, new AddVendorWarrantyClaimComment());
        $builder->setAction($url);

        return $builder->getForm()->createView();
    }
}
