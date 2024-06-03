<?php
namespace OCA\QLCV\Cron;

use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;
use OCA\QLCV\Service\ProjectService;
use OCP\ILogger;

class SetDoingProject extends TimedJob {

    private $projectService;

    public function __construct(ITimeFactory $time, ProjectService $projectService,  ILogger $logger) {
        parent::__construct($time);
        $this->projectService = $projectService;
        $this->logger = $logger;
        $this->setInterval(60 * 10);
    }

    protected function run($arguments) {
        $currentTime = new \DateTime();
        $startOfDay = new \DateTime('today 00:00:00');
        $endOfFirst15Minutes = (clone $startOfDay)->modify('+15 minutes');

        if ($currentTime >= $startOfDay && $currentTime <= $endOfFirst15Minutes) {
            try {
                $this->projectService->setDoingProject();
        $this->logger->debug('SetDoingProject');
            } catch (\Exception $e) {
        $this->logger->debug('SetDoingProject Error');
            }
        }

    }
    }