<?php

declare(strict_types=1);

namespace App\AI\Tool\Search;

use App\AI\Dto\SearchOutput;
use App\AI\Service\Search\Intranet\IntranetSearcher;
use App\AI\Tool\ToolInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class SearchIntranetTool implements ToolInterface
{
    private const string NAME = 'search_intranet';
    private const string DESCRIPTION = 'Search across intranet modules matching a query.';

    public function __construct(private IntranetSearcher $searcher)
    {
    }

    public function __invoke(string $query): SearchOutput
    {
        return $this->searcher->search($query);
    }
}
