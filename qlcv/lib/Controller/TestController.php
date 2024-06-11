<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCA\QLCV\Service\TestService;

class TestController extends Controller
{
    private $testService;

    public function __construct(
        $AppName,
        IRequest $request,
        TestService $testService
    ) {
        parent::__construct($AppName, $request);
        $this->testService = $testService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function predictCompletionTime(int $numTasks, float $complexity)
    {
        try {
            $predictedDuration = $this->testService->predictCompletionTime($numTasks, $complexity);
            return new JSONResponse(['predicted_duration' => $predictedDuration]);
        } catch (Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}