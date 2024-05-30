<?php
declare(strict_types=1);

namespace OCA\KMAQLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\KMAQLCV\Service\ProjectService;
use OCA\KMAQLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;


class ProjectController extends Controller {
    private $projectService;
    private $notificationHelper;

    public function __construct(
        $AppName,
        IRequest $request,
        ProjectService $projectService,
        NotificationManager $notificationManager,


    ) {
        parent::__construct($AppName, $request);
        $this->projectService = $projectService;
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createProject(
        $project_id,
        $project_name,
        $user_id,
        $start_date,
        $end_date
    ) {
        $result = $this->projectService->createProject($project_id, $project_name, $user_id, $start_date, $end_date);
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getProjects($user_id) {
        $data = $this->projectService->getProjects($user_id);
        return new JSONResponse(['projects' => $data]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function updateProject(
        $project_id,
        $project_name,
        $user_id,
        $start_date,
        $end_date,
        $status
    ) {
        $result = $this->projectService->updateProject($project_id, $project_name, $user_id, $start_date, $end_date, $status);
        // $this->notificationHelper->notifyRenameProject('user1', 'TEST', $project_name);
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function deleteProject($project_id) {
        $result = $this->projectService->deleteProject($project_id);
        return new JSONResponse($result);
    }
}