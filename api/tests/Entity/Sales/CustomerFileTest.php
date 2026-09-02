<?php

declare(strict_types=1);

namespace App\Tests\Entity\Sales;

use App\Entity\Legal\Contract;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerFile;
use PHPUnit\Framework\TestCase;

/**
 * Expected functionality: a CustomerFile can optionally be flagged as a contract file. When it is, and a
 * Contract gets created from it, the link between the two must be kept so users can navigate from the
 * ECUST file back to the contract it created.
 */
final class CustomerFileTest extends TestCase
{
    public function testARegularCustomerFileIsNotAContractFileByDefault(): void
    {
        $file = new CustomerFile();

        self::assertFalse($file->isContract, 'Existing/regular customer files must not be treated as contract files unless explicitly flagged.');
        self::assertNull($file->contract, 'A customer file has no linked contract unless a contract has actually been created from it.');
    }

    public function testMarkingAFileAsAContractDoesNotLinkAContractByItself(): void
    {
        $file = new CustomerFile();
        $file->isContract = true;

        self::assertTrue($file->isContract);
        self::assertNull($file->contract, 'Flagging a file as "is contract" only marks intent; the link is only set once the contract has actually been created.');
    }

    public function testTheContractCreatedFromAFileCanBeLinkedBackToIt(): void
    {
        $file = new CustomerFile();
        $file->isContract = true;

        $contract = $this->createMock(Contract::class);
        $file->contract = $contract;

        self::assertSame($contract, $file->contract, 'Once a contract has been created from this file, the persistent link back to it must be available.');
    }

    public function testACustomerFileIsAlwaysAttachedToItsCustomer(): void
    {
        $file = new CustomerFile();
        $customer = new Customer();

        $file->setCustomer($customer);

        self::assertSame($customer, $file->getCustomer(), 'The ECUST -> Contract flow relies on knowing which customer the contract file was submitted from.');
    }
}
