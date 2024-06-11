<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\IUserSession;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\QLCV\Service\ProjectService;
use OCA\QLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;
use OCA\QLCV\Service\AuthorizationService;

class ProjectController extends Controller
{
    private $userSession;
    private $projectService;
    private $notificationHelper;
    private $authorizationService;

    public function __construct(
        $AppName,
        IRequest $request,
        IUserSession $userSession,
        ProjectService $projectService,
        NotificationManager $notificationManager,
        AuthorizationService $authorizationService
    ) {
        parent::__construct($AppName, $request);
        $this->userSession = $userSession;
        $this->projectService = $projectService;
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
        $this->authorizationService = $authorizationService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createProject(
        $project_id,
        $project_name,
        $description,
        $status
    ) {
        $currentUser = $this->userSession->getUser();
        if (!$currentUser) {
            return new JSONResponse(["error" => "User not authenticated"], 403);
        }
        $result = $this->projectService->createProject(
            $project_id,
            $project_name,
            $currentUser->getUID(),
            $description,
            $status
        );
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getProjects()
    {
        $currentUser = $this->userSession->getUser();
        if (!$currentUser) {
            return new JSONResponse(["error" => "User not authenticated"], 403);
        }
        $data = $this->projectService->getProjects($currentUser->getUID());
        return new JSONResponse(["projects" => $data]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getAProject($project_id)
    {
        try {
            $this->authorizationService->isProjectOwner($project_id);
            $data = $this->projectService->getAProject($project_id);
            return new JSONResponse(["project" => $data]);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function updateProject(
        $project_id,
        $project_name,
        $description,
        $status
    ) {
        try {
            $this->authorizationService->isProjectOwner($project_id);
            $result = $this->projectService->updateProject(
                $project_id,
                $project_name,
                $this->userSession->getUser()->getUID(),
                $description,
                $status
            );
            // $this->notificationHelper->notifyRenameProject('user1', 'TEST', $project_name);
            return new JSONResponse($result);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function deleteProject($project_id)
    {
        try {
            $this->authorizationService->isProjectOwner($project_id);
            $result = $this->projectService->deleteProject($project_id);
            return new JSONResponse($result);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }
}
