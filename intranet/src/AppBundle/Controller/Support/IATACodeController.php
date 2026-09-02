<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Support\IATACodesFilterType;
use AppBundle\Form\Type\Support\EditIATACodeType;
use AppBundle\Form\Type\Support\IATACodeType;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/iata-codes', defaults: ['alvest_module' => 'APC'])]
class IATACodeController extends AbstractController
{
    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
    }

    #[Route(path: '', name: 'support_iata_code_list')]
    #[Template('support/iata_codes/list.html.twig')]
    public function list(Request $request)
    {
        $formFilter = $this
            ->formFactory
            ->createNamed(
                '',
                IATACodesFilterType::class, [],
                [
                    'action' => $this->generateUrl('support_iata_code_list'),
                    'method' => 'GET',
                ]);

        $iataCodes = [];
        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = $formFilter->getData();
            $parameters['pagination'] = false;
            $parameters['order'] = ['code' => 'asc'];

            try {
                $iataCodes = $this->client->findBy('iata_codes', $parameters);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFilter);
                $iataCodes = [];
            }
        }

        return [
            'iataCodes' => $iataCodes,
            'formFilter' => $formFilter->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'support_iata_code_show', methods: 'GET')]
    #[Template('support/iata_codes/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => ['airport', 'busStation', 'ferryPort', 'heliport', 'metropolitanArea', 'offLinePoint', 'railwayStation']])] ApiData $iataCode)
    {
        return ['iataCode' => $iataCode];
    }

    #[Route(path: '/add', name: 'support_iata_code_add', methods: 'GET|POST')]
    #[Template('support/iata_codes/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_IATA_CODE_WRITE') or is_granted('MOO_ER')"))]
    public function add(Request $request)
    {
        $form = $this->createForm(IATACodeType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formData = $form->getData();
                $iataCode = $this->client->save($this->getResourceName($formData['type']), $formData);

                $this->addFlash(
                    'success',
                    $this->translator->trans('support.iata.messages.success.add', ['%code%' => $iataCode['code'], '%type%' => $iataCode['type']], 'support')
                );

                return $this->redirectToRoute('support_iata_code_show', ['id' => $iataCode['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'support_iata_code_edit', methods: 'GET|POST')]
    #[Template('support/iata_codes/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_IATA_CODE_WRITE') or is_granted('MOO_ER')"))]
    public function edit(#[ApiValueResolverAttribute(parameters: ['allowedTypes' => ['airport', 'busStation', 'ferryPort', 'heliport', 'metropolitanArea', 'offLinePoint', 'railwayStation']])] ApiData $iataCode, Request $request)
    {
        $form = $this->createForm(EditIATACodeType::class, $iataCode);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save($this->getResourceName($iataCode['type']), $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('support.iata.messages.success.edit', ['%code%' => $iataCode['code'], '%type%' => $iataCode['type']], 'support')
                );

                return $this->redirectToRoute('support_iata_code_show', ['id' => $iataCode['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'iataCode' => $iataCode,
        ];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getResourceName($type)
    {
        $name = str_replace([' ', '-'], '', $type);
        $inflector = InflectorFactory::create()->build();

        return $inflector->pluralize($inflector->tableize($name));
    }
}
