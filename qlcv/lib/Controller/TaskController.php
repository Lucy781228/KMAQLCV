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

class TaskController extends Controller
{
    private $notificationHelper;

    private $taskService;

    public function __construct(
        $AppName,
        IRequest $request,
        NotificationManager $notificationManager,
        TaskService $taskService
    ) {
        parent::__construct($AppName, $request);
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
        $this->taskService = $taskService;
    }
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createTask($work_id, $content, $is_done) {
        $result = $this->taskService->createTask($work_id, $content, $is_done);
        // $this->notificationHelper->notifyNewWork($assigned_to, $work_name, 'TEST');
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getTasks($work_id)
    {
        $data = $this->taskService->getTasks($work_id);
        return new JSONResponse(["tasks" => $data]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function updateTask($task_id, $content, $is_done) {
        $result = $this->taskService->updateTask($task_id, $content, $is_done);
        // $this->notificationHelper->notifyNewWork($assigned_to, $work_name, 'TEST');
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function deleteTask($task_id)
    {
        $result = $this->taskService->deleteTask($task_id);
        return new JSONResponse($result);
    }
}
