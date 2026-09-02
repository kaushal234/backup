<?php

declare(strict_types=1);

namespace App\Request\Task;

use App\Entity\MIS\Project\Project;
use App\Factory\Task\MIS\Project\ProjectPhaseTaskCreationFactory;
use App\Factory\Task\MIS\Project\ProjectPrivacyTaskCreationFactory;
use App\Request\SubRequestManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class SubRequestCreateTask
{
    public function __construct(
        private SubRequestManager $subRequestManager,
        private ProjectPhaseTaskCreationFactory $projectTaskCreationFactory,
        private ProjectPrivacyTaskCreationFactory $projectPrivacyTaskCreationFactory,
    ) {
    }

    public function insertTask(Project $project, Response $parentResponse): void
    {
        $responsePhaseTask = $this->subRequestManager->doSubRequest('update_project_phase', ['id' => $project->getActivePhase()->getId()], Request::METHOD_PUT, $this->projectTaskCreationFactory->createTask($project));
        $this->addSubRequestContext($responsePhaseTask, $parentResponse, 'phaseTask');

        if (0 === $project->getActivePhase()->number) {
            $responsePrivacyTask = $this->subRequestManager->doSubRequest('update_project_phase', ['id' => $project->getActivePhase()->getId()], Request::METHOD_PUT, $this->projectPrivacyTaskCreationFactory->createTask($project));
            $this->addSubRequestContext($responsePrivacyTask, $parentResponse, 'privacyTask');
        }
    }

    private function addSubRequestContext($response, $parentResponse, $name): void
    {
        if (300 <= $response->getStatusCode()) {
            $parentResponse->setStatusCode(Response::HTTP_PARTIAL_CONTENT);
            $content = json_decode($parentResponse->getContent(), true);

            $content['@sub_resources'][$name] = json_decode($response->getContent(), true);
            $parentResponse->setContent(json_encode($content));
        }
    }
}
