<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Entity\Directory\Position;
use App\Entity\Group;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:insert:people')]
class InsertPeopleCommand extends Command
{
    /**
     * @var string
     */
    final public const ACL_AUTH_INTRANET = 'ACL_AUTH_INTRANET';

    private readonly EntityManagerInterface $em;

    private readonly UserManager $userManager;

    public function __construct(EntityManagerInterface $em, UserManager $userManager)
    {
        parent::__construct();
        $this
            ->setDescription('Generate TLD People in DB')
            ->addArgument('lastname', InputArgument::REQUIRED, 'Lastname of People')
            ->addArgument('firstname', InputArgument::REQUIRED, 'Firstname of People')
            ->addArgument('jobTitle', InputArgument::REQUIRED, 'Job title of People')
            ->addArgument('businessUnit', InputArgument::REQUIRED, 'Business Unit ID# of People')
            ->addArgument('position', InputArgument::REQUIRED, 'Position ID# of People')
            ->addArgument('department', InputArgument::REQUIRED, 'Department ID# of People')
            ->addArgument('supervisor', InputArgument::REQUIRED, 'Supervisor ID# of People')
            ->addArgument('language', InputArgument::REQUIRED, 'Language of People for locale')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email of People')
            ->addOption('phone', 'p', InputArgument::OPTIONAL, 'Office phone number of People')
            ->addOption('mobile', 'm', InputArgument::OPTIONAL, 'Office mobile number of People')
        ;
        $this->em = $em;
        $this->userManager = $userManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleRepository = $this->em->getRepository(People::class);
        $businessUnitRepository = $this->em->getRepository(BusinessUnit::class);
        $positionRepository = $this->em->getRepository(Position::class);
        $departmentRepository = $this->em->getRepository(Department::class);
        $groupRepository = $this->em->getRepository(Group::class);

        $payload = $input->getArguments();
        $phones = [
            'phone' => $input->getOption('phone'),
            'mobile' => $input->getOption('mobile'),
        ];

        try {
            $people = new People();
            $people
                ->setJobTitle($payload['jobTitle'])
                ->setBusinessUnit($businessUnitRepository->find($payload['businessUnit']))
                ->setPosition($positionRepository->find($payload['position']))
                ->setDepartment($departmentRepository->find($payload['department']))
                ->setSupervisor($peopleRepository->find($payload['supervisor']))
                ->setLocale($payload['language'])
                ->setLastname($payload['lastname'])
                ->setFirstname($payload['firstname'])
                ->setEmail($payload['email'])
                ->setUsername($payload['email'])
                ->setDisabled(false)
                ->setHidden(false)
            ;

            /** @var string|null $number */
            foreach ($phones as $type => $number) {
                if (null !== $number) {
                    $people->addPhone((new Phone())
                        ->setType($type)
                        ->setNumber($number)
                    );
                }
            }

            $this->userManager->generatePassword($people);

            $this->em->persist($people);
            $this->em->flush();
        } catch (\Exception $exception) {
            $output->writeln(\sprintf('Could not insert People. Reason : %s', $exception->getMessage()));

            return Command::FAILURE;
        }

        $acl = (new Acl())
                    ->setGroup($groupRepository->findOneBy(['name' => self::ACL_AUTH_INTRANET]))
                    ->setUser($people)
        ;

        try {
            $this->em->persist($acl);
            $this->em->flush();
        } catch (\Exception $exception) {
            $output->writeln(\sprintf('Could not insert ACL for People #%d. Reason : %s', $people->getId(), $exception->getMessage()));

            return Command::FAILURE;
        }

        $output->writeln('People and ACL correctly inserted');

        return 0;
    }
}
