<?php

declare(strict_types=1);

namespace App\AI\Tool\Search;

use App\AI\Dto\SearchOutput;
use App\AI\Service\Search\TechnicianOnCall\TechnicianOnCallSearcher;
use App\AI\Tool\ToolInterface;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class SearchTechnicianOnCallTool implements ToolInterface
{
    private const string NAME = 'search_toc';
    private const string DESCRIPTION = 'Search for Technician On-Call (TOC) entries matching a query. a TOC is a ticket opened to track and solve a problem on an equipment record (ER), a machine.';

    public function __construct(private TechnicianOnCallSearcher $searcher)
    {
    }

    public function __invoke(string $query): SearchOutput
    {
        return $this->searcher->search($query);
    }
}
