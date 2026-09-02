<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\MIS;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\BaseTask;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;

final readonly class TroubleTicketFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'mis_trouble_ticket';
    }

    public function entityClass(): string
    {
        return TroubleTicket::class;
    }

    public function defaultAlias(): string
    {
        return 'tt';
    }

    public function defaultOrder(): array
    {
        return ['createdAt' => 'DESC'];
    }

    public function description(): string
    {
        return 'MIS trouble tickets (support tickets / incidents / requests raised on an application module), including status, type, indice factor, module, region, creator, assignee, MIS assignee, Jira issue, satisfaction and dates. ';
    }

    public function fields(): array
    {
        $statuses = [
            BaseTask::PENDING, TroubleTicket::PENDING_MOO, BaseTask::IN_PROGRESS,
            TroubleTicket::AWAITING_USER, TroubleTicket::AWAITING_USER_MOO,
            TroubleTicket::SOLUTION_PROPOSED, TroubleTicket::SOLUTION_PROPOSED_MOO,
            TroubleTicket::CLOSED_SOLVED, TroubleTicket::CLOSED_NOT_AN_ISSUE,
            TroubleTicket::CLOSED_ALREADY_RAISED, TroubleTicket::CLOSED_NOT_APPROVED,
        ];
        $typeCategories = [Type::INCIDENT, Type::REQUEST];
        $indiceFactors = [BaseTask::IF_1, BaseTask::IF_10, BaseTask::IF_100, BaseTask::IF_1000];
        $satisfactions = [
            TroubleTicket::NOT_SATISFIED_AT_ALL, TroubleTicket::NOT_MUCH_SATISFIED,
            TroubleTicket::SATISFIED, TroubleTicket::VERY_SATISFIED,
        ];

        return [
            Filter::like('shortDescriptionLike', 'shortDescription', desc: 'Partial, case-insensitive match on the ticket short description.'),
            Filter::in('statuses', 'status', enum: $statuses, desc: 'Ticket statuses. Multiple = OR.'),
            Filter::in('typeCategories', 'type.type', enum: $typeCategories, desc: 'Ticket type categories (Incident or Request). Multiple = OR.'),
            Filter::in('typeDescriptions', 'type.description', desc: 'Exact ticket type descriptions / labels (the specific type name, e.g. "security_high_attention"), distinct from the Incident/Request category. Multiple = OR.'),
            Filter::in('indiceFactors', 'indiceFactor', enum: $indiceFactors, desc: 'Indice factors. Multiple = OR.'),
            Filter::in('moduleNames', 'module.name', desc: 'Exact module names.'),
            Filter::in('regionNames', 'createdBy.businessUnit.region.name', desc: 'Exact region names of the ticket creator.'),
            Filter::inInt('createdByPeopleIds', 'createdBy', desc: 'IDs of creators (People).'),
            Filter::inInt('assigneePeopleIds', 'assignee', desc: 'IDs of assignees (People).'),
            Filter::inInt('misAssigneePeopleIds', 'misAssignee', desc: 'IDs of MIS assignees (People).'),
            Filter::in('satisfactions', 'satisfaction', enum: $satisfactions, desc: 'User satisfaction values. Multiple = OR.'),
            Filter::eq('jiraIssueNumber', 'jiraIssueNumber', desc: 'Exact Jira issue number.'),
            Filter::exists('hasJiraIssue', 'jiraIssueNumber', desc: 'True = only tickets linked to a Jira issue, false = without, omit for both.'),
            Filter::bool('isAddToUserStories', 'isAddToUserStories', desc: 'True = only tickets added to user stories, false = not added, omit for both.'),
            Filter::dateRange('createdAt', afterName: 'createdAfter', beforeName: 'createdBefore', afterDesc: 'ISO-8601 date — tickets created on/after this date.', beforeDesc: 'ISO-8601 date — tickets created on/before this date.'),
            Filter::dateRange('closedAt', afterName: 'closedAfter', beforeName: 'closedBefore', afterDesc: 'ISO-8601 date — tickets closed on/after this date.', beforeDesc: 'ISO-8601 date — tickets closed on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, TroubleTicket::class);

        return [
            'id' => $entity->getId(),
            'status' => $entity->getStatus(),
            'type' => $entity->type->type,
            'typeDescription' => $entity->type->description,
            'shortDescription' => $entity->shortDescription,
            'indiceFactor' => $entity->indiceFactor,
            'module' => $entity->module?->getName(),
            'jiraIssueNumber' => $entity->jiraIssueNumber,
            'createdBy' => $this->personName($entity->createdBy),
            'assignee' => $this->personName($entity->assignee),
            'misAssignee' => $this->personName($entity->misAssignee),
            'satisfaction' => $entity->satisfaction,
            'isAddToUserStories' => $entity->isAddToUserStories,
            'createdAt' => $entity->createdAt->format(\DATE_ATOM),
            'dueDate' => $entity->dueDate?->format(\DATE_ATOM),
            'closedAt' => $entity->closedAt?->format(\DATE_ATOM),
            'lastCommentedAt' => $entity->lastCommentedAt?->format(\DATE_ATOM),
        ];
    }
}
