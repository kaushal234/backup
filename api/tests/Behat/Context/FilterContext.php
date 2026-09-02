<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Exception\OperationNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\ION\Client\Request\ComparisonExpression;
use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ExpectationException;

class FilterContext implements Context
{
    use AssertionTrait;
    use KernelAwareTrait;

    private ?string $resourceClass = null;
    private readonly IriConverterInterface $iriConverter;
    private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory;

    public function __construct(IriConverterInterface $iriConverter, ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory)
    {
        $this->iriConverter = $iriConverter;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    /**
     * @Given the class :class is exposed on the API
     */
    public function classIsExposed(string $class)
    {
        try {
            $metadata = $this->resourceMetadataFactory->create($class);
            /** @var ApiResource $apiResource */
            $apiResource = $metadata->getIterator()->current();
        } catch (\Exception $e) {
            throw new ExpectationException(\sprintf('No exposed resource found for class %s', $class), $this->getDriver());
        }

        if ([] === (array) $apiResource->getOperations()) {
            throw new ExpectationException(\sprintf('No Route found for class %s', $class), $this->getDriver());
        }
        $this->resourceClass = $class;
    }

    /**
     * @Then the filter :property should be available and its type should be :type
     */
    public function theFilterShouldBeAvailableAndItsTypeShouldBe(string $property, string $type)
    {
        if (null === $this->resourceClass) {
            throw new ExpectationException('Exposition of resource class should be tested before calling this step', $this->getDriver());
        }

        $metadata = $this->resourceMetadataFactory->create($this->resourceClass);
        try {
            $operation = $metadata->getOperation(forceCollection: true);
        } catch (OperationNotFoundException $exception) {
            $operation = $metadata->getOperation();
        }

        foreach ($operation->getFilters() as $filter) {
            foreach ($this->getClientContainer()->get($filter)->getDescription($this->resourceClass) as $key => $value) {
                if ($key === $property && $value['type'] === $type) {
                    return true;
                }
            }
        }

        throw new ExpectationException(\sprintf('The filter %s does not exist on the class %s, or its type is not equal to %s', $property, $this->resourceClass, $type), $this->getDriver());
    }

    /**
     * @Then the query parameter :property should be available
     */
    public function theQueryParameterShouldBeAvailable(string $property)
    {
        if (null === $this->resourceClass) {
            throw new ExpectationException('Exposition of resource class should be tested before calling this step', $this->getDriver());
        }

        $metadata = $this->resourceMetadataFactory->create($this->resourceClass);
        try {
            $operation = $metadata->getOperation(forceCollection: true);
        } catch (OperationNotFoundException $exception) {
            $operation = $metadata->getOperation();
        }

        foreach ($operation->getParameters() as $parameter) {
            if ($parameter->getKey() === $property) {
                return true;
            }
        }
        throw new ExpectationException(\sprintf('The query parameter %s does not exist on the class %s', $property, $this->resourceClass), $this->getDriver());
    }

    /**
     * @Then the ION filter :property should be available and its type should be :type
     */
    public function theIONFilterShouldBeAvailableAndItsTypeShouldBe(string $property, string $type)
    {
        foreach (ComparisonExpression::ION_COMPARISON_OPERATOR_LIST as $operator) {
            $paths = [
                $property,
                \sprintf('%s[%s]', $property, $operator),
                \sprintf('%s[%s][]', $property, $operator),
            ];
            foreach ($paths as $path) {
                $propertyPath = \sprintf($path, $property, $operator);
                $this->theFilterShouldBeAvailableAndItsTypeShouldBe($propertyPath, $type);
            }
        }
    }
}
