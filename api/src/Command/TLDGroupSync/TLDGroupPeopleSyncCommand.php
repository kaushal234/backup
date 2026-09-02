<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Manager\Directory\PeopleManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:group:sync:people')]
class TLDGroupPeopleSyncCommand extends Command
{
    private readonly EntityManagerInterface $em;

    private readonly Connection $wordpressConnection;

    private readonly PeopleManager $peopleManager;

    public function __construct(
        EntityManagerInterface $em,
        Connection $wordpressConnection,
        PeopleManager $peopleManager
    ) {
        parent::__construct();
        $this->setDescription('Sync People to TLD Group Wordpress database');

        $this->em = $em;
        $this->wordpressConnection = $wordpressConnection;
        $this->peopleManager = $peopleManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $businessUnitRepository = $this->em->getRepository(BusinessUnit::class);

        $q = $this->wordpressConnection->getDatabasePlatform()->getTruncateTableSQL('tld_people');
        $this->wordpressConnection->executeStatement($q);

        $queryBuilder = $this->em->getRepository(People::class)->createQueryBuilder('p');

        $queryBuilder
            ->addSelect('businessUnit')
            ->addSelect('bu_location')
            ->addSelect('position')
            ->addSelect('acls')
            ->addSelect('department')
            ->addSelect('phones')
            ->addSelect('gr')
            ->join('p.businessUnit', 'businessUnit')
            ->join('businessUnit.location', 'bu_location')
            ->join('p.position', 'position')
            ->join('p.acls', 'acls')
            ->join('p.department', 'department')
            ->join('p.phones', 'phones')
            ->join('acls.group', 'gr')
            ->andWhere('p.disabled = :disabled')
            ->setParameter('disabled', false)
        ;

        /** @var People[] $people */
        $people = $queryBuilder->getQuery()->getResult();

        $businessUnitLAC = $businessUnitRepository->findOneBy(['name' => 'TLD LAC']);
        $businessUnitMEAI = $businessUnitRepository->findOneBy(['name' => 'TLD MEAI']);
        $businessUnitEUR = $businessUnitRepository->findOneBy(['name' => 'TLD EUR']);
        $businessUnitSTL = $businessUnitRepository->findOneBy(['name' => 'TLD STL']);
        $businessUnitAME = $businessUnitRepository->findOneBy(['name' => 'TLD AME']);

        foreach ($people as $person) {
            if (
                null !== $person->getBusinessUnit()
                && null !== $person->getPosition()
                && 'Spare Parts Manager' === $person->getJobTitle()
                && 'TLD AME' === $person->getBusinessUnit()->getName()
                && 'SPARE PARTS MANAGER' === $person->getPosition()->getDescription()
            ) {
                $person->setBusinessUnit($businessUnitLAC);
                $this->insert($person, $output, 'SPM AME');
                $person->setBusinessUnit($businessUnitAME);
                $this->insert($person, $output, 'SPM LAC');
            }

            if (
                null !== $person->getBusinessUnit()
                && null !== $person->getPosition()
                && 'TLD EUR' === $person->getBusinessUnit()->getName()
                && 'SPARE PARTS MANAGER' === $person->getPosition()->getDescription()
            ) {
                $person->setBusinessUnit($businessUnitMEAI);
                $this->insert($person, $output, 'SPM MEAI');
                $person->setBusinessUnit($businessUnitEUR);
                $this->insert($person, $output, 'SPM EUR');
                $person->setBusinessUnit($businessUnitSTL);
                $this->insert($person, $output, 'SPM EUR');
            }

            if ($this->peopleManager->hasPublicPicture($person)) {
                $this->insert($person, $output, 'Public Profile');
            }
        }

        return 0;
    }

    private function insert(People $person, OutputInterface $output, string $reason)
    {
        $qb = $this->wordpressConnection->createQueryBuilder();

        $phones = [
            Phone::TYPE_FAX => '',
            Phone::TYPE_HOME => '',
            Phone::TYPE_MOBILE => '',
            Phone::TYPE_PHONE => '',
            Phone::TYPE_RECEPTION => '',
        ];

        foreach ($person->getPhones() as $phone) {
            $phones[$phone->getType()] = $phone->getNumber();
        }

        $businessUnit = $person->getBusinessUnit();

        $imageUrl = null !== $person->getPhoto() ? 'https://api.tld-group.com/public/people/'.$person->getId().'/photo/'.$person->getPhoto()->getId() : '';

        $qb
            ->insert('tld_people')
            ->setValue('id', ':id')
            ->setValue('firstname', ':firstname')
            ->setValue('lastname', ':lastname')
            ->setValue('div_id', ':div_id')
            ->setValue('bu_id', ':bu_id')
            ->setValue('dpt', ':dpt')
            ->setValue('fct', ':fct')
            ->setValue('title', ':title')
            ->setValue('phone', ':phone')
            ->setValue('direct_phone', ':direct_phone')
            ->setValue('mobile', ':mobile')
            ->setValue('email', ':email')
            ->setValue('img_url', ':img_url')
            ->setParameters([
                'id' => $person->getLegacyId(),
                'firstname' => $person->getFirstname(),
                'lastname' => $person->getLastname(),
                'div_id' => null !== $businessUnit && null !== $businessUnit->getRegion() ? $businessUnit->getRegion()->getLegacyId() : 0,
                'bu_id' => null !== $businessUnit ? $businessUnit->getLegacyId() : 0,
                'dpt' => (string) $person->getDepartment(),
                'fct' => null !== $person->getPosition() ? $person->getPosition()->getDescription() : '',
                'title' => $person->getJobTitle(),
                'phone' => $phones[Phone::TYPE_RECEPTION],
                'direct_phone' => $phones[Phone::TYPE_PHONE],
                'mobile' => $phones[Phone::TYPE_MOBILE],
                'email' => $person->getEmail(),
                'img_url' => $imageUrl,
            ])
        ;

        do {
            $success = $this->executeQuery($qb);
            if (!$success) {
                $parameters = $qb->getParameters();
                $parameters['id'] += 10_000;
                $qb->setParameters($parameters);
            }
        } while (!$success);

        $output->writeln(\sprintf('%s inserted (%s)', $person, $reason));
    }

    private function executeQuery(QueryBuilder $queryBuilder): bool
    {
        try {
            $this->wordpressConnection->executeQuery($queryBuilder->getSQL(), $queryBuilder->getParameters());

            return true;
        } catch (UniqueConstraintViolationException $uniqueConstraintViolationException) {
            return false;
        }
    }
}
