<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Acl;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Department;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Entity\Directory\Position;
use App\Entity\Directory\Premise;
use App\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:edit:user')]
class EditUserCommand extends Command
{
    private readonly EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Edit existing users')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Api Id of the the user'
            )
            ->addOption(
                'lastname',
                'ln',
                InputOption::VALUE_OPTIONAL,
                'Lastname of the user'
            )
            ->addOption(
                'firstname',
                'fn',
                InputOption::VALUE_OPTIONAL,
                'Firstname of the user'
            )
            ->addOption(
                'email',
                'email',
                InputOption::VALUE_OPTIONAL,
                'Email of the user'
            )
            ->addOption(
                'erpLogin',
                'el',
                InputOption::VALUE_OPTIONAL,
                'LN Login of the user'
            )
            ->addOption(
                'erpIdentifier',
                'ei',
                InputOption::VALUE_OPTIONAL,
                'LN employee ID of the user'
            )
            ->addOption(
                'jobTitle',
                'j',
                InputOption::VALUE_OPTIONAL,
                'Job title of the user'
            )
            ->addOption(
                'businessUnit',
                'bu',
                InputOption::VALUE_OPTIONAL,
                'Business Unit ID# of the user'
            )
            ->addOption(
                'position',
                'po',
                InputOption::VALUE_OPTIONAL,
                'Position ID# of the user'
            )
            ->addOption(
                'department',
                'dep',
                InputOption::VALUE_OPTIONAL,
                'Department ID# of the user'
            )
            ->addOption(
                'supervisor',
                's',
                InputOption::VALUE_OPTIONAL,
                'Supervisor ID# of the user'
            )
            ->addOption(
                'language',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Language of the user for locale'
            )
            ->addOption(
                'phone',
                'p',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Office phone number of the user'
            )
            ->addOption(
                'mobile',
                'm',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Office mobile number of the user'
            )
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                "Groups to import on user's profile"
            )
            ->addOption(
                'disabled',
                'dis',
                InputOption::VALUE_OPTIONAL,
                'To disable (or enable) user'
            )
            ->addOption(
                'premise',
                'prem',
                InputOption::VALUE_OPTIONAL,
                'To update premise of user'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleRepository = $this->em->getRepository(People::class);
        $businessUnitRepository = $this->em->getRepository(BusinessUnit::class);
        $positionRepository = $this->em->getRepository(Position::class);
        $departmentRepository = $this->em->getRepository(Department::class);
        $groupRepository = $this->em->getRepository(Group::class);
        $premiseRepository = $this->em->getRepository(Premise::class);
        $peopleId = $input->getArgument('id');
        $options = $input->getOptions();

        $people = $peopleRepository->find($peopleId);
        if (!$people instanceof People) {
            $output->writeln('<error>Please provide a valid people ID</error>');

            return 1;
        }

        try {
            if (null !== $options['lastname']) {
                $people->setLastname($options['lastname']);
            }

            if (null !== $options['firstname']) {
                $people->setFirstname($options['firstname']);
            }

            if (null !== $options['email']) {
                $people->setEmail($options['email']);
                $people->setUsername($options['email']);
            }

            if (null !== $options['erpLogin']) {
                $people->setErpLogin($options['erpLogin']);
            }

            if (null !== $options['erpIdentifier']) {
                $people->setErpIdentifier((string) $options['erpIdentifier']);
            }

            if (null !== $options['jobTitle']) {
                $people->setJobTitle($options['jobTitle']);
            }

            if (null !== $options['businessUnit']) {
                $people->setBusinessUnit($businessUnitRepository->find($options['businessUnit']));
            }

            if (null !== $options['position']) {
                $people->setPosition($positionRepository->find($options['position']));
            }

            if (null !== $options['department']) {
                $people->setDepartment($departmentRepository->find($options['department']));
            }

            if (null !== $options['supervisor']) {
                $people->setSupervisor($peopleRepository->find($options['supervisor']));
            }

            if (null !== $options['language']) {
                $people->setLocale($options['language']);
            }

            if (null !== $options['disabled']) {
                $people->setDisabled((bool) $options['disabled']);
            }

            if (null !== $options['premise']) {
                $premise = $premiseRepository->findOneBy(['name' => $options['premise']]);
                if (null === $premise) {
                    $output->writeln('<error>Premise not found</error>');
                } else {
                    $people->setPremise($premise);
                }
            }

            if (!empty($options['phone'])) {
                foreach ($options['phone'] as $phone) {
                    $people->addPhone((new Phone())
                        ->setType('phone')
                        ->setNumber($phone)
                    );
                }
            }

            if (!empty($options['mobile'])) {
                foreach ($options['mobile'] as $phone) {
                    $people->addPhone((new Phone())
                        ->setType('mobile')
                        ->setNumber($phone)
                    );
                }
            }
            $this->em->persist($people);
            $this->em->flush();
        } catch (\Exception $exception) {
            $output->writeln(\sprintf('Could not edit user. Reason : %s', $exception->getMessage()));

            return 1;
        }

        if (!empty($options['group'])) {
            $businessUnit = $people->getBusinessUnit();
            $peopleGroups = [];

            foreach ($people->getAcls() as $acl) {
                $peopleGroups[] = $acl->getGroup()->getName();
            }

            foreach (array_diff($options['group'], $peopleGroups) as $group) {
                $acl = (new Acl())
                    ->setGroup($groupRepository->findOneBy(['name' => $group]))
                    ->setUser($people);

                if (null !== $businessUnit) {
                    $acl->setLocation($businessUnit->getLocation());
                }

                try {
                    $this->em->persist($acl);
                    $this->em->flush();
                } catch (\Exception $exception) {
                    $output->writeln(\sprintf('Could not insert ACL for user #%d. Reason : %s', $people->getId(), $exception->getMessage()));

                    return 1;
                }
            }
        }

        $output->writeln(\sprintf('User #%d correctly edited', $people->getId()));

        return 0;
    }
}
