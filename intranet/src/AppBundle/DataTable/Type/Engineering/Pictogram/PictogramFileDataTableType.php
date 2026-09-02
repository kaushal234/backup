<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Type\Engineering\Pictogram;

use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Controller\Engineering\Pictogram\PictogramController;
use AppBundle\DataTable\Column\Type\DateColumnType;
use AppBundle\DataTable\Column\Type\FileSizeColumnType;
use AppBundle\DataTable\Type\AbstractSimpleDataTableType;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class PictogramFileDataTableType extends AbstractSimpleDataTableType
{
    public const string RESOURCE = 'engineering/pictograms/%s/files';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $tokenManager,
        private readonly Security $security,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('filePath', TextColumnType::class, [
                'label' => 'files',
                'header_translation_domain' => 'engineering_pictogram',
            ])
            ->addColumn('description', TextColumnType::class, [
                'label' => 'demo.fields.file_description',
                'header_translation_domain' => 'demo',
            ])
            ->addColumn('main', BooleanColumnType::class, [
                'label' => 'picture',
                'header_translation_domain' => 'engineering_pictogram',
            ])
            ->addColumn('createdAt', DateColumnType::class, [
                'label' => 'demo.fields.created_at',
                'header_translation_domain' => 'demo',
                'format' => 'Y-m-d H:i:s',
                'visible' => false,
            ])
            ->addColumn('size', FileSizeColumnType::class, [
                'label' => 'size',
                'header_translation_domain' => 'messages',
                'visible' => false,
            ])
        ;

        $builder
            ->addRowAction('download', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $file): string {
                    return $this->urlGenerator->generate('pictogram_files_show', [
                        'id' => $file->getIriId(),
                        'pictogramId' => Iri::id($file['pictogram']),
                    ]);
                },
                'icon' => 'fa7-solid:download',
            ])
            ->addRowAction('delete', ButtonActionType::class, [
                'label' => '',
                'href' => function (ApiData $file): string {
                    return $this->urlGenerator->generate('pictogram_files_delete', [
                        'id' => $file->getIriId(),
                        'pictogramId' => Iri::id($file['pictogram']),
                        '_token' => $this->tokenManager->getToken(PictogramController::DELETE_TOKEN_FILE),
                    ]);
                },
                'confirmation' => [
                    'label_title' => 'delete_file.popup.title',
                    'label_description' => 'delete_file.popup.message',
                    'translation_domain' => 'engineering_pictogram',
                ],
                'icon' => 'fa7-solid:trash',
                'variant' => 'danger',
                'visible' => $this->security->isGranted('FEATURE_PICTOGRAM_UPDATE'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'personalization_enabled' => true,
        ]);
    }
}
