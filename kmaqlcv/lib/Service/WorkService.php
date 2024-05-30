<?php
declare(strict_types=1);

namespace OCA\KMAQLCV\Service;

use OCP\IDBConnection;
use Exception;
use DateTime;
use OCA\KMAQLCV\Service\TaskService;

class WorkService {
    private $db;

    private $taskService;

    public function __construct(IDBConnection $db, TaskService $taskService) {
        $this->db = $db;
        $this->taskService = $taskService;
    }

    public function getAllWorks() {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("w.*", "p.project_name")
                  ->from("kmaqlcv_work", "w")
                  ->join("w", "kmaqlcv_project", "p", "w.project_id = p.project_id");
    
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
        $contents
    ) {
        $this->db->beginTransaction();
        try {
            $query = $this->db->getQueryBuilder();
            $query->insert("kmaqlcv_work")
                  ->values([
                      "project_id" => $query->createNamedParameter($project_id),
                      "work_name" => $query->createNamedParameter($work_name),
                      "description" => $query->createNamedParameter($description),
                      "start_date" => $query->createNamedParameter($start_date),
                      "end_date" => $query->createNamedParameter($end_date),
                      "label" => $query->createNamedParameter($label),
                      "assigned_to" => $query->createNamedParameter($assigned_to),
                      "owner" => $query->createNamedParameter($owner)
                  ])
                  ->execute();
            
            $work_id = $this->db->lastInsertId('kmaqlcv_work');
    
            foreach ($contents as $content) {
                $this->taskService->createTask($work_id, $content, 0);
            }    
            $this->db->commit();
            $query = $this->db->getQueryBuilder();
            $query->select("project_name")
                  ->from("kmaqlcv_project")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)));
    
            $result = $query->execute();
            $project = $result->fetch();

        return [
            "status" => "success",
            "project_name" => $project['project_name'],
        ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

    public function getWorks($project_id, $user_id, $assigned_to) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("*")
                  ->from("kmaqlcv_work")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)));

            if ($user_id !== $assigned_to) {
                $query->andWhere(
                    $query->expr()->eq("assigned_to", $query->createNamedParameter($assigned_to))
                );
            }

            $result = $query->execute();
            $data = $result->fetchAll();

            return $data;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function getWorkById($work_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("*")
                  ->from("kmaqlcv_work")
                  ->where($query->expr()->eq("work_id", $query->createNamedParameter($work_id)));

            $result = $query->execute();
            $data = $result->fetch();

            return $data;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function updateWork($work_id, $work_name, $description, $start_date, $end_date, $label, $assigned_to) {
        try {
            $sql = 'UPDATE `oc_kmaqlcv_work` SET `work_name` = COALESCE(?, `work_name`), 
                                                `description` = COALESCE(?, `description`), 
                                                `start_date` = COALESCE(?, `start_date`), 
                                                `end_date` = COALESCE(?, `end_date`), 
                                                `label` = COALESCE(?, `label`),
                                                `assigned_to` = COALESCE(?, `assigned_to`)
                                                WHERE `work_id` = ?';
            $query = $this->db->prepare($sql);
            
            $query->execute([
                $work_name,
                $description,
                $start_date,
                $end_date,
                $label,
                $assigned_to,
                $work_id
            ]);
    
            return ["status" => "success"];
        } catch (\Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function deleteWork($work_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->delete("kmaqlcv_work")
                  ->where($query->expr()->eq("work_id", $query->createNamedParameter($work_id)))
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function calculateDaysToDeadline($workId) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("end_date")
                  ->from("kmaqlcv_work")
                  ->where($query->expr()->eq("work_id", $query->createNamedParameter($workId)));

            $result = $query->execute();
            $work = $result->fetch();

            if (!$work) {
                throw new Exception("Không tìm thấy công việc với ID: " . $workId);
            }

            $endDate = new DateTime($work['end_date']);
            $today = new DateTime();
            $interval = $today->diff($endDate);

            return $interval->format('%r%a');
        } catch (Exception $e) {
            throw new Exception("Lỗi khi tính toán ngày đến hạn của công việc: " . $e->getMessage());
        }
    }
}