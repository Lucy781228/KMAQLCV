<?php
declare(strict_types=1);

namespace OCA\KMAQLCV\Service;

use OCP\IDBConnection;
use Exception;
use DateTime;

class ProjectService {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function createProject($project_id, $project_name, $user_id, $start_date, $end_date) {

        try {
            $query = $this->db->getQueryBuilder();
            $query->insert("kmaqlcv_project")
                  ->values([
                      "project_id" => $query->createNamedParameter($project_id),
                      "project_name" => $query->createNamedParameter($project_name),
                      "user_id" => $query->createNamedParameter($user_id),
                      "start_date" => $query->createNamedParameter($start_date),
                      "end_date" => $query->createNamedParameter($end_date),
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
    
            $orCondition = $query->expr()->orX(
                $query->expr()->eq("p.user_id", $query->createNamedParameter($user_id)),
                $query->expr()->eq("w.assigned_to", $query->createNamedParameter($user_id))
            );
    
            $query->selectDistinct("p.*", "w.*")
                  ->from("kmaqlcv_project", "p")
                  ->leftJoin("p", "kmaqlcv_work", "w", "p.project_id = w.project_id")
                  ->where($orCondition);
    
            $result = $query->execute();
            $data = $result->fetchAll();
    
            return $data;
        } catch (\Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function updateProject($project_id, $project_name, $user_id, $start_date, $end_date) {
        try {
            $sql = 'UPDATE `oc_kmaqlcv_project` SET `project_name` = COALESCE(?, `project_name`), 
                                                `user_id` = COALESCE(?, `user_id`), 
                                                `start_date` = COALESCE(?, `start_date`), 
                                                `end_date` = COALESCE(?, `end_date`)
                                                WHERE `project_id` = ?';
            $query = $this->db->prepare($sql);
            
            $query->execute([
                $project_name,
                $user_id,
                $start_date,
                $end_date,
                $project_id,
            ]);
    
            return ["status" => "success"];
        } catch (\Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function deleteProject($project_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->delete("kmaqlcv_project")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)))
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function setDoingProject() {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("p.project_id", "p.project_name")
                  ->from("kmaqlcv_project", "p")
                  ->where($query->expr()->eq("p.status", 0));
    
            $result = $query->execute();
            $projects = $result->fetchAll();
    
            $projectIds = array_column($projects, 'project_id');
            $query = $this->db->getQueryBuilder();
            $query->update("kmaqlcv_project")
                  ->set("status", 1)
                  ->where($query->expr()->in("project_id", $query->createNamedParameter($projectIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)));
    
            $affectedRows = $query->execute();
            return "success";
        } catch (Exception $e) {
            return "fail";
        }
    }
}