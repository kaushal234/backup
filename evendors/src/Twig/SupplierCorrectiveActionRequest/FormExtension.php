<?php

declare(strict_types=1);

namespace App\Twig\SupplierCorrectiveActionRequest;

use App\DataTransferObject\SupplierCorrectiveActionRequest\AddSupplierCorrectiveActionRequestComment;
use App\Form\SupplierCorrectiveActionRequest\CommentType;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
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
            new TwigFunction('create_scar_comment_add_form', $this->createCommentForm(...)),
        ];
    }

    public function createCommentForm(SupplierCorrectiveActionRequest $request): FormView
    {
        $url = $this->urlGenerator->generate('supplier-corrective-action-request:comment', ['id' => $request->id]);

        $builder = $this->factory->createBuilder(CommentType::class, new AddSupplierCorrectiveActionRequestComment());
        $builder->setAction($url);

        return $builder->getForm()->createView();
    }
}
