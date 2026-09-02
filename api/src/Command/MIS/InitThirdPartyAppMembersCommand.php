<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\Module\ThirdPartyApp\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:third_party_app:init', description: 'Init third party app members without update tasks.')]
class InitThirdPartyAppMembersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function removeBlackListUsers(): int
    {
        $sql = <<<'SQL'
                DELETE FROM third_party_app_member
                WHERE id IN (
                    SELECT
                        me.id
                    FROM modules m
                             INNER JOIN third_party_app_blacklist b ON b.extended_id = m.id
                             INNER JOIN user u ON u.id = b.people_id
                             INNER JOIN third_party_app_member me ON me.user_id = b.people_id AND me.third_party_app_id = m.id
                    WHERE
                        u.disabled = 0
                      AND u.hidden = 0
                      AND m.discr IN ('third_party_app_extended')
                      AND m.status != 'DISABLED'
                )
            SQL;

        $result = $this->entityManager->getConnection()->executeQuery($sql);

        return $result->rowCount();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 0;

        $this->clearMembers();

        $count += $this->insertBusinessUnitPositionUsers();
        $count += $this->insertWhitelistUsers();
        $count -= $this->removeBlackListUsers();

        $output->writeln(\sprintf('Number of users inserted : %s', $count));

        return Command::SUCCESS;
    }

    protected function insertBusinessUnitPositionUsers(): int
    {
        $sql = <<<'SQL'
                INSERT IGNORE INTO third_party_app_member (user_id, third_party_app_id)
                SELECT DISTINCT
                    u.id,
                    m.id
                FROM modules m
                         INNER JOIN third_party_app_business_unit_position bup ON bup.third_party_app_id = m.id
                         INNER JOIN user u ON u.position_id = bup.position_id AND u.business_unit_id = bup.business_unit_id AND u.discr IN ('people')
                WHERE
                    u.disabled = 0
                    AND u.hidden = 0
                    AND m.discr IN ('third_party_app_extended')
                    AND m.status != 'DISABLED'
            SQL;

        $result = $this->entityManager->getConnection()->executeQuery($sql);

        return $result->rowCount();
    }

    protected function insertWhitelistUsers(): int
    {
        $sql = <<<'SQL'
                INSERT IGNORE INTO third_party_app_member (user_id, third_party_app_id)
                SELECT DISTINCT
                    u.id,
                    m.id
                FROM modules m
                         INNER JOIN third_party_app_whitelist w ON w.extended_id = m.id
                         INNER JOIN user u ON u.id = w.people_id
                WHERE
                    u.disabled = 0
                  AND u.hidden = 0
                  AND m.discr IN ('third_party_app_extended')
                  AND m.status != 'DISABLED'
            SQL;

        $result = $this->entityManager->getConnection()->executeQuery($sql);

        return $result->rowCount();
    }

    protected function clearMembers()
    {
        $cmd = $this->entityManager->getClassMetadata(Member::class);
        $connection = $this->entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $truncateQuery = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeQuery($truncateQuery);
    }
}
