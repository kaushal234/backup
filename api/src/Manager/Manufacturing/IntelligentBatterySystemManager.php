<?php

declare(strict_types=1);

namespace App\Manager\Manufacturing;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Notifier\Tasks\LegacyTaskNotifier;
use App\Repository\Directory\PeopleRepository;
use LegacyBundle\Manager\TaskManager;
use LegacyBundle\Model\Task;

class IntelligentBatterySystemManager
{
    private readonly TaskManager $taskManager;
    private readonly LegacyTaskNotifier $taskNotifier;
    private readonly PeopleRepository $peopleRepository;

    public function __construct(TaskManager $taskManager, LegacyTaskNotifier $taskNotifier, PeopleRepository $peopleRepository)
    {
        $this->taskManager = $taskManager;
        $this->taskNotifier = $taskNotifier;
        $this->peopleRepository = $peopleRepository;
    }

    public function createTasks(People $asm, Location $sso, Location $factory, string $module, int $parentId)
    {
        $asmTask = (new Task())
            ->setLocation($sso)
            ->setModule($module)
            ->setParentId($parentId)
            ->setAssignee($asm)
            ->setAssignor($asm->getSupervisor())
            ->setDescription('You have selected iBS battery for this unit, please collect info from customer regarding charging infrastructure: Charger(s) brand, Model, serial number, Local Distributor info')
        ;
        $this->taskManager->insert($asmTask);
        $this->taskNotifier->sendEmail($asmTask);

        $psms = $this->peopleRepository->findGroupMembers('ROLE_PSM', $factory);
        if (false !== ($psm = current($psms))) {
            $psmTask = (new Task())
                ->setLocation($factory)
                ->setModule($module)
                ->setParentId($parentId)
                ->setAssignee($psm)
                ->setAssignor($asm)
                ->setDescription('Base on charging infrastructure\'s info provided by ASM, please confirm full compatibility iBS. Get support from your local Engineering team if required')
            ;
            $this->taskManager->insert($psmTask);
            $this->taskNotifier->sendEmail($psmTask);
        }
    }
}
