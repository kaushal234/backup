<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422125301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add VendorWarrantyClaimThreshold entity and define initial threshold values';
    }

    public function up(Schema $schema): void
    {
        $mappingCurrencyThreshold = [
            'USD' => 1000,
            'EUR' => 1000,
            'CAD' => 1500,
            'INR' => 50000,
        ];

        $locations = [
            'AERO Specialties' => 'USD',
            'TLD AME' => 'USD',
            'TLD ASI' => 'USD',
            'TLD EUR' => 'EUR',
            'TLD PV' => 'EUR',
            'TLD SHE' => 'CAD',
            'TLD STL' => 'EUR',
            'TLD WIN' => 'USD',
            'TLD LEB' => 'EUR',
            'TLD MTL' => 'EUR',
            'TLD DTV' => 'EUR',
            'TLD WIM' => 'USD',
        ];

        $this->addSql('CREATE TABLE vendor_warranty_claim_threshold (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, threshold INT NOT NULL, INDEX IDX_DEB98FD864D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vendor_warranty_claim_threshold ADD CONSTRAINT FK_DEB98FD864D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');

        foreach ($locations as $locationName => $currencyCode) {
            $location = $this->connection->fetchAssociative(
                'SELECT id FROM directory_location WHERE name = ?',
                [$locationName]
            );

            if (!$location) {
                $this->write("Location not found: {$locationName}");
                continue;
            }

            $threshold = $mappingCurrencyThreshold[$currencyCode] ?? null;
            if (null === $threshold) {
                $this->write("Threshold not defined for currency: {$currencyCode}");
                continue;
            }

            $this->addSql(
                'INSERT INTO vendor_warranty_claim_threshold (location_id, threshold) VALUES (?, ?)',
                [$location['id'], $threshold]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vendor_warranty_claim_threshold DROP FOREIGN KEY FK_DEB98FD864D218E');
        $this->addSql('DROP TABLE vendor_warranty_claim_threshold');
    }
}
