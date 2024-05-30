<?php
declare(strict_types=1);

namespace OCA\KMAQLCV\Service;

use OCP\IDBConnection;
use Exception;

class TaskService {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function createTask($work_id, $content, $is_done) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->insert('kmaqlcv_task')
                  ->values([
                      'work_id' => $query->createNamedParameter($work_id),
                      'content' => $query->createNamedParameter($content),
                      'is_done' => $query->createNamedParameter($is_done)
                  ])
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function updateTask($task_id, $content, $is_done) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->update('kmaqlcv_task')
                  ->set('content', $query->createNamedParameter($content))
                  ->set('is_done', $query->createNamedParameter($is_done))
                  ->where($query->expr()->eq('task_id', $query->createNamedParameter($task_id)))
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function deleteTask($task_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->delete('kmaqlcv_task')
                  ->where($query->expr()->eq('task_id', $query->createNamedParameter($task_id)))
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function getTasks($work_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select('*')
                  ->from('kmaqlcv_task')
                  ->where($query->expr()->eq('work_id', $query->createNamedParameter($work_id)));

            $result = $query->execute();
            $data = $result->fetchAll();

            return $data;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }
}