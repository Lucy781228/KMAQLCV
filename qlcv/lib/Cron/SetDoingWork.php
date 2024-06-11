<?php
namespace OCA\QLCV\Cron;

use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;
use OCA\QLCV\Service\WorkService;

class SetDoingWork extends TimedJob
{
    private $workService;
    private $fileService;
    public function __construct(
        ITimeFactory $time,
        WorkService $workService,
    ) {
        parent::__construct($time);
        $this->workService = $workService;
        $this->setInterval(60 * 10);
    }

    protected function run($arguments)
    {
        $currentTime = new \DateTime();
        $startOfDay = new \DateTime("today 00:00:00");
        $endOfFirst15Minutes = (clone $startOfDay)->modify("+15 minutes");

        // if (
        //     $currentTime >= $startOfDay &&
        //     $currentTime <= $endOfFirst15Minutes
        // ) {
        //     try {
        //         $works = $this->workService->setDoingWork();
        //     } catch (\Exception $e) {
        //     }
        // }

        try {
            $works = $this->workService->setDoingWork();
        } catch (\Exception $e) {
        }
    }
}
