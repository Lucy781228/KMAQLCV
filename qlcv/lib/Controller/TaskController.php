<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later
namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCA\QLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;
use OCA\QLCV\Service\TaskService;
use OCA\QLCV\Service\AuthorizationService;

class TaskController extends Controller
{
    private $notificationHelper;
    private $taskService;
    private $authorizationService;

    public function __construct(
        $AppName,
        IRequest $request,
        NotificationManager $notificationManager,
        TaskService $taskService,
        AuthorizationService $authorizationService,
    ) {
        parent::__construct($AppName, $request);
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
        $this->taskService = $taskService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createTask($work_id, $content, $is_done) {
        try {
            $this->authorizationService->isWorkOwner($work_id);
            $result = $this->taskService->createTask($work_id, $content, $is_done);
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
    public function getTasks($work_id)
    {
        try {
            $this->authorizationService->hasAccessWork($work_id);
            $data = $this->taskService->getTasks($work_id);
            return new JSONResponse(["tasks" => $data]);
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
    public function updateTask($task_id, $content, $is_done) {
        try {
            $task = $this->taskService->getTaskById($task_id);
            $this->authorizationService->hasAccessWork($task["work_id"]);
            $result = $this->taskService->updateTask($task_id, $content, $is_done);
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
    public function deleteTask($task_id)
    {
        try {
            $task = $this->taskService->getTaskById($task_id);
            $this->authorizationService->isWorkOwner($task["work_id"]);
            $result = $this->taskService->deleteTask($task_id);
            return new JSONResponse($result);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }
}
