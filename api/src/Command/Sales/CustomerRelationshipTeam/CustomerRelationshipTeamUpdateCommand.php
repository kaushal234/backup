<?php

declare(strict_types=1);

namespace App\Command\Sales\CustomerRelationshipTeam;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\SubDivision;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:crt:update')]
class CustomerRelationshipTeamUpdateCommand extends Command
{
    private readonly EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this
            ->setDescription('Update CRT')
            ->addOption('salesRepresentative', 'asm', InputOption::VALUE_OPTIONAL, 'Sales representative of CRT to update')
            ->addOption('subDivision', 'sub', InputOption::VALUE_OPTIONAL, 'Subdivision of Main Representative of Customer of CRT to update')
            ->addOption('erpLocation', 'erp', InputOption::VALUE_OPTIONAL, 'ERP Location of CRT to update')
            ->addOption('id', 'id', InputOption::VALUE_OPTIONAL, 'ID CRT to update')
            ->addOption('targetSalesRepresentative', 't_sales_rep', InputOption::VALUE_OPTIONAL, 'Sales Representative target to transfer CRT to')
            ->addOption('targetPartsRepresentative', 't_parts_rep', InputOption::VALUE_OPTIONAL, 'Parts Representative target to transfer CRT to')
            ->addOption('targetServiceRepresentative', 't_service_rep', InputOption::VALUE_OPTIONAL, 'Service Representative target to transfer CRT to')
            ->addOption('targetERPLocation', 't_erp_loc', InputOption::VALUE_OPTIONAL, 'ERP Location target to transfer CRT to')
            ->addOption('targetServiceLocation', 't_service_loc', InputOption::VALUE_OPTIONAL, 'Service Location target to transfer CRT to')
            ->addOption('targetPartsLocation', 't_parts_loc', InputOption::VALUE_OPTIONAL, 'Parts Location target to transfer CRT to')
            ->addOption('isLegacyId', 'is_legacy_id', InputOption::VALUE_OPTIONAL, 'Use legacy ID to find target', false)
        ;

        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $optionFilters = [];
        foreach (['salesRepresentative' => People::class, 'subDivision' => SubDivision::class, 'erpLocation' => Location::class, 'id' => CustomerRelationshipTeam::class] as $key => $class) {
            if (null !== ($id = $input->getOption($key)) && null !== ($object = $this->em->getRepository($class)->find($id))) {
                $optionFilters[$key] = $object;
            }
        }

        if ([] === $optionFilters) {
            $output->writeln('Nothing could be found with filter options given, or no filters given');

            return 0;
        }

        $optionTargets = [];
        foreach (['targetSalesRepresentative' => People::class, 'targetPartsRepresentative' => People::class, 'targetServiceRepresentative' => People::class, 'targetERPLocation' => Location::class, 'targetServiceLocation' => Location::class, 'targetPartsLocation' => Location::class] as $key => $class) {
            if (null !== ($id = $input->getOption($key))) {
                if ($input->getOption('isLegacyId')) {
                    $object = $this->em->getRepository($class)->findOneBy(['legacyId' => $id]);
                } else {
                    $object = $this->em->getRepository($class)->find($id);
                }

                if (null !== $object) {
                    $optionTargets[$key] = $object;
                }
            }
        }

        if ([] === $optionTargets) {
            $output->writeln('Nothing could be found with target options given, or no targets given');

            return 0;
        }

        /** @var CustomerRelationshipTeamRepository $crtRepository */
        $crtRepository = $this->em->getRepository(CustomerRelationshipTeam::class);
        $crts = $crtRepository->findCustomerRelationshipTeamsToUpdate($optionFilters);

        if ([] === $crts) {
            $output->writeln('No CRT found to update');

            return 0;
        }

        /** @var CustomerRelationshipTeam $crt */
        foreach ($crts as $crt) {
            if (isset($optionTargets['targetSalesRepresentative'])) {
                $crt->setSalesRepresentative($optionTargets['targetSalesRepresentative']);
            }

            if (isset($optionTargets['targetPartsRepresentative'])) {
                $crt->setPartsRepresentative($optionTargets['targetPartsRepresentative']);
            }

            if (isset($optionTargets['targetServiceRepresentative'])) {
                $crt->setServiceRepresentative($optionTargets['targetServiceRepresentative']);
            }

            if (isset($optionTargets['targetERPLocation'])) {
                $crt->setErpLocation($optionTargets['targetERPLocation']);
            }

            if (isset($optionTargets['targetServiceLocation'])) {
                $crt->setServiceLocation($optionTargets['targetServiceLocation']);
            }

            if (isset($optionTargets['targetServiceLocation'])) {
                $crt->setServiceLocation($optionTargets['targetServiceLocation']);
            }

            if (isset($optionTargets['targetPartsLocation'])) {
                $crt->setPartsLocation($optionTargets['targetPartsLocation']);
            }

            $this->em->persist($crt);
        }

        $this->em->flush();
        $output->writeln(\sprintf('%d CRTs updated', \count($crts)));

        return 0;
    }
}
