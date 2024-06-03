<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\QLCV\Service\ProjectAnalystService;

class DataController extends Controller
{
    private $projectAnalystService;

    public function __construct(
        $AppName,
        IRequest $request,
        ProjectAnalystService $projectAnalystService
    ) {
        parent::__construct($AppName, $request);
        $this->projectAnalystService = $projectAnalystService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function countWorksPerProject($user_id)
    {
        $activeProjects = $this->projectAnalystService->getActiveProjects($user_id);
        $data = [];
        foreach ($activeProjects as $project) {
            $workCount = $this->projectAnalystService->countWorksPerProject(
                $project["project_id"]
            );

            $data[] = [
                "project_name" => $project["project_name"],
                "work_count" => $workCount
            ];

        }
        return new JSONResponse(['data' => $data]); 
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function doneWorksPerProject($user_id)
    {
        $activeProjects = $this->projectAnalystService->getActiveProjects($user_id);
        $data = [];
        foreach ($activeProjects as $project) {
            $workCount = $this->projectAnalystService->doneWorksPerProject(
                $project["project_id"]
            );

            $data[] = [
                "project_name" => $project["project_name"],
                "work_count" => $workCount
            ];

        }
        return new JSONResponse(['data' => $data]); 
    }
}
