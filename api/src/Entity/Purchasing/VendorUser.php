<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\User;
use App\Filter\SimpleSearchFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\EnterpriseModel\Entities\Department;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['email'], message: 'This value is already used by another Vendor User.')]
#[ORM\Entity(repositoryClass: 'App\Repository\Purchasing\VendorUserRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['vendor_user']]),
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['vendor_user:write']],
            security: 'user === object or is_granted("FEATURE_VENDOR_USER_WRITE")',
            validationContext: ['groups' => ['Default', 'User', 'password']],
        ),
    ],
    routePrefix: 'purchasing',
    normalizationContext: ['groups' => ['vendor_user:detail']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
)]
#[App\Loggable]
#[ApiFilter(BooleanFilter::class, properties: ['disabled'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id',
    'firstname' => 'partial',
    'lastname' => 'partial',
    'email' => 'partial',
    'erpIdentifier' => 'partial',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'id',
    'firstname' => 'partial',
    'lastname' => 'partial',
    'email' => 'partial',
    'erpIdentifier' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'lastLogin'])]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'firstname',
    'lastname',
    'email',
    'erpIdentifier',
    'createdAt',
    'lastLogin',
])]
class VendorUser extends User
{
    public BusinessPartnerContact $contact;

    public function getBusinessPartnerCodes(): array
    {
        return array_reduce($this->contact->getBusinessPartners()->toArray(), static function ($memo, BusinessPartner $businessPartner) {
            $memo[] = $businessPartner->code;

            return $memo;
        }, []);
    }

    public function getErpCodes(): array
    {
        return array_reduce($this->contact->getBusinessPartners()->toArray(), static function ($allErpsCode, BusinessPartner $businessPartner) {
            return [...$allErpsCode, ...array_reduce($businessPartner->getBuyFromDepartments()->toArray(), static function ($departmentErps, Department $department) {
                $departmentErps[] = $department->erp;

                return $departmentErps;
            }, [])];
        }, []);
    }
}
