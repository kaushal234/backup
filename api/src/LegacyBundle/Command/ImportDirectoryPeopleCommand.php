<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Entity\User;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\AddressHelper;
use LegacyBundle\Command\Helper\ImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface;

#[AsCommand(name: 'legacy:import:directory:people')]
class ImportDirectoryPeopleCommand extends Command
{
    private readonly ImportHelper $helper;

    private readonly Connection $legacyConnection;

    private readonly PasswordHasherFactoryInterface $passwordHasherFactory;

    private readonly AddressHelper $addressHelper;

    /**
     * ImportDirectoryPeopleCommand constructor.
     */
    public function __construct(ImportHelper $helper, Connection $legacyConnection, PasswordHasherFactoryInterface $encoderFactory, AddressHelper $addressHelper)
    {
        parent::__construct();
        $this->setDescription('Imports people from legacy people table');
        $this->helper = $helper;
        $this->legacyConnection = $legacyConnection;
        $this->passwordHasherFactory = $encoderFactory;
        $this->addressHelper = $addressHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var LegacyPasswordHasherInterface $passwordHasher */
        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        // Import business units
        $sql = <<<'SQL'
            SELECT * FROM people
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);
        $this->helper->progressiveImport(
            $output, $stmt, People::class, 'legacyId', 'id',
            function (People $people, array $data) use ($passwordHasher) {
                $people
                    ->setNickname($data['nickname'])
                    ->setJobTitle($data['title'])
                    ->setAddress($this->addressHelper->parseAddress($data['address']))
                    ->setLegacyId((int) $data['id'])
                    ->setUsername($data['email'])
                    ->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'] ?: null)
                    ->setDisabled('Y' === $data['disabled'])
                    ->setHidden('1' === $data['hidden'])
                    ->setEmail($data['email'])
                    ->setSalt(bin2hex(random_bytes(32)))
                ;

                $people->setEncodedPassword($passwordHasher->hash($data['password'], $people->getSalt()));
            }
        );

        return 0;
    }
}
