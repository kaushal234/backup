<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Column\Type;

use ApiBundle\Model\ApiData;
use Kreyu\Bundle\DataTableBundle\Column\Type\AbstractColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TocCommentsColumnType extends AbstractColumnType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getParent(): ?string
    {
        return TemplateColumnType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'property_path' => false,
            'sort' => false,
            'export' => false,
            'personalizable' => false,
            'template_path' => 'bundles/KreyuDataTableBundle/column/icon_remote_preview.html.twig',
            'template_vars' => fn (ApiData $data): array => [
                'icon' => 'mingcute:comment-line',
                'iconAttr' => ['width' => '18', 'height' => '18'],
                'resource' => 'comments',
                'query' => [
                    // "resource" here is the Comment API filter name (SearchFilter on Activity::$resource), unrelated to the "resource" key above.
                    'resource' => $data->getIri(),
                    'itemsPerPage' => 5,
                ],
                'order' => ['createdAt' => 'desc'],
                'template' => 'service/technician_on_call/partial/_comments_preview.html.twig',
                'templateVars' => [],
                'title' => $this->translator->trans(
                    'toc.fields.recent_comments_title',
                    ['%id%' => $data->getIriId()],
                    'technician_on_call'
                ),
                'href' => $this->urlGenerator->generate('technician_on_calls_show', ['id' => $data->getIriId()]),
            ],
        ]);
    }
}
