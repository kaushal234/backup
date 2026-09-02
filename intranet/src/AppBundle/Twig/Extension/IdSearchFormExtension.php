<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use AppBundle\Form\Type\IdSearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IdSearchFormExtension extends AbstractExtension
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('id_search_form', [$this, 'renderSearchForm'], ['is_safe' => ['html']]),
        ];
    }

    public function renderSearchForm(
        bool|string $idLabel = 'by_id',
        string $idPlaceholder = '#',
        ?string $redirectRoute = null,
        ?string $apiRoute = null,
    ): string {
        $idSearchForm = $this->formFactory->create(IdSearchType::class, null, [
            'id_label' => $idLabel,
            'id_placeholder' => $idPlaceholder,
            'redirect_route' => $redirectRoute,
            'api_route' => $apiRoute,
        ]);

        return $this->twig->render('id_search_form.html.twig', [
            'form_id' => $idSearchForm->createView(),
        ]);
    }
}
