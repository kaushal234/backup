<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\ThirdPartyApp;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Mis\ModuleController;
use AppBundle\Form\Type\Mis\Module\ThirdPartyApp\AccountReviewType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/modules/{moduleId}/account_reviews', defaults: ['alvest_module' => 'MOD', 'breadcrumb_label' => 'menu.modules.title', 'moduleDomain' => 'mis_modules'])]
class AccountReviewController extends AbstractController
{
    public const RESOURCE_URL = 'modules/third_party_app';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, FileManager::class, FileStreamedResponseFactory::class, TranslatorInterface::class]);
    }

    #[Route(path: '', name: 'mis_modules_account_review_index', methods: 'GET')]
    #[Template('mis/modules/third_party_app/account_review/index.html.twig')]
    public function index(#[ApiValueResolverAttribute(parameters: ['id' => 'moduleId', 'allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_LIGHT, ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module)
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $client = $this->container->get(Client::class);
        $accountReviews = $client->findBy(\sprintf('%s/%s/account_reviews', self::RESOURCE_URL, $module->getIriId()));

        return [
            'module' => $module,
            'accountReviews' => $accountReviews,
        ];
    }

    #[Route(path: '/add', name: 'mis_modules_account_review_add', methods: 'GET|POST')]
    #[Template('mis/modules/third_party_app/account_review/add.html.twig')]
    public function create(Request $request, #[ApiValueResolverAttribute(parameters: ['id' => 'moduleId', 'allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_LIGHT, ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module): array|Response
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $form = $this->createForm(AccountReviewType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->container->get(Client::class);
                $data = $form->getData();
                $data['thirdPartyApp'] = $module->getIri();
                $accountReviewSave = $client->save(\sprintf('%s/account_reviews', self::RESOURCE_URL), $data);
                $file = $form->get('file')->getData();
                if (null !== $file) {
                    $this->container->get(FileManager::class)->uploadFile($accountReviewSave, $file, self::RESOURCE_URL.'/account_reviews', null, 'files', false, true);
                }

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.account_review.success.added', [], 'mis'));

                return $this->redirectToRoute('mis_modules_account_review_index', ['moduleId' => $module->getIriId()]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('%s: %s', $this->container->get(TranslatorInterface::class)->trans('mis.account_review.error.added', [], 'mis'), $e->getMessage()));
            }
        }

        return [
            'module' => $module,
            'form' => $form,
        ];
    }

    #[Route(path: '/{id}', name: 'mis_modules_account_review_show', methods: 'GET')]
    #[Template('mis/modules/third_party_app/account_review/show.html.twig')]
    public function read(Request $request, #[ApiValueResolverAttribute(parameters: ['id' => 'moduleId', 'allowedTypes' => [ModuleController::TYPE_THIRD_PARTY_APP_LIGHT, ModuleController::TYPE_THIRD_PARTY_APP_EXTENDED]])] ApiData $module, int $id): array
    {
        $this->denyAccessUnlessGranted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', '/modules/'.$module->getIriId());

        $client = $this->container->get(Client::class);
        $accountReview = $client->find(self::RESOURCE_URL.'/account_reviews', $id);

        return [
            'module' => $module,
            'accountReview' => $accountReview,
        ];
    }

    #[Route(path: '/{accountReviewId}/files/{id}', name: 'mis_modules_account_review_file', methods: 'GET')]
    public function showFile(int $accountReviewId, int $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::RESOURCE_URL.'/account_reviews', $accountReviewId, $id));
    }
}
