<?php

declare(strict_types=1);

namespace App\AI\Tool\Search;

use App\AI\Dto\SearchOutput;
use App\AI\Service\Search\DMS\DMSSearcher;
use App\AI\Tool\ToolInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class SearchDMSTool implements ToolInterface
{
    private const string NAME = 'search_dms';
    private const string DESCRIPTION = 'Search for DMS documents matching a query. DMS stands for Document Management System, where internal processes and procedures are documented.';

    public function __construct(private DMSSearcher $searcher)
    {
    }

    public function __invoke(string $query): SearchOutput
    {
        return $this->searcher->search($query);
    }
}
