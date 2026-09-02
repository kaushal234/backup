<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'common.inc.php';

#[AsCommand(name: 'manufacturing:green-tag:green_tag_due_soon')]
class EquipmentRecordGreenTagNotificationCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Notify ASM if Equipment Record is soon Green Tag';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = <<<'SQL'
SELECT
    people.email,
    er.id,
    er.sn,
    er.dgt_act,
    er.model,
    er.customer_name,
    sol.id AS sol_id
FROM service AS er
LEFT JOIN sor_units AS sor_u ON sor_u.id = er.sor_uid
LEFT JOIN sor_lines AS sol ON sor_u.parent_id = sol.id
LEFT JOIN sor ON sor.id = sol.parent_id
INNER JOIN people ON people.id = sor.asm
WHERE
    DATEDIFF(er.dgt_act, NOW()) = 14
SQL;

        $vars = \tldUtils::getSqlToAssocArray($query);

        foreach ($vars as $er) {
            $subject = "{$er['model']}, {$er['sn']}, {$er['customer_name']}, SOL#{$er['sol_id']} to be GTed in 2 weeks";
            $body = <<<EOF
                The unit {$er['sn']} will be Green Tagged in two weeks, at {$er['dgt_act']}.<br>
                <a href="https://www.tld-gse.com/en/private/product_support/index.ps.php?m[0]=equipment&m[1]=view&id={$er['id']}">
                    Click here to go to the Equipment Record.
            </a><br>
EOF;

            \tldUtils::emailAttachment($er['email'], 'noreply@tld-gse.com', $subject, $body);
            $this->logger->error('An email has been send to'.$er['email']);
        }

        return Command::SUCCESS;
    }
}
