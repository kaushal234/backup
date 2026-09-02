<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20220131081702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Switch RE-APPROVAL to PENDING RE-APPROVAL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE customers SET status='PENDING RE-APPROVAL' WHERE status='RE-APPROVAL'");
        $this->addSql("UPDATE customers
                            LEFT JOIN customer_customer_type ON customers.id = customer_customer_type.customer_id
                            LEFT JOIN customer_types ON customer_customer_type.customer_type_id = customer_types.id
                            SET status='PENDING'
                            WHERE status='PENDING RE-APPROVAL'
                                AND customer_types.name NOT IN ('Equipment broker', 'Agent', 'Distributor', 'GSE parts broker')");
        $this->addSql("UPDATE customers SET status='APPROVED' WHERE status='PENDING RE-APPROVAL'");
    }
}
