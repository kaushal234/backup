<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Mis;

use ApiBundle\Model\ApiData;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class SupportTeamDataTableType extends AbstractDataTableType
{
    public const string RESOURCE = 'mis/support_teams';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('id', TextColumnType::class, [
                'label' => 'display.table.scar_files.headers.id',
                'header_translation_domain' => 'messages',
                'sort' => true,
            ])
            ->addColumn('name', TextColumnType::class, [
                'label' => 'support_team.fields.name',
                'header_translation_domain' => 'support_team',
            ])
        ;

        $builder
            ->setSearchHandler(static function (ApiProxyQuery $query, string $search) {
                $query->search($search);
            })
            ->setDefaultSortingData(SortingData::fromArray([
                'name' => 'desc',
            ]))
            ->addRowAction('edit', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $supportTeam): string {
                    return $this->urlGenerator->generate('support_team_edit', [
                        'id' => $supportTeam->getIriId(),
                    ]);
                },
                'icon' => 'fa7-solid:edit',
                'visible' => $this->security->isGranted('FEATURE_EDIT_SUPPORT_TEAM'),
            ])
            ->addRowAction('delete', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $category): string {
                    return $this->urlGenerator->generate('support_team_delete', [
                        'id' => $category->getIriId(),
                        '_token' => $this->tokenManager->getToken('delete_support_team'),
                    ]);
                },
                'confirmation' => [
                    'label_title' => 'support_team.delete.popup.title',
                    'label_description' => 'support_team.delete.popup.message',
                    'translation_domain' => 'support_team',
                ],
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
                'visible' => $this->security->isGranted('FEATURE_DELETE_SUPPORT_TEAM'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'support_team.menu.home',
            'translation_domain' => 'support_team',
        ]);
    }
}
