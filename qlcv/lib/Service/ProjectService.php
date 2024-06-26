<?php
declare(strict_types=1);

namespace OCA\QLCV\Service;

use OCP\IDBConnection;
use Exception;
use DateTime;

class ProjectService {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function createProject($project_id, $project_name, $user_id, $description, $status) {

        try {
            $query = $this->db->getQueryBuilder();
            $query->insert("qlcv_project")
                  ->values([
                      "project_id" => $query->createNamedParameter($project_id),
                      "project_name" => $query->createNamedParameter($project_name),
                      "user_id" => $query->createNamedParameter($user_id),
                      "description" => $query->createNamedParameter($description),
                      "status" => $query->createNamedParameter($status),
                  ])
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function getProjects($user_id) {
        try {
            $query = $this->db->getQueryBuilder();
    
            $workAssignedAndStatusCondition = $query->expr()->andX(
                $query->expr()->eq("w.assigned_to", $query->createNamedParameter($user_id)),
                $query->expr()->neq("w.status", $query->createNamedParameter(0))
            );
    
            $orCondition = $query->expr()->orX(
                $query->expr()->eq("p.user_id", $query->createNamedParameter($user_id)),
                $workAssignedAndStatusCondition
            );
    
            $query->selectDistinct("p.*", "w.*")
                  ->from("qlcv_project", "p")
                  ->leftJoin("p", "qlcv_work", "w", "p.project_id = w.project_id")
                  ->where($orCondition);
    
            $result = $query->execute();
            $data = $result->fetchAll();
    
            return $data;
        } catch (\Exception $e) {
            throw new \Exception("ERROR: " . $e->getMessage());
        }
    }

    public function getAProject($project_id) {
        try {
            $query = $this->db->getQueryBuilder();
    
            $query->select("p.*")
                  ->from("qlcv_project", "p")
                  ->where(
                      $query->expr()->eq("p.project_id", $query->createNamedParameter($project_id))
                  );
    
            $result = $query->execute();
            $data = $result->fetch();
    
            return $data;
        } catch (\Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function updateProject($project_id, $project_name, $user_id, $description, $status) {
        try {
            $sql = 'UPDATE `oc_qlcv_project` SET `project_name` = COALESCE(?, `project_name`), 
                                                `user_id` = COALESCE(?, `user_id`), 
                                                `description` = COALESCE(?, `description`), 
                                                `status` = COALESCE(?, `status`)
                                                WHERE `project_id` = ?';
            $query = $this->db->prepare($sql);
            
            $query->execute([
                $project_name,
                $user_id,
                $description,
                $status,
                $project_id,
            ]);
    
            return ["status" => "success"];
        } catch (\Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function setDoingProject($project_id) {
        $this->updateProject($project_id, null, null, null, 1);
    }

    public function deleteProject($project_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->delete("qlcv_project")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)))
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function updateProjectStatus($project_id)
    {
        $query = $this->db->getQueryBuilder();
        $query
            ->select($query->func()->count("*", "total_work_count"))
            ->from("qlcv_work")
            ->where(
                $query->expr()->eq(
                    "project_id",
                    $query->createNamedParameter($project_id)
                )
            );
        $totalWorkCount = $query->execute()->fetchColumn();
    
        $query = $this->db->getQueryBuilder();
        $query
            ->select($query->func()->count("*", "done_work_count"))
            ->from("qlcv_work")
            ->where(
                $query->expr()->eq(
                    "project_id",
                    $query->createNamedParameter($project_id)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    "status",
                    $query->createNamedParameter(3)
                )
            );
        $doneWorkCount = $query->execute()->fetchColumn();
    
        if ($totalWorkCount == $doneWorkCount && $totalWorkCount > 0) {
            return true;
        } else {
            return false;
        }
    }
}