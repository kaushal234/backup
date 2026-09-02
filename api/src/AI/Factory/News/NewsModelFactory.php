<?php

declare(strict_types=1);

namespace App\AI\Factory\News;

use App\AI\Dto\Directory\AddressModel;
use App\AI\Dto\Directory\DepartmentModel;
use App\AI\Dto\Directory\DivisionModel;
use App\AI\Dto\Directory\PremiseModel;
use App\AI\Dto\News\CategoryModel;
use App\AI\Dto\News\NewsModel;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\AddressWithCountry;
use App\Entity\Directory\Department;
use App\Entity\Directory\Division;
use App\Entity\Directory\Premise;
use App\Entity\News\News;
use App\Entity\News\NewsCategory;

final readonly class NewsModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return News::class === $class;
    }

    /**
     * @param News $entity
     */
    public function create(object $entity): NewsModel
    {
        return new NewsModel(
            title: $entity->getTitle(),
            content: $entity->getContent(),
            contentShort: $entity->getContentShort(),
            bannerText: $entity->getBannerText(),
            majorIncident: $entity->isMajorIncident(),
            date: $entity->getDate(),
            banner: $entity->isBanner(),
            category: null === $entity->getCategory() ? null : $this->createCategory($entity->getCategory()),
            people: null === $entity->getPeople() ? null : $this->peopleModelFactory->create($entity->getPeople()),
            department: null === $entity->department ? null : $this->createDepartment($entity->department),
            division: null === $entity->division ? null : $this->createDivision($entity->division),
            premise: null === $entity->premise ? null : $this->createPremise($entity->premise),
        );
    }

    private function createCategory(NewsCategory $category): CategoryModel
    {
        return new CategoryModel(name: $category->getName());
    }

    private function createDepartment(Department $department): DepartmentModel
    {
        return new DepartmentModel(
            name: $department->getName(),
            sso: $department->isSso(),
            factory: $department->isFactory(),
        );
    }

    private function createDivision(Division $division): DivisionModel
    {
        return new DivisionModel(name: $division->name);
    }

    private function createPremise(Premise $premise): PremiseModel
    {
        return new PremiseModel(
            name: $premise->name,
            description: $premise->description,
            latitude: $premise->latitude,
            longitude: $premise->longitude,
            address: $this->createAddress($premise->address),
        );
    }

    private function createAddress(AddressWithCountry $address): AddressModel
    {
        return new AddressModel(
            street1: $address->getStreet1(),
            street2: $address->getStreet2(),
            postalCode: $address->getPostalCode(),
            city: $address->getCity(),
            town: $address->getTown(),
            state: $address->getState(),
            country: $address->getCountry(),
        );
    }
}
