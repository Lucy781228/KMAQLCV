<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later
namespace OCA\KMAQLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCA\KMAQLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;
use OCA\KMAQLCV\Service\TaskService;

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
    public function createWorkAndTasks(
        $project_id,
        $work_name,
        $description,
        $start_date,
        $end_date,
        $label,
        $assigned_to,
        $owner,
        $contents
    ) {
        $result = $this->taskService->createWorkAndTasks(
            $project_id,
            $work_name,
            $description,
            $start_date,
            $end_date,
            $label,
            $assigned_to,
            $owner,
            $contents
        );
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
    public function updateWork(
        $work_id,
        $work_name,
        $description,
        $start_date,
        $end_date,
        $label,
        $assigned_to
    ) {
        $result = $this->taskService->updateWork(
            $work_id,
            $work_name,
            $description,
            $start_date,
            $end_date,
            $label,
            $assigned_to
        );
        // $this->notificationHelper->notifyNewWork($assigned_to, $work_name, 'TEST');
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function deleteWork($work_id)
    {
        $result = $this->taskService->deleteWork($work_id);
        return new JSONResponse($result);
    }
}
