<?php

declare(strict_types=1);

namespace AppBundle\Controller\Manufacturing;

use ApiBundle\Client;
use ApiBundle\Model\User;
use AppBundle\Form\Type\Manufacturing\Timekeeping\CloseTransactionType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/manufacturing/timekeeping', defaults: ['alvest_module' => 'PI'])]
class TimekeepingController extends AbstractController
{
    /** @var string */
    private const END_ACTIVE = 'END-ACTIVE';

    /** @var string */
    private const DIRECT = 'DIRECT';

    /** @var string */
    private const INDIRECT = 'INDIRECT';

    /** @var string */
    private const RESOURCE_URL = '/ion/time_keepings';

    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '', name: 'timekeeping_submit_close_transaction', methods: ['GET', 'POST'])]
    #[Template('manufacturing/timekeeping/close_transaction.html.twig')]
    #[IsGranted('FEATURE_TIMEKEEPING_CLOSE_TRANSACTION')]
    public function close_transaction(Request $request)
    {
        $form = $this->createForm(CloseTransactionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var User $user */
                $user = $this->getUser();
                $data = $form->getData();
                $transaction = $this->client->save(self::RESOURCE_URL, [
                    'employeeNumber' => $data['employeeId'],
                    'transactionType' => self::END_ACTIVE,
                    'endDate' => $data['endDate'],
                    'comment' => \sprintf('unlocked from admin intranet by %s %s', $user->getLastname(), $user->getFirstname()),
                ]);

                if (empty($transaction['lines'])) {
                    $this->addFlash('warning', $this->translator->trans('pio.empty_transaction_message', [], 'pio'));
                }

                foreach ($transaction['lines'] as $transaction) {
                    $this->addFlash('success', $this->translator->trans('time_keeping.transaction_posted', [], 'time_keeping'));
                    if (self::DIRECT === $transaction['transactionType']) {
                        $this->addFlash('success', $this->translator->trans('time_keeping.success_transaction_direct_message', [
                            '%employeeNumber%' => $transaction['employeeNumber'],
                            '%firstname%' => $transaction['firstname'],
                            '%lastname%' => $transaction['lastname'],
                            '%transactionType%' => $transaction['transactionType'],
                            '%productionOrder%' => $transaction['productionOrder'],
                            '%operationNumber%' => $transaction['operationNumber'],
                            '%status%' => $transaction['status'],
                        ], 'time_keeping'));
                    }
                    if (self::INDIRECT === $transaction['transactionType']) {
                        $this->addFlash('success', $this->translator->trans('time_keeping.success_transaction_indirect_message', [
                            '%employeeNumber%' => $transaction['employeeNumber'],
                            '%firstname%' => $transaction['firstname'],
                            '%lastname%' => $transaction['lastname'],
                            '%transactionType%' => $transaction['transactionType'],
                            '%task%' => $transaction['task'],
                            '%status%' => $transaction['status'],
                        ], 'time_keeping'));
                    }
                }
            } catch (ClientException $exception) {
                $errorDescription = json_decode($exception->getResponse()->getContent(false), true);

                $this->addFlash('error', $this->translator->trans('time_keeping.error_transaction_message', [], 'time_keeping'));
                $this->addFlash('error', $errorDescription['hydra:description']);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
