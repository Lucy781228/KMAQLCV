<?php
namespace OCA\QLCV\Service;

use OCP\IDBConnection;

class ProjectAnalystService
{
    private $db;

    public function __construct(IDBConnection $db)
    {
        $this->db = $db;
    }

    public function getProjectIds($startDate, $endDate, $userId)
    {
        // Define your base SQL query with placeholders
        $sql = "
            SELECT
                p.project_id,
                p.project_name,
                MIN(w.start_date) AS project_start_date,
                MAX(w.end_date) AS project_end_date
            FROM
                oc_qlcv_project p
            LEFT JOIN
                oc_qlcv_work w ON p.project_id = w.project_id
            WHERE
                p.user_id = :userId
        ";
    
        // Initialize your parameters array with the named parameter `userId`
        $params = ['userId' => $userId];
    
        // Add additional conditions and associated parameters if needed
        if ($startDate !== null) {
            $sql .= " AND w.start_date >= :startDate";
            $params['startDate'] = $startDate;
        }
    
        if ($endDate !== null) {
            $sql .= " AND w.end_date <= :endDate";
            $params['endDate'] = $endDate;
        }
    
        // Add GROUP BY and ORDER BY clauses
        $sql .= " GROUP BY p.project_id ORDER BY MIN(w.start_date) ASC";
    
        // Prepare and execute the query with the parameter array
        $stmt = $this->db->executeQuery($sql, $params);
    
        // Fetch and return results
        return $stmt->fetchAll();
    }

    public function countWorksPerProject($startDate, $endDate, $userId)
    {
        $projects = $this->getProjectIds($startDate, $endDate, $userId);
        $result = [];
    
        foreach ($projects as $project) {
            $projectResult = [
                "project_id" => $project["project_id"],
                "project_name" => $project["project_name"],
                "all_works" => 0,
                "todo_work" => 0,
                "doing_work" => 0,
                "pending_work" => 0,
                "done_work" => 0,
                "high" => 0,
                "normal" => 0,
                "low" => 0
            ];
    
            $projectResult["all_works"] = $this->countWorksByStatus($project["project_id"], null);
            $projectResult["todo_work"] = $this->countWorksByStatus($project["project_id"], 0);
            $projectResult["doing_work"] = $this->countWorksByStatus($project["project_id"], 1);
            $projectResult["pending_work"] = $this->countWorksByStatus($project["project_id"], 2);
            $projectResult["done_work"] = $this->countWorksByStatus($project["project_id"], 3);
            $projectResult["high"] = $this->countWorksByLabel($project["project_id"], "Cao");
            $projectResult["normal"] = $this->countWorksByLabel($project["project_id"], "Trung bình");
            $projectResult["low"] = $this->countWorksByLabel($project["project_id"], "Thấp");
    
            $result[] = $projectResult;
        }
    
        return $result;
    }
    
    private function countWorksByStatus($projectId, $status)
    {
        $query = $this->db->getQueryBuilder();
        $query
            ->select($query->func()->count("*", "work_count"))
            ->from("qlcv_work")
            ->where(
                $query->expr()->eq(
                    "project_id",
                    $query->createNamedParameter($projectId)
                )
            );
    
        if ($status !== null) {
            $query->andWhere(
                $query->expr()->eq(
                    "status",
                    $query->createNamedParameter($status)
                )
            );
        }
    
        $count = $query->execute()->fetch();
        return $count["work_count"];
    }

    private function countWorksByLabel($projectId, $label)
    {
        $query = $this->db->getQueryBuilder();
        $query
            ->select($query->func()->count("*", "work_count"))
            ->from("qlcv_work")
            ->where(
                $query->expr()->eq(
                    "project_id",
                    $query->createNamedParameter($projectId)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    "label",
                    $query->createNamedParameter($label)
                ));
    
        $count = $query->execute()->fetch();
        return $count["work_count"];
    }
}
