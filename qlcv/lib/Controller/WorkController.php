<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later
namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\IUserSession;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCA\QLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;
use OCA\QLCV\Service\WorkService;
use OCA\QLCV\Service\AuthorizationService;

class WorkController extends Controller
{
    private $userSession;
    private $notificationHelper;
    private $workService;
    private $authorizationService;

    public function __construct(
        $AppName,
        IRequest $request,
        IUserSession $userSession,
        NotificationManager $notificationManager,
        WorkService $workService,
        AuthorizationService $authorizationService
    ) {
        parent::__construct($AppName, $request);
        $this->userSession = $userSession;
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
        $this->workService = $workService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createWorkAndTasks(
        $project_id,
        $work_name,
        $description,
        $start_date,
        $end_date,
        $label,
        $assigned_to,
        $owner,
        $contents,
        $status
    ) {
        try {
            $this->authorizationService->isProjectOwner($project_id);
            $result = $this->workService->createWorkAndTasks(
                $project_id,
                $work_name,
                $description,
                $start_date,
                $end_date,
                $label,
                $assigned_to,
                $owner,
                $contents,
                $status
            );
            if ($status == 1) {
                $this->notificationHelper->notifyNewWork(
                    $assigned_to,
                    $result["project_name"],
                    $work_name
                );
            }
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
    public function getWorks($project_id, $user_id, $assigned_to)
    {
        $currentUser = $this->userSession->getUser();
        if (!$currentUser) {
            return new JSONResponse(["error" => "User not authenticated"], 403);
        }
        $data = $this->workService->getWorks(
            $project_id,
            $user_id,
            $assigned_to
        );
        return new JSONResponse(["works" => $data]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getWorkById($work_id)
    {
        try {
            $this->authorizationService->hasAccessWork($work_id);
            $data = $this->workService->getWorkById($work_id);
            return new JSONResponse(["work" => $data]);
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
    public function updateWork(
        $work_id,
        $work_name,
        $description,
        $start_date,
        $end_date,
        $label,
        $assigned_to,
        $status,
        $project_id
    ) {
        try {
            $this->authorizationService->isWorkOwner($work_id);
            $result = $this->workService->updateWork(
                $work_id,
                $work_name,
                $description,
                $start_date,
                $end_date,
                $label,
                $assigned_to,
                $status,
                $project_id
            );
            if ($work_name !== null) {
                $this->notificationHelper->notifyRenameWork(
                    $assigned_to,
                    $result["project_name"],
                    $result["work_name"],
                    $work_name
                );
            }

            if ($start_date !== null) {
                $this->notificationHelper->notifyChangeWorkStart(
                    $assigned_to,
                    $result["project_name"],
                    $result["work_name"]
                );
            }

            if ($end_date !== null) {
                $this->notificationHelper->notifyChangeWorkEnd(
                    $assigned_to,
                    $result["project_name"],
                    $result["work_name"]
                );
            }

            if ($label !== null) {
                $this->notificationHelper->notifyChangeWorkLabel(
                    $assigned_to,
                    $result["project_name"],
                    $result["work_name"]
                );
            }

            if ($description !== null) {
                $this->notificationHelper->notifyChangeWorkDescription(
                    $assigned_to,
                    $result["project_name"],
                    $result["work_name"]
                );
            }
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
    public function deleteWork($work_id)
    {
        try {
            $this->authorizationService->isWorkOwner($work_id);
            $result = $this->workService->deleteWork($work_id);
            return new JSONResponse($result);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }
}
