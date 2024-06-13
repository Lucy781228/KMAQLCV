<?php
declare(strict_types=1);

namespace OCA\QLCV\Service;

use OCP\IDBConnection;
use Exception;
use DateTime;
use OCA\QLCV\Service\TaskService;
use OCA\QLCV\Service\ProjectService;
use OCA\QLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;

class WorkService
{
    private $db;

    private $taskService;
    private $projectService;
    private $notificationHelper;

    public function __construct(
        IDBConnection $db,
        TaskService $taskService,
        ProjectService $projectService,
        NotificationManager $notificationManager
    ) {
        $this->db = $db;
        $this->taskService = $taskService;
        $this->projectService = $projectService;
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
    }

    public function getAllWorks()
    {
        try {
            $query = $this->db->getQueryBuilder();
            $query
                ->select("w.*", "p.project_name")
                ->from("qlcv_work", "w")
                ->join("w", "qlcv_project", "p", "w.project_id = p.project_id")
                ->where("w.status = 1");
    
            $result = $query->execute();
            $data = $result->fetchAll();
    
            return $data;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

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
        $this->db->beginTransaction();
        try {
            $query = $this->db->getQueryBuilder();
            $query
                ->insert("qlcv_work")
                ->values([
                    "project_id" => $query->createNamedParameter($project_id),
                    "work_name" => $query->createNamedParameter($work_name),
                    "description" => $query->createNamedParameter($description),
                    "start_date" => $query->createNamedParameter($start_date),
                    "end_date" => $query->createNamedParameter($end_date),
                    "label" => $query->createNamedParameter($label),
                    "assigned_to" => $query->createNamedParameter($assigned_to),
                    "owner" => $query->createNamedParameter($owner),
                    "status" => $query->createNamedParameter($status),
                ])
                ->execute();

            $work_id = $this->db->lastInsertId("oc_qlcv_work");

            foreach ($contents as $content) {
                $this->taskService->createTask($work_id, $content, 0);
            }
            $this->db->commit();
            $query = $this->db->getQueryBuilder();
            $query
                ->select("project_name")
                ->from("qlcv_project")
                ->where(
                    $query
                        ->expr()
                        ->eq(
                            "project_id",
                            $query->createNamedParameter($project_id)
                        )
                );

            $result = $query->execute();
            $project = $result->fetch();

            return [
                "status" => "success",
                "project_name" => $project["project_name"],
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function getWorks($project_id, $user_id, $assigned_to)
    {
        try {
            $query = $this->db->getQueryBuilder();
            $query
                ->select("*")
                ->from("qlcv_work")
                ->where(
                    $query
                        ->expr()
                        ->eq(
                            "project_id",
                            $query->createNamedParameter($project_id)
                        )
                );

            if ($user_id !== $assigned_to) {
                $query
                    ->andWhere(
                        $query
                            ->expr()
                            ->eq(
                                "assigned_to",
                                $query->createNamedParameter($assigned_to)
                            )
                    )
                    ->andWhere(
                        $query
                            ->expr()
                            ->gt("status", $query->createNamedParameter(0))
                    );
            }
            $query->orderBy("start_date", "ASC");

            $result = $query->execute();
            $data = $result->fetchAll();

            return $data;
        } catch (\Exception $e) {
            throw new \Exception("ERROR: " . $e->getMessage());
        }
    }

    public function getWorkById($work_id)
    {
        try {
            $query = $this->db->getQueryBuilder();
            $query
                ->select("*")
                ->from("qlcv_work")
                ->where(
                    $query
                        ->expr()
                        ->eq("work_id", $query->createNamedParameter($work_id))
                );

            $result = $query->execute();
            $data = $result->fetch();

            return $data;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function updateWork(
        $work_id,
        $work_name,
        $description,
        $start_date,
        $end_date,
        $label,
        $assigned_to,
        $status,
        $is_returned,
        $actual_end_date,
        $project_id
    ) {
        try {
            $work = $this->getWorkById($work_id);
            $sql = 'UPDATE `oc_qlcv_work` SET `work_name` = COALESCE(?, `work_name`), 
                                                `description` = COALESCE(?, `description`), 
                                                `start_date` = COALESCE(?, `start_date`), 
                                                `end_date` = COALESCE(?, `end_date`), 
                                                `label` = COALESCE(?, `label`),
                                                `assigned_to` = COALESCE(?, `assigned_to`),
                                                `status` = COALESCE(?, `status`),
                                                `is_returned` = COALESCE(?, `is_returned`),
                                                `actual_end_date` = COALESCE(?, `actual_end_date`)
                                                WHERE `work_id` = ?';
            $query = $this->db->prepare($sql);

            $query->execute([
                $work_name,
                $description,
                $start_date,
                $end_date,
                $label,
                $assigned_to,
                $status,
                $is_returned,
                $actual_end_date,
                $work_id,
            ]);

            $result = $this->projectService->updateProjectStatus($project_id);

            $query = $this->db->getQueryBuilder();
            $query
                ->select("project_name")
                ->from("qlcv_project")
                ->where(
                    $query
                        ->expr()
                        ->eq(
                            "project_id",
                            $query->createNamedParameter($work["project_id"])
                        )
                );
            $project = $query->execute()->fetch();

            return [
                "status" => "success",
                "isProjectDone" => $result,
                "work_name" => $work["work_name"],
                "project_name" => $project["project_name"],
            ];
        } catch (\Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function deleteWork($work_id)
    {
        try {
            $query = $this->db->getQueryBuilder();
            $query
                ->delete("qlcv_work")
                ->where(
                    $query
                        ->expr()
                        ->eq("work_id", $query->createNamedParameter($work_id))
                )
                ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function calculateDaysToDeadline($workId)
    {
        $today = new DateTime();
        $today->setTime(0, 0);
        $todayTimestamp = $today->getTimestamp();

        try {
            $query = $this->db->getQueryBuilder();
            $query
                ->select("end_date")
                ->from("qlcv_work")
                ->where(
                    $query
                        ->expr()
                        ->eq("work_id", $query->createNamedParameter($workId))
                );

            $result = $query->execute();
            $work = $result->fetch();
            return $work["end_date"] - $todayTimestamp;
        } catch (Exception $e) {
            throw new Exception(
                "Lỗi khi tính toán ngày đến hạn của công việc: " .
                    $e->getMessage()
            );
        }
    }

    public function setDoingWork()
    {
        $today = new DateTime();
        $today->setTime(0, 0);
        $todayTimestamp = $today->getTimestamp();

        try {
            $query = $this->db->getQueryBuilder();
            $query
                ->select("work_id", "project_id")
                ->from("qlcv_work")
                ->where(
                    $query
                        ->expr()
                        ->eq(
                            "start_date",
                            $query->createNamedParameter($todayTimestamp)
                        )
                )
                ->andWhere(
                    $query
                        ->expr()
                        ->eq("status", $query->createNamedParameter(0))
                );

            $result = $query->execute();
            $works = $result->fetchAll();

            if (count($works) > 0) {
                foreach ($works as $work) {
                    $this->updateWork(
                        $work["work_id"],
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        1,
                        $work["project_id"]
                    );

                    $this->projectService->setDoingProject($work["project_id"]);
                    $query = $this->db->getQueryBuilder();
                    $query
                        ->select("project_name")
                        ->from("qlcv_project")
                        ->where(
                            $query
                                ->expr()
                                ->eq(
                                    "project_id",
                                    $query->createNamedParameter(
                                        $work["project_id"]
                                    )
                                )
                        );

                    $result = $query->execute();
                    $project = $result->fetch();
                    $this->notificationHelper->notifyNewWork(
                        $work["assigned_to"],
                        $project["project_name"],
                        $work["work_name"]
                    );
                }
            }

            return $works;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function getUpcomingWorks($assigned_to)
    {
        try {
            $today = new DateTime();
            $today->setTime(0, 0);
            $todayTimestamp = $today->getTimestamp();

            $query = $this->db->getQueryBuilder();
            $query
                ->select("*")
                ->from("qlcv_work")
                ->where(
                    $query
                        ->expr()
                        ->eq(
                            "assigned_to",
                            $query->createNamedParameter($assigned_to)
                        )
                )
                ->andWhere(
                    $query
                        ->expr()
                        ->eq("status", $query->createNamedParameter(1))
                )
                ->andWhere(
                    $query
                    ->expr()
                    ->lte(
                        "end_date",
                        $query->createNamedParameter(
                            $todayTimestamp + 30 * 24 * 60 * 60
                        )
                    )
                );

            $result = $query->execute();

            return $result->fetchAll();
        } catch (\Exception $e) {
            throw new \Exception("ERROR: " . $e->getMessage());
        }
    }
}
