<?php
namespace OCA\QLCV\Cron;

use OCA\QLCV\Service\WorkService;
use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Notification\IManager as NotificationManager;
use OCA\QLCV\Notification\NotificationHelper;

class CheckWorkDeadline extends TimedJob {

    private $workService;
    private $notificationHelper;

    private NotificationManager $notificationManager;

    public function __construct(ITimeFactory $time, WorkService $workService, NotificationManager $notificationManager) {
        parent::__construct($time);
        $this->workService = $workService;
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
        $this->setInterval(60 * 10);
    }

    protected function run($arguments) {

        // $currentTime = new \DateTime();
        // $startOfDay = new \DateTime('today 00:00:00');
        // $endOfFirst15Minutes = (clone $startOfDay)->modify('+15 minutes');

        // if ($currentTime >= $startOfDay && $currentTime <= $endOfFirst15Minutes) {
        //     try {
        //         $works = $this->workService->getAllWorks();

        //         foreach ($works as $work) {
        //             $daysToDeadline = $this->workService->calculateDaysToDeadline($work['work_id']);
        
        //             if ($daysToDeadline === 0) {
        //                 $this->notificationHelper->notifyDueWork($work['assigned_to'], $work['project_name'], $work['work_name']);
        //             } elseif ($daysToDeadline === 7) {
        //                 $this->notificationHelper->notify7dayWork($work['assigned_to'], $work['project_name'], $work['work_name']);
        //             } elseif ($daysToDeadline === 30) {
        //                 $this->notificationHelper->notify30dayWork($work['assigned_to'], $work['project_name'], $work['work_name']);
        //             }
        //         }
        //     } catch (\Exception $e) {
        //     }
        // }

        try {
            $works = $this->workService->getAllWorks();

            foreach ($works as $work) {
                $daysToDeadline = $this->workService->calculateDaysToDeadline($work['work_id']);
    
                if ($daysToDeadline === 0) {
                    $this->notificationHelper->notifyDueWork($work['assigned_to'], $work['project_name'], $work['work_name']);
                } elseif ($daysToDeadline === 7) {
                    $this->notificationHelper->notify7dayWork($work['assigned_to'], $work['project_name'], $work['work_name']);
                } elseif ($daysToDeadline === 30) {
                    $this->notificationHelper->notify30dayWork($work['assigned_to'], $work['project_name'], $work['work_name']);
                }
            }
        } catch (\Exception $e) {
        }

    }
}