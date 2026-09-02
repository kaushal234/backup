<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Directory\PositionClassificationFilterType;
use AppBundle\Form\Type\Directory\Position\PositionClassificationBatchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/position-classifications', defaults: ['alvest_module' => 'ESM'])]
class PositionClassificationController extends AbstractController
{
    final public const RESOURCE_URL = 'position_classifications';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class]);
    }

    #[Route(path: '/{id}/edit', name: 'position_classifications_edit', methods: ['GET|POST'])]
    #[Template('directory/position_classification/home.html.twig')]
    public function index(#[ApiValueResolverAttribute] ApiData $businessUnit, Request $request)
    {
        $positionClassifications = $this->container->get(Client::class)->findBy(self::RESOURCE_URL, ['businessUnit' => $businessUnit->getIriId()]);
        $positions = $this->container->get(Client::class)
            ->findBy('positions', [
                'users.businessUnit' => $businessUnit->getIriId(),
                'users.disabled' => false,
                'discriminator' => ['people'],
            ]);
        $positionClassificationsIndexedByPosition = [];
        $positionClassificationsIndexedByCategory = [];
        foreach ($positionClassifications->getSimpleArrayCopy() as $positionsClassification) {
            $positionClassificationsIndexedByCategory[$positionsClassification['positionCategory']['@id']] = $positionsClassification;
            foreach ($positionsClassification['positions'] as $position) {
                $positionClassificationsIndexedByPosition[$position['@id']] = $positionsClassification;
            }
        }

        $data = [];
        foreach ($positions->getSimpleArrayCopy() as $position) {
            $positionId = Iri::id($position['@id']);
            $data[$positionId] = ['position' => $position['@id'], 'positionCode' => $position['code']];
            if ($positionClassification = $positionClassificationsIndexedByPosition[$position['@id']] ?? false) {
                $data[$positionId] += [
                    '@id' => $positionClassification['@id'],
                    'positionCategory' => $positionClassification['positionCategory'],
                ];
            }
        }

        $form = $this->createForm(PositionClassificationBatchType::class, ['positionClassifications' => $data], ['businessUnit' => $businessUnit->getIri()]);

        $businessUnitIri = $businessUnit->getIri();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $preparedData = [];
            foreach ($data['positionClassifications'] as $positionClassification) {
                if ('' === $positionClassification['positionCategory']) {
                    continue;
                }

                $classificationPositions = $preparedData[$positionClassification['positionCategory']]['positions'] ?? [];
                $classificationPositions[] = $positionClassification['position'];
                sort($classificationPositions);

                $preparedData[$positionClassification['positionCategory']] = [
                    'businessUnit' => $businessUnitIri,
                    'positionCategory' => $positionClassification['positionCategory'],
                    'positions' => $classificationPositions,
                ];
            }

            $distinctCategories = array_unique(array_column($preparedData, 'positionCategory'));

            foreach ($positionClassifications as $positionClassification) {
                if (!\in_array($positionClassification['positionCategory']['@id'], $distinctCategories, true)) {
                    $this->container->get(Client::class)->remove(self::RESOURCE_URL, $positionClassification->getIriId());
                }
            }

            foreach ($preparedData as $payload) {
                if ($existingClassification = $positionClassificationsIndexedByCategory[$payload['positionCategory']] ?? false) {
                    $existingPositions = array_column($existingClassification['positions'], '@id');
                    sort($existingPositions);

                    if ($existingPositions === $payload['positions']) {
                        continue;
                    }

                    $payload['@id'] = $existingClassification['@id'];
                }

                try {
                    $this->container->get(Client::class)->save(self::RESOURCE_URL, $payload);
                    $this->addFlash(
                        'success',
                        $this->container->get(TranslatorInterface::class)->trans('directory.position_classifications.edit.success', [], 'directory')
                    );
                } catch (ClientException $e) {
                    $errors = json_decode($e->getResponse()->getContent(false), true);
                    foreach ($errors['violations'] ?? [] as $error) {
                        $this->addFlash(
                            'warning',
                            $error['message']
                        );
                    }
                }
            }

            return $this->redirectToRoute('position_classifications_edit', ['id' => $businessUnit->getIriId()]);
        }

        return [
            'positionClassifications' => $positionClassifications,
            'businessUnit' => $businessUnit,
            'positions' => $positions,
            'form' => $form->createView(),
            'formFilter' => $this->createForm(PositionClassificationFilterType::class)->createView(),
        ];
    }
}
