<?php

declare(strict_types=1);

namespace App\SageParts\Builder;

class Parameters
{
    private string $sageCID;
    private string $sageMType;
    private XmlBuilder $builder;

    public function __construct(XmlBuilder $builder, string $sageCID, string $sageMType)
    {
        $this->sageCID = $sageCID;
        $this->sageMType = $sageMType;
        $this->builder = $builder;
    }

    public function getParameters(string $identifier): array
    {
        return [
            'CID' => $this->sageCID,
            'MTYPE' => $this->sageMType,
            'cXML' => $this->builder->build($identifier),
        ];
    }
}
