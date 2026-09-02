<?php

declare(strict_types=1);

namespace App\Notifier\Sales\Customer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Entity\Sales\Customer;
use App\Entity\User;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Module\ModuleRepository;
use App\Repository\Sales\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class CustomerNotifier
{
    public function __construct(
        private MailerInterface $mailer,
        private NormalizerInterface $normalizer,
        private IriConverterInterface $iriConverter,
        private EntityManagerInterface $entityManager,
        private RecipientsFinder $recipientsFinder,
    ) {
    }

    public function sendWatchListReport(): void
    {
        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->entityManager->getRepository(Customer::class);
        $iris = [];
        /** @var Customer $customer */
        foreach ($customerRepository->findCustomersOnWatchListwithoutERPReference() as $customer) {
            $iris[] = $this->iriConverter->getIriFromResource($customer);
        }

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        /** @var ModuleRepository $moduleRepository */
        $moduleRepository = $this->entityManager->getRepository(Module::class);
        $module = $moduleRepository->findByName('CRT');
        $moo = $module->getOperationalOwner();

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $peopleRepository->findGroupsMembers(['ROLE_SAM'])))
            ->cc($moo->getEmail())
            ->subject('customer.watch_list_report.subject')
            ->htmlTemplate('Emails/Sales/Customer/watch_list_report.html.twig')
            ->context($this->buildContext($iris));

        $this->mailer->send($email);
    }

    public function sendEdition(Customer $customer, User $user, array $changeSet, array $secondarySalesRepresentativeChangeSet): void
    {
        $context = [
            'changeSet' => $changeSet,
            'secondarySalesRepresentativeChangeSet' => $secondarySalesRepresentativeChangeSet,
            'id' => $customer->getId(),
            'customer_name' => $customer->getName(),
            'user' => $this->normalizer->normalize($user, null, ['groups' => ['people_public']]),
            'customer_normalized' => $this->normalizer->normalize($customer, null, ['groups' => ['customer_export']]),
        ];

        if (empty($recipients = $this->recipientsFinder->findRecipients($customer))) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('customer.edition.subject')
            ->htmlTemplate('Emails/Sales/Customer/customer_edition.html.twig')
            ->context($context);

        $this->mailer->send($email);
    }

    public function sendThirdPartiesCustomerReApprovalEmail(Customer $customer): void
    {
        $context = [
            'id' => $customer->getId(),
            'customer_name' => $customer->getName(),
            'customer_normalized' => $this->normalizer->normalize($customer, null, ['groups' => ['customer_export']]),
        ];

        $recipients = $this->recipientsFinder->findRecipients($customer);

        if (empty($recipients)) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('customer.re_approval.subject')
            ->htmlTemplate('Emails/Sales/Customer/customer_re_approval.html.twig')
            ->context($context)
        ;

        $this->mailer->send($email);
    }

    private function buildContext(array $customersIris): array
    {
        $normalizedCustomers = [];
        foreach ($customersIris as $iri) {
            $normalizedCustomers[] = $this->normalizer->normalize($this->iriConverter->getResourceFromIri($iri), null, ['groups' => ['customer', 'country']]);
        }

        return [
            'customers' => $normalizedCustomers,
        ];
    }
}
