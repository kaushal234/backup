<?php

declare(strict_types=1);

namespace App\Job\Equotes;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'equotes:sync')]
class ExportUsersToEquotesCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Synchronize users between intranet and equotes';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $synchronizedUsers = \tldGroup::inGroup('gg_equotes_users');

        $users = [];
        foreach ($synchronizedUsers as $synchronizedUser) {
            if (isset($users[$synchronizedUser['id']])) {
                continue;
            }
            $users[$synchronizedUser['id']] = $synchronizedUser;
        }

        \tldUtils::connectDb('equotes');
        $e = \tldUtils::sqlQuery('DELETE FROM users', null, ['src' => 'equotes']);
        if (\is_string($e)) {
            $this->logger->critical('Could not execute deletion query. Aborting.');

            return Command::FAILURE;
        }

        foreach ($users as $user) {
            $user = \tldUtils::cleanupFormInput($user);
            $query = <<<SQL
INSERT INTO users
      SET
        Code_user={$user['id']},
        Titre_user='Mr.',
        Nom_user='{$user['lastname']}',
        Prenom_user='{$user['firstname']}',
        Type_user='{$user['department']}',
        Fonction_UK_user='{$user['title']}',
        Email_user='{$user['email']}',
        Fax_user='{$user['fax']}',
        Tel_user='{$user['phone']}',
        Tel2_user='{$user['direct_phone']}',
        Telhome_user='{$user['home_phone']}',
        Telmob_user='{$user['mobile']}',
        Adr_user='{$user['address']}',
        Div_user='{$user['division']}',
        Password_user=MD5('{$user['password']}')
SQL;
            $insertError = \tldUtils::sqlQuery($query, null, ['src' => 'equotes']);
            if (\is_string($insertError)) {
                $this->logger->error("Error: $insertError");
                continue;
            }
            $this->logger->info(\sprintf('User %s created in equotes', $user['email']));
        }

        return Command::SUCCESS;
    }
}
