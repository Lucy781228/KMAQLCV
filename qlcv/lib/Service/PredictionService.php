<?php
declare(strict_types=1);

namespace OCA\QLCV\Service;

use OCP\IDBConnection;
use Exception;

class PredictionService {
    private $db;
    private $projectAnalystService;
    private $beta0;
    private $beta1;

    public function __construct(IDBConnection $db, ProjectAnalystService $projectAnalystService) {
        $this->db = $db;
        $this->projectAnalystService = $projectAnalystService;
    }

    private function calculateRegressionCoefficients($project_id) {
        try {
            $data = $this->getHistoricalData($project_id);

            // Tính toán các giá trị trung bình
            $n = count($data);
            $sumX = 0;
            $sumY = 0;
            $sumXY = 0;
            $sumX2 = 0;

            foreach ($data as $row) {
                $x = $row['task_count'];
                $y = $row['duration'];
                $sumX += $x;
                $sumY += $y;
                $sumXY += $x * $y;
                $sumX2 += $x * $x;
            }

            $meanX = $sumX / $n;
            $meanY = $sumY / $n;

            // Tính toán các hệ số hồi quy
            $this->beta1 = ($sumXY - $n * $meanX * $meanY) / ($sumX2 - $n * $meanX * $meanX);
            $this->beta0 = $meanY - $this->beta1 * $meanX;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function predictCompletionTime($project_id) {
        try {
            $this->calculateRegressionCoefficients($project_id);

            $query = $this->db->getQueryBuilder();
            $query->select("work_id")
                  ->from("qlcv_work")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)))
                  ->andWhere($query->expr()->eq("status", $query->createNamedParameter(1)));

            $result = $query->execute();
            $workIds = $result->fetchAll();

            $predictions = [];
            foreach ($workIds as $row) {
                $work_id = $row['work_id'];
                $taskCount = $this->countTaskPerWork($work_id, 0);
                $predictedDuration = $this->beta0 + $this->beta1 * $taskCount;
                $predictions[] = [
                    'work_id' => $work_id,
                    'predicted_duration' => $predictedDuration
                ];
            }

            return $predictions;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    private function countTaskPerWork($work_id, $is_done) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select($query->func()->count('*', 'task_count'))
                  ->from("qlcv_task")
                  ->where($query->expr()->eq("work_id", $query->createNamedParameter($work_id)))
                  ->andWhere($query->expr()->eq("is_done", $query->createNamedParameter($is_done)));
    
            $result = $query->execute();
            $taskCount = $result->fetchColumn();
    
            return $taskCount;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    private function getHistoricalData($project_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select("duration", "work_id")
                  ->from("qlcv_work")
                  ->where($query->expr()->eq("project_id", $query->createNamedParameter($project_id)))
                  ->andWhere($query->expr()->eq("status", $query->createNamedParameter(3)));

            $result = $query->execute();
            $data = $result->fetchAll();

            $historicalData = [];

            foreach ($data as $row) {
                $work_id = $row['work_id'];
                $duration = $row['duration'];
                $taskCount = $this->countTaskPerWork($work_id, 1);

                $historicalData[] = [
                    'work_id' => $work_id,
                    'duration' => $duration,
                    'task_count' => $taskCount
                ];
            }

            return $historicalData;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }
}