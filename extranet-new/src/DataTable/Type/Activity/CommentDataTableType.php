<?php

declare(strict_types=1);

namespace App\DataTable\Type\Activity;

use App\DataTable\Column\DateColumnType;
use App\DataTable\Column\PeopleColumnType;
use App\Sdk\Resource\Comment;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\HtmlColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AutoconfigureTag(name: 'kreyu_data_table.type')]
class CommentDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('createdAt', DateColumnType::class, [
                'label' => 'fields.created_at',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('user', PeopleColumnType::class, [
                'label' => 'fields.poster',
                'header_translation_domain' => 'messages',
            ])
            ->addColumn('message', HtmlColumnType::class, [
                'label' => 'activity.comment.comment',
                'header_translation_domain' => 'messages',
            ])
        ;

        $builder
            ->addRowAction('download', ButtonActionType::class, [
                'label' => '',
                'href' => function (Comment $comment): ?string {
                    if (null === $comment->file) {
                        return null;
                    }

                    return $this->urlGenerator->generate('comments:file', [
                        'id' => $comment->id,
                        'fileId' => $comment->file->id,
                    ]);
                },
                'icon_attr' => [
                    'type' => 'fa7-solid:download',
                ],
                'attr' => ['download' => true],
                'visible' => static function (Comment $comment): bool {
                    return null !== $comment->file;
                },
            ])
        ;

        $builder->setDefaultSortingData(SortingData::fromArray(['createdAt' => 'desc']));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => 'activity.comment.name',
            'translation_domain' => 'messages',
        ]);
    }
}
