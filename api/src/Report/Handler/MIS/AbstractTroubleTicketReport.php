<?php

declare(strict_types=1);

namespace App\Report\Handler\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Division;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Premise;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Entity\MIS\SupportTeam;
use App\Entity\MIS\TroubleTicket\Application;
use App\Entity\MIS\TroubleTicket\Type;
use App\Entity\Module\Module;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractTroubleTicketReport
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function applyFilters(QueryBuilder $queryBuilder, array $options): void
    {
        if (\array_key_exists('after', $options) && !empty($options['after'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->gte('t.createdAt', ':after'))
                ->setParameter('after', (new \DateTime($options['after']))->format('Y-m-d'))
            ;
        } else {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->gt('t.createdAt', ':one_year_ago'))
                ->setParameter('one_year_ago', (new \DateTime('1 year ago'))->format('Y-m-d'))
            ;
        }

        if (\array_key_exists('before', $options) && !empty($options['before'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('t.createdAt', ':before'))
                ->setParameter('before', (new \DateTime($options['before']))->format('Y-m-d'))
            ;
        }

        if (\array_key_exists('location', $options) && !empty($options['location'])) {
            $locations = [];

            foreach ($options['location'] as $locationIri) {
                $location = $this->iriConverter->getResourceFromIri($locationIri);
                if ($location instanceof Location) {
                    $locations[] = $location;
                }
            }

            if (!empty($locations)) {
                $queryBuilder
                    ->leftJoin(People::class, 'p', Join::WITH, 't.createdBy = p')
                    ->leftJoin(BusinessUnit::class, 'b', Join::WITH, 'p.businessUnit = b')
                    ->leftJoin(Location::class, 'l', Join::WITH, 'b.location = l')
                    ->andWhere($queryBuilder->expr()->in('l', ':locations'))
                    ->setParameter('locations', $locations)
                ;
            }
        }

        if (\array_key_exists('region', $options) && !empty($options['region'])) {
            $regions = [];

            foreach ($options['region'] as $regionIri) {
                $region = $this->iriConverter->getResourceFromIri($regionIri);
                if ($region instanceof Region) {
                    $regions[] = $region;
                }
            }

            if (!empty($regions)) {
                $queryBuilder
                    ->leftJoin(People::class, 'p', Join::WITH, 't.createdBy = p')
                    ->leftJoin(BusinessUnit::class, 'b', Join::WITH, 'p.businessUnit = b')
                    ->leftJoin(Region::class, 'r', Join::WITH, 'b.region = r')
                    ->andWhere($queryBuilder->expr()->in('r', ':regions'))
                    ->setParameter('regions', $regions)
                ;
            }
        }

        if (\array_key_exists('subDivision', $options) && !empty($options['subDivision'])) {
            $subdivisions = [];

            foreach ($options['subDivision'] as $subdivisionIri) {
                $subdivision = $this->iriConverter->getResourceFromIri($subdivisionIri);
                if ($subdivision instanceof SubDivision) {
                    $subdivisions[] = $subdivision;
                }
            }

            if (!empty($subdivisions)) {
                $queryBuilder
                    ->leftJoin(People::class, 'p', Join::WITH, 't.createdBy = p')
                    ->leftJoin(BusinessUnit::class, 'b', Join::WITH, 'p.businessUnit = b')
                    ->leftJoin(Region::class, 'r', Join::WITH, 'b.region = r')
                    ->leftJoin(SubDivision::class, 'sd', Join::WITH, 'r.subDivision = sd')
                    ->andWhere($queryBuilder->expr()->in('sd', ':subdivisions'))
                    ->setParameter('subdivisions', $subdivisions)
                ;
            }
        }

        if (\array_key_exists('division', $options) && !empty($options['division'])) {
            $divisions = [];

            foreach ($options['division'] as $divisionIri) {
                $division = $this->iriConverter->getResourceFromIri($divisionIri);
                if ($division instanceof Division) {
                    $divisions[] = $division;
                }
            }

            if (!empty($divisions)) {
                $queryBuilder
                    ->leftJoin(People::class, 'p', Join::WITH, 't.createdBy = p')
                    ->leftJoin(BusinessUnit::class, 'b', Join::WITH, 'p.businessUnit = b')
                    ->leftJoin(Region::class, 'r', Join::WITH, 'b.region = r')
                    ->leftJoin(SubDivision::class, 'sd', Join::WITH, 'r.subDivision = sd')
                    ->leftJoin(Division::class, 'd', Join::WITH, 'sd.division = d')
                    ->andWhere($queryBuilder->expr()->in('d', ':divisions'))
                    ->setParameter('divisions', $divisions)
                ;
            }
        }

        if (\array_key_exists('indiceFactor', $options) && !empty($options['indiceFactor'])) {
            $indiceFactors = [];
            foreach ($options['indiceFactor'] as $indiceFactor) {
                $indiceFactors[] = $indiceFactor;
            }

            if (!empty($indiceFactors)) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->in('t.indiceFactor', ':indices'))
                    ->setParameter('indices', $indiceFactors)
                ;
            }
        }

        if (\array_key_exists('module', $options) && !empty($options['module'])) {
            $modules = [];

            foreach ($options['module'] as $moduleiri) {
                $module = $this->iriConverter->getResourceFromIri($moduleiri);
                if ($module instanceof Module && Module::DISABLED !== $module->status) {
                    $modules[] = $module;
                }
            }

            if (!empty($modules)) {
                $queryBuilder
                    ->leftJoin(Module::class, 'm', Join::WITH, 't.module = m')
                    ->andWhere($queryBuilder->expr()->in('m', ':modules'))
                    ->setParameter('modules', $modules)
                ;
            }
        }

        if (\array_key_exists('type', $options) && !empty($options['type'])) {
            $type = $options['type'];
            $queryBuilder
                ->leftJoin(Type::class, 'ty', Join::WITH, 't.type = ty')
                ->andWhere('ty.type = :type')
                ->setParameter('type', $type)
            ;
        }

        if (\array_key_exists('application', $options) && !empty($options['application'])) {
            $applications = [];

            foreach ($options['application'] as $applicationIri) {
                $application = $this->iriConverter->getResourceFromIri($applicationIri);
                if ($application instanceof Application) {
                    $applications[] = $application;
                }
            }

            if (!empty($applications)) {
                $queryBuilder
                    ->leftJoin(Module::class, 'mo', Join::WITH, 't.module = mo')
                    ->leftJoin(Application::class, 'ap', Join::WITH, 'mo.application = ap')
                    ->andWhere($queryBuilder->expr()->in('ap', ':applications'))
                    ->setParameter('applications', $applications)
                ;
            }
        }

        if (\array_key_exists('supportTeam', $options) && !empty($options['supportTeam'])) {
            $supportTeams = [];

            foreach ($options['supportTeam'] as $supportTeamIri) {
                $supportTeam = $this->iriConverter->getResourceFromIri($supportTeamIri);
                if ($supportTeam instanceof SupportTeam) {
                    $supportTeams[] = $supportTeam;
                }
            }

            if (!empty($supportTeams)) {
                $queryBuilder
                    ->leftJoin(People::class, 'p', Join::WITH, 't.createdBy = p')
                    ->leftJoin(Premise::class, 'premise', Join::WITH, 'p.premise = premise')
                    ->leftJoin(SupportTeam::class, 'st', Join::WITH, 'premise.supportTeam = st')
                    ->andWhere($queryBuilder->expr()->in('st', ':supportTeams'))
                    ->setParameter('supportTeams', $supportTeams)
                ;
            }
        }
    }
}
