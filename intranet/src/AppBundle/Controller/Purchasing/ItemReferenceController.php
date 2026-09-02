<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing;

use ApiBundle\Client;
use AppBundle\Form\Type\IdSearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/purchasing/item_reference', defaults: ['alvest_module' => 'XREF', 'moduleDomain' => 'xref'])]
class ItemReferenceController extends AbstractController
{
    public const RESOURCE_URL = 'ion/item_by_vendors';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class]);
    }

    #[Route(path: '', name: 'xref_home', methods: ['GET|POST'])]
    #[Template('purchasing/vendor_item/list.html.twig')]
    public function list(Request $request)
    {
        $vendorItems = null;

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'Vendor Part Number',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $vendorItems = $this->container->get(Client::class)->get(\sprintf(self::RESOURCE_URL.'/%s', mb_strtoupper($id)));
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('vendor items #%s does not exist', $id));
            }
        }

        return [
            'vendorItemForm' => $idSearchForm->createView(),
            'vendorItem' => $vendorItems,
        ];
    }
}
