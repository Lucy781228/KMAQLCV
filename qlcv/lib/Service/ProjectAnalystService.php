<?php
declare(strict_types=1);

namespace OCA\QLCV\Service;

use OCP\IDBConnection;
use Exception;
use DateTime;

class ProjectAnalystService {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function getActiveProjects($user_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("project_id", "project_name")
                  ->from("qlcv_project")
                  ->where($query->expr()->eq("user_id", $query->createNamedParameter($user_id)))
                  ->andWhere($query->expr()->eq("status", $query->createNamedParameter(1)));
    
            $result = $query->execute();
            $projects = $result->fetchAll();
    
            return $projects;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function countWorksPerProject($project_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select($query->func()->count('*', 'work_count'))
                  ->from("qlcv_work")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)));
    
            $result = $query->execute();
            $workCount = $result->fetchColumn();
    
            return $workCount;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function doneWorksPerProject($project_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select($query->func()->count('*', 'work_count'))
                  ->from("qlcv_work")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)))
                  ->andWhere($query->expr()->eq("status", $query->createNamedParameter(3)));
    
            $result = $query->execute();
            $workCount = $result->fetchColumn();
    
            return $workCount;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }    
    
}