<?php

declare(strict_types=1);

namespace AppBundle\Controller\Legal;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Http\ZipStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Legal\ContractDataTableType;
use AppBundle\Form\Type\Legal\ContractAiAnalyzeType;
use AppBundle\Form\Type\Legal\ContractType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/legal/contracts', defaults: ['alvest_module' => 'CRS', 'moduleDomain' => 'contract'])]
class ContractController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    final public const RESOURCE_URL = 'contracts';
    public const string DELETE_TOKEN_FILE = 'delete_contract_file';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, FileManager::class, FileStreamedResponseFactory::class, FormFactoryInterface::class, ZipStreamedResponseFactory::class, SecurityService::class]);
    }

    #[Route(path: '', name: 'contract_home', methods: ['GET', 'POST'])]
    #[Template('legal/contract/home.html.twig')]
    public function home(Request $request)
    {
        $datatable = $this->createDataTable(ContractDataTableType::class, ContractDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'contractDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'contract_show', methods: ['GET', 'POST'])]
    #[Template('legal/contract/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $contract)
    {
        return [
            'contract' => $contract,
        ];
    }

    #[Route(path: '/{id}/show/files_ajax', name: 'contract_show_file_ajax', methods: ['GET'])]
    #[Template('legal/contract/partial/files_ajax.html.twig')]
    public function ajax_list_files(#[ApiValueResolverAttribute] ApiData $contract,
    ): array {
        return compact('contract');
    }

    #[Route(path: '/{contractId}/file/{fileId}', name: 'contract_download_file', methods: ['GET'])]
    public function download_file(int $contractId, int $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('contracts/%d/files/%d',
            $contractId, $fileId));
    }

    #[Route(path: '/{contractId}/files/{fileId}/delete', name: 'contract_delete_file', methods: ['GET'])]
    public function delete_file(int $contractId, int $fileId, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN_FILE, $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('contract_show', ['id' => $contractId]);
        }
        $this->container->get(Client::class)->remove(\sprintf('contracts/%d/files', $contractId), $fileId);
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('task.message.delete_file', [], 'task'));

        return $this->redirectToRoute('contract_show', ['id' => $contractId]);
    }

    #[Route(path: '/{id}/edit', name: 'contract_edit', methods: ['GET', 'POST'])]
    #[Template('legal/contract/edit.html.twig')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $contract, Request $request)
    {
        $form = $this->createForm(ContractType::class, $contract, ['is_edit' => true])
            ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $payload = $form->getData();
                unset($payload['files'], $payload['createdBy'], $payload['childContracts'], $payload['alvestSignatories']);
                $this->container->get(Client::class)->save(
                    \sprintf('%s/%s', self::RESOURCE_URL, $contract['id']),
                    $payload
                );

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('legal.alert.success_edit', [], 'legal')
                );

                return $this->redirectToRoute('contract_show', ['id' => $contract['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'contract' => $contract,
        ];
    }

    #[Route(path: '/{id}/status', name: 'contract_status', methods: ['GET', 'POST'])]
    #[Template('legal/contract/status.html.twig')]
    public function edit_status(int $id)
    {
        return ['id' => $id];
    }

    #[Route(path: '/add', name: 'contract_add', methods: ['GET', 'POST'])]
    #[Template('legal/contract/add.html.twig')]
    public function add(Request $request)
    {
        $aiData = $request->getSession()->get('contract_ai_data');
        $aiFile = $request->getSession()->get('contract_ai_file');
        $sourceCustomerFileId = $request->getSession()->get('contract_ai_source_customer_file_id');

        $form = $this->createForm(ContractType::class, null, ['is_edit' => false])
            ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $payload = [];
                $payload = array_merge($payload, $form->getData());
                $contract = $this->container->get(Client::class)->save(self::RESOURCE_URL, $payload);

                // Keep the link between the source ECUST (customer) file and the contract it created,
                // so users can navigate from the customer file back to this contract.
                if (null !== $sourceCustomerFileId) {
                    try {
                        $this->container->get(Client::class)->save('files', [
                            '@id' => \sprintf('/files/%d', $sourceCustomerFileId),
                            'contract' => $contract['@id'],
                        ]);
                    } catch (ClientException) {
                        // The contract was created successfully; the back-link on the source file is best-effort.
                    }
                }

                $request->getSession()->remove('contract_ai_data');
                $request->getSession()->remove('contract_ai_file');
                $request->getSession()->remove('contract_ai_filename');
                $request->getSession()->remove('contract_ai_source_customer_file_id');

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('legal.alert.success_add', [], 'legal'));

                return $this->redirectToRoute('contract_show', ['id' => $contract['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'props' => [
                'aiData' => $aiData,
                'aiFile' => $aiFile,
            ],
        ];
    }

    #[Route(path: '/{id}/follow', name: 'trouble_ticket_follow', methods: 'GET|POST')]
    public function follow(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $contract)
    {
        /** @var ?User $user */
        $user = $this->getUser();
        if (null === $user) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('trouble_ticket.errors.user_not_found', [], 'trouble_ticket'));

            return $this->redirectToRoute('contract_show', ['id' => $contract->getIriId()]);
        }

        try {
            $this->container->get(Client::class)->save('subscriptions', ['resource' => $contract->getIri(), 'user' => $user->getIriId()]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('legal.alert.success_follow', [], 'legal'));
        } catch (ClientException $exception) {
            $this->addFlash('error', \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('legal.alert.error_follow', [], 'legal'), $exception->getMessage()));
        }

        return $this->redirectToRoute('contract_show', ['id' => $contract->getIriId()]);
    }

    #[Route(path: '/ai-analyze', name: 'contract_ai_analyze', methods: ['GET', 'POST'])]
    #[Template('legal/contract/ai_analyze.html.twig')]
    public function aiAnalyze(Request $request)
    {
        $form = $this->createForm(ContractAiAnalyzeType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();

                $multiPart = [
                    'file' => DataPart::fromPath(
                        $file->getPathname(),
                        $file->getClientOriginalName()
                    ),
                ];

                $formData = new FormDataPart($multiPart);

                $response = $this->container->get(Client::class)->request(
                    'contracts/extract',
                    null,
                    null,
                    Request::METHOD_POST,
                    [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]
                );

                $responseData = json_decode($response->getContent(), true);

                if (!$responseData) {
                    throw new \Exception('AI analysis failed: no data returned');
                }

                $fileContent = file_get_contents($file->getPathname());
                $base64File = base64_encode($fileContent);

                $request->getSession()->set('contract_ai_data', $responseData);
                $request->getSession()->set('contract_ai_filename', $file->getClientOriginalName());
                $request->getSession()->set('contract_ai_file', [
                    'filename' => $file->getClientOriginalName(),
                    'mimeType' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'base64' => $base64File,
                ]);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans(
                        'legal.alert.ai_analyze_success',
                        [],
                        'legal'
                    )
                );

                return $this->redirectToRoute('contract_add');
            } catch (\Throwable $e) {
                $this->addFlash(
                    'error',
                    $this->container->get(TranslatorInterface::class)->trans(
                        'legal.alert.ai_analyze_error',
                        ['%error%' => $e->getMessage()],
                        'legal'
                    )
                );
            }
        }

        return [
            'props' => [
                'actionUrl' => $this->generateUrl('contract_ai_analyze'),
            ],
        ];
    }
}
