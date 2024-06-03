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
use OCA\QLCV\Service\WorkService;

class WorkController extends Controller
{
    private $notificationHelper;

    private $workService;

    public function __construct(
        $AppName,
        IRequest $request,
        NotificationManager $notificationManager,
        WorkService $workService
    ) {
        parent::__construct($AppName, $request);
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
        $this->workService = $workService;
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
        $result = $this->workService->createWorkAndTasks(
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
    public function getWorks($project_id, $user_id, $assigned_to)
    {
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
        $data = $this->workService->getWorkById($work_id);
        return new JSONResponse(["work" => $data]);
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
        $status
    ) {
        $result = $this->workService->updateWork(
            $work_id,
            $work_name,
            $description,
            $start_date,
            $end_date,
            $label,
            $assigned_to,
            $status
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
        $result = $this->workService->deleteWork($work_id);
        return new JSONResponse($result);
    }
}
