<?php

declare(strict_types=1);

namespace App\Manager\Sales;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\Notifier\Tasks\LegacyTaskNotifier;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class DemoManager
{
    private readonly TaskManager $taskManager;
    private readonly RouterInterface $router;
    private readonly LegacyTaskNotifier $notifier;

    public function __construct(RouterInterface $router, LegacyTaskNotifier $notifier, TaskManager $taskManager)
    {
        $this->taskManager = $taskManager;
        $this->router = $router;
        $this->notifier = $notifier;
    }

    public function createTaskAndNotifyAssigneeWhenApproved(Demo $demo, People $assignee)
    {
        $route = $this->generateDemoUrl($demo);
        $description = \sprintf("Demo <a href='%s'>#%d</a> is now approved. Please go on details page of the Demo and assign ER.", $route, $demo->getId());

        $this->createAndSendTask($demo, $description, $assignee);
    }

    private function createAndSendTask(Demo $demo, string $description, People $assignee)
    {
        $task = (new Task())
            ->setAssignee($assignee)
            ->setAssignor($demo->getAsm())
            ->setLocation($demo->getSso())
            ->setModule('DEMO')
            ->setDescription($description)
        ;

        try {
            $this->taskManager->insert($task);
        } catch (\Exception $exception) {
            throw new \LogicException('Task not created. Reason : '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->notifier->sendEmail($task);
    }

    private function generateDemoUrl(Demo $demo)
    {
        return $route = $this->router->generate('demos', ['id' => $demo->getId()], Router::ABSOLUTE_URL);
    }
}
