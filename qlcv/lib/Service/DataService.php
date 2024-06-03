<?php
declare(strict_types=1);

namespace OCA\QLCV\Service;

use OCP\IDBConnection;
use Exception;
use DateTime;

class DataService {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }
    public function countWorksPerProject($user_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("p.project_id", "p.project_name", $query->func()->count("w.work_id", "work_count"))
                  ->from("qlcv_project", "p")
                  ->leftJoin("p", "qlcv_work", "w", "p.project_id = w.project_id")
                  ->where($query->expr()->eq("p.user_id", $query->createNamedParameter($user_id)))
                  ->groupBy("p.project_id");
    
            $result = $query->execute();
            $projects = $result->fetchAll();
    
            return $projects;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function calculateProjectProgress($user_id) {
        try {
            $query = $this->db->getQueryBuilder();
            // Đếm tổng số công việc
            $query->select("p.project_id", $query->func()->count("w1.work_id", "total_works"))
                  ->from("qlcv_project", "p")
                  ->leftJoin("p", "qlcv_work", "w1", "p.project_id = w1.project_id")
                  ->where($query->expr()->eq("p.user_id", $query->createNamedParameter($user_id)))
                  ->groupBy("p.project_id");
    
            $totalWorksResult = $query->execute();
            $totalWorksData = $totalWorksResult->fetchAll();
    
            // Tạo một truy vấn mới để tránh sử dụng lại alias 'w'
            $query = $this->db->getQueryBuilder();
            // Đếm số công việc đã hoàn thành (status = 3)
            $query->select("p.project_id", $query->func()->count("w2.work_id", "completed_works"))
                  ->from("qlcv_project", "p")
                  ->leftJoin("p", "qlcv_work", "w2", "p.project_id = w2.project_id")
                  ->where($query->expr()->eq("p.user_id", $query->createNamedParameter($user_id)))
                  ->andWhere($query->expr()->eq("w2.status", $query->createNamedParameter(3)))
                  ->groupBy("p.project_id");
    
            $completedWorksResult = $query->execute();
            $completedWorksData = $completedWorksResult->fetchAll();
    
            // Tính toán tiến độ
            $progressData = [];
            foreach ($totalWorksData as $totalWork) {
                $completedWork = current(array_filter($completedWorksData, function ($cw) use ($totalWork) {
                    return $cw['project_id'] === $totalWork['project_id'];
                }));
                $progress = ($totalWork['total_works'] > 0 && $completedWork) ? ($completedWork['completed_works'] / $totalWork['total_works']) * 100 : 0;
                $progressData[] = [
                    'project_id' => $totalWork['project_id'],
                    'project_name' => $totalWork['project_name'],
                    'progress' => round($progress, 2)
                ];
            }
    
            return $progressData;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }
}