<?php
declare(strict_types=1);
namespace OCA\QLCV\Service;

use OCP\IUserSession;
use OCA\QLCV\Service\ProjectService;
use OCA\QLCV\Service\WorkService;

class AuthorizationService
{
    private $userSession;
    private $projectService;
    private $workService;

    public function __construct(
        IUserSession $userSession,
        ProjectService $projectService,
        WorkService $workService
    ) {
        $this->userSession = $userSession;
        $this->projectService = $projectService;
        $this->workService = $workService;
    }

    public function isProjectOwner($project_id)
    {
        $currentUser = $this->userSession->getUser();
        if (!$currentUser) {
            throw new \Exception("User not authenticated", 401);
        }

        if ($project_id !== null) {
            $project = $this->projectService->getAProject($project_id);
            if (!$project) {
                throw new \Exception("Project not found", 404);
            }

            if ($project["user_id"] === $currentUser->getUID()) {
                return true;
            }
        }

        throw new \Exception("Unauthorized", 403);
    }

    public function hasAccessWork($work_id)
    {
        $currentUser = $this->userSession->getUser();
        if (!$currentUser) {
            throw new \Exception("User not authenticated", 401);
        }

        if ($work_id !== null) {
            $work = $this->workService->getWorkById($work_id);
            if (!$work) {
                throw new \Exception("Work not found", 404);
            }

            if (
                $work["assigned_to"] === $currentUser->getUID() ||
                $work["owner"] === $currentUser->getUID()
            ) {
                return true;
            }
        }

        throw new \Exception("Unauthorized", 403);
    }

    public function isWorkOwner($work_id)
    {
        $currentUser = $this->userSession->getUser();
        if (!$currentUser) {
            throw new \Exception("User not authenticated", 401);
        }

        if ($work_id !== null) {
            $work = $this->workService->getWorkById($work_id);
            if (!$work) {
                throw new \Exception("Work not found", 404);
            }

            if ($work["owner"] === $currentUser->getUID()) {
                return true;
            }
        }

        throw new \Exception("Unauthorized", 403);
    }
}
