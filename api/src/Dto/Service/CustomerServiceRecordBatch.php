<?php

declare(strict_types=1);

namespace App\Dto\Service;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Service\CustomerServiceRecordBatchController;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/service/customer_service_records/multiple_closure',
            controller: CustomerServiceRecordBatchController::class,
            security: "is_granted('FEATURE_CUSTOMER_SERVICE_RECORD_CLOSE')",
            output: false,
            validate: true,
            name: 'multiple_closure',
        ),
    ],
    denormalizationContext: ['groups' => ['customer_service_record:batch']]
)]
#[AppAssert\WorkflowStatus]
class CustomerServiceRecordBatch
{
    #[Groups(['customer_service_record:batch'])]
    private Collection $customerServiceRecords;

    public function __construct()
    {
        $this->customerServiceRecords = new ArrayCollection();
    }

    /**
     * @return Collection<AbstractCustomerServiceRecord>
     */
    public function getCustomerServiceRecords(): Collection
    {
        return $this->customerServiceRecords;
    }

    public function addCustomerServiceRecord(AbstractCustomerServiceRecord $customerServiceRecords): self
    {
        if (!$this->customerServiceRecords->contains($customerServiceRecords)) {
            $this->customerServiceRecords->add($customerServiceRecords);
        }

        return $this;
    }

    public function removeCustomerServiceRecord(AbstractCustomerServiceRecord $customerServiceRecords): self
    {
        $this->customerServiceRecords->removeElement($customerServiceRecords);

        return $this;
    }
}
