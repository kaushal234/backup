<?php

declare(strict_types=1);

namespace App\Formatter\Snappy\AdapterFactory;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use App\Formatter\Snappy\Adapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class ManualChapter4ContentAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @var string
     */
    final public const PURPOSE = 'pdf_chapter4';

    private readonly ParameterBagInterface $parameters;
    private readonly EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param Manual $manual
     */
    public function getAdapter($manual, string $format): Adapter
    {
        $documentsData = [];
        $categoriesOrder = [
            'Accessories and Options',
            'Body-Chassis',
            'Boom',
            'Bridge',
            'Covers and Panels',
            'Electrical System',
            'Elevator',
            'Hydraulic System',
            'Lifting-Scissors System',
            'PLUMBING',
            'Pneumatic System',
            'Power Plant',
            'Refrigeration System',
            'Suspension, Tires and Brakes',
            'TRANSMISSION',
            'User Interfaces and Cab',
        ];
        foreach ($categoriesOrder as $category) {
            $documentsData[$category] = [];
        }

        /*
         * Manual have an ID when it was created on our database, and document was stored on database also.
         * It is possible to simulate manual from a CBOM directly. In this case manual does not have an ID and documents are created in the factories.
         */
        if (null !== $manual->getId()) {
            $repository = $this->em->getRepository(ManualDocument::class);
            $documents = $repository->findBy(['manual' => $manual, 'type' => ManualDocument::PARTS_DIAGRAM], ['position' => 'ASC']);
        } else {
            $documents = $manual->getDocuments();
        }

        foreach ($documents as $document) {
            if (isset($document->category)) {
                $documentsData[$document->category->name][] = $document;
            }
        }

        $adapter = new Adapter(
            'Pdf/Support/Manual/Chapter4/layout.html.twig',
            [
                'manual' => $manual,
                'documentsData' => $documentsData,
                'numberOfDocuments' => \count($documents),
            ]
        );
        $adapter->setHeaderTemplate('Pdf/Support/Manual/Chapter4/header.html.twig');
        $adapter->setFooterTemplate('Pdf/Support/Manual/Chapter4/footer.html.twig');

        // generate a table of contents automatically, doc here https://packagist.org/packages/knplabs/knp-snappy
        $adapter->addOption('toc', true);

        /*
         * transformation template to add style to the generated toc.
         * Be carefull when you update TOC style.
         * Because some conditions on twig content template depend on TOC lines.
         */
        $tocPath = $this->parameters->get('kernel.project_dir').'/templates/Pdf/Support/Manual/Chapter4';

        try {
            $locale = mb_strtolower((string) ('CH' === ($iso2 = $manual->language) ? 'ZH' : $iso2));
            $file = new File(\sprintf('%s/%s.xsl', $tocPath, 'toc_'.mb_strtolower($locale)));
            $adapter->addOption('xsl-style-sheet', $file->getPathname());
        } catch (FileNotFoundException $exception) {
            $adapter->addOption('xsl-style-sheet', \sprintf('%s/toc.xsl', $tocPath));
        }

        // without this option, you can not read local pdf/jpeg files that will be included in the final pdf
        $adapter->addOption('enable-local-file-access', true);

        return $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object, string $format): bool
    {
        return $object instanceof Manual && self::PURPOSE === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurpose(): string
    {
        return self::PURPOSE;
    }
}
