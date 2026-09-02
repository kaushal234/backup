<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaimThreshold\VendorWarrantyClaimThresholdsType;
use AppBundle\Form\Type\Purchasing\VendorWarrantyClaimThreshold\VendorWarrantyClaimThresholdType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/vendor-warranty-claims/thresholds', defaults: ['alvest_module' => 'VWC', 'moduleDomain' => 'vendor_warranty_claim'])]
class VendorWarrantyClaimThresholdController extends AbstractController
{
    final public const VENDOR_WARRANTY_CLAIM_THRESHOLD_URL = 'purchasing/vendor_warranty_claim_thresholds';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, FormFactoryInterface::class]);
    }

    #[Route(path: '', name: 'vendor_warranty_claim_thresholds', methods: ['GET|POST'])]
    #[Template('purchasing/vendor_warranty_claim_threshold/home.html.twig')]
    public function home(Request $request)
    {
        $client = $this->container->get(Client::class);
        $vendorWarrantyClaimThresholds = $client->get(self::VENDOR_WARRANTY_CLAIM_THRESHOLD_URL);

        $editThresholdForms = $this->container->get(FormFactoryInterface::class)->createNamed('editThresholdForms', VendorWarrantyClaimThresholdsType::class, $vendorWarrantyClaimThresholds['hydra:member']);
        $editThresholdForms->handleRequest($request);

        if ($editThresholdForms->isSubmitted() && $editThresholdForms->isValid()) {
            foreach ($editThresholdForms->get('thresholdForms') as $updatedThresholdForm) {
                $response = $this->handleThresholdFormSave($updatedThresholdForm);
                if ($response instanceof RedirectResponse) {
                    return $response;
                }
            }
        }

        $newThresholdForm = $this->container->get(FormFactoryInterface::class)->createNamed('newThresholdForm', VendorWarrantyClaimThresholdType::class);
        $newThresholdForm->handleRequest($request);

        $response = $this->handleThresholdFormSave($newThresholdForm);
        if ($response instanceof RedirectResponse) {
            return $response;
        }

        return [
            'editThresholdForms' => $editThresholdForms->createView(),
            'newThresholdForm' => $newThresholdForm->createView(),
        ];
    }

    private function handleThresholdFormSave($form): ?RedirectResponse
    {
        if ($form->get('submit')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                if (\is_array($data['location'])) {
                    unset($data['location']);
                }
                $this->container->get(Client::class)->save(self::VENDOR_WARRANTY_CLAIM_THRESHOLD_URL, $data);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.threshold.success', [], 'vendor_warranty_claim'));

                return $this->redirectToRoute('vendor_warranty_claim_thresholds');
            } catch (ClientException $e) {
                $this->addFlash('error', $e->getMessage());
                $violationMapper = $this->container->get(ViolationMapper::class);
                $violationMapper->mapToForm($e, $form);

                if (Response::HTTP_FORBIDDEN === $e->getCode()) {
                    $form->get('threshold')->addError(new FormError($this->container->get(TranslatorInterface::class)->trans('vendor_warranty_claim.threshold.forbidden_error', [], 'vendor_warranty_claim')));
                }
            }
        }

        return null;
    }
}
