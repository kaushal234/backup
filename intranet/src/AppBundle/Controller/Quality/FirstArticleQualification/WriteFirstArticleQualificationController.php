<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality\FirstArticleQualification;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Quality\FirstArticleQualification\FirstArticleQualificationType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/quality/first-article-qualifications', defaults: ['alvest_module' => 'FAQ', 'moduleDomain' => 'first_article_qualifications'])]
class WriteFirstArticleQualificationController extends AbstractController
{
    private const RESOURCE_URL = 'quality/first_article_qualifications';

    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route(path: '/add', name: 'first_article_qualifications_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'first_article_qualifications_edit', methods: ['GET', 'POST'])]
    #[Template('quality/first_article_qualification/write.html.twig')]
    public function __invoke(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $firstArticleQualification = null)
    {
        $isEditMode = null !== $firstArticleQualification;

        if ($isEditMode) {
            $status = $firstArticleQualification['status'] ?? null;
            $resourceId = $firstArticleQualification['@id'] ?? null;

            if ('QUALIFIED' === $status && !$this->isGranted('FEATURE_FIRST_ARTICLE_QUALIFICATION_EDIT_QUALIFIED')) {
                throw new AccessDeniedException();
            }

            if (!\in_array($status, ['REJECTED', 'CONDITIONAL'], true) && !$this->isGranted('FEATURE_FAQ_PLAN_WRITE', $resourceId)) {
                throw new AccessDeniedException();
            }
        } else {
            $firstArticleQualification = $this->buildInitialFirstArticleQualificationData($request);
        }

        $form = $this->createForm(FirstArticleQualificationType::class, $firstArticleQualification)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rawData = $form->getData();
            $rawData = $rawData instanceof ApiData ? $rawData->toArray() : $rawData;
            $data = array_intersect_key($rawData, $form->all() + ['@id' => null, 'supplierNumber' => null]);

            if (isset($data['partNumbers']) && \is_array($data['partNumbers'])) {
                foreach ($data['partNumbers'] as $key => $line) {
                    if (!empty($line['number'])) {
                        $data['partNumbers'][$key]['number'] = self::extractItemCode($line['number']) ?? $line['number'];
                    }
                }
            }

            try {
                $faq = $this->client->save(self::RESOURCE_URL, $data);

                $flashMessage = $isEditMode
                    ? 'first_article_qualification.messages.successfully_updated'
                    : 'first_article_qualification.messages.successfully_created';

                $this->addFlash('success', $this->translator->trans($flashMessage, [], 'first_article_qualification'));

                return $this->redirectToRoute('first_article_qualifications_show', ['id' => Iri::id($faq)]);
            } catch (ClientException $exception) {
                $this->violationMapper->mapToForm($exception, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'firstArticleQualification' => $firstArticleQualification,
            'sequenceId' => $request->query->get('sequenceId'),
            'title' => $isEditMode ? 'first_article_qualification.titles.edit' : 'first_article_qualification.titles.add',
        ];
    }

    /**
     * Builds the initial form data for the "add" mode.
     *
     * Some legacy callers redirect here with "erp" and/or "partNumbers" query
     * params already known (e.g. from an ERP task or a purchase request),
     * so we pre-fill the Factory and try to resolve revision/description
     * for each part number when possible.
     */
    private function buildInitialFirstArticleQualificationData(Request $request): array
    {
        $partNumbers = $request->query->all('partNumbers');
        $erp = $request->query->get('erp');
        $location = null;

        if (null !== $erp) {
            try {
                $locations = $this->client->findBy('locations', ['erp' => $erp], [], ['raw_results' => true])['hydra:member'] ?? [];
                $location = $locations[0]['@id'] ?? null;

                if (null === $location) {
                    $this->addFlash('warning', $this->translator->trans('first_article_qualification.messages.factory_not_found', ['%erp%' => $erp], 'first_article_qualification'));
                }
            } catch (ClientException) {
                $this->addFlash('warning', $this->translator->trans('first_article_qualification.messages.factory_not_found', ['%erp%' => $erp], 'first_article_qualification'));
            }
        }

        if (empty($partNumbers)) {
            $partNumbersData = [
                ['number' => '', 'revision' => '', 'description' => ''],
            ];
        } else {
            $partNumbersData = array_map(
                function (string $pn) use ($erp): array {
                    $revision = '';
                    $description = '';

                    if (null !== $erp) {
                        try {
                            $item = $this->client->find('ion/items', \sprintf('site=%s;item=%s', $erp, $pn), ['raw_results' => true]);
                            $revision = $item['revision'] ?? '';
                            $description = $item['itemDescription'] ?? '';
                        } catch (ClientException) {
                            $this->addFlash('warning', $this->translator->trans('first_article_qualification.messages.part_number_not_found', ['%number%' => $pn, '%erp%' => $erp], 'first_article_qualification'));
                        }
                    }

                    return ['number' => $pn, 'revision' => $revision, 'description' => $description];
                },
                $partNumbers
            );
        }

        return [
            'planDefinitionDueDate' => (new \DateTime('+7 days'))->format('Y-m-d\TH:i:sO'),
            'dueDate' => (new \DateTime('+21 days'))->format('Y-m-d\TH:i:sO'),
            'eap' => $request->query->get('eap'),
            'meap' => $request->query->get('meap'),
            'partNumbers' => $partNumbersData,
            'location' => $location,
            'erp' => $erp,
        ];
    }

    private static function extractItemCode(string $iri): ?string
    {
        foreach (explode(';', $iri) as $part) {
            if (str_starts_with($part, 'item=')) {
                return mb_substr($part, 5);
            }
        }

        return null;
    }
}
