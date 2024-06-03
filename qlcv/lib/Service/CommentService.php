<?php
declare(strict_types=1);

namespace OCA\QLCV\Service;

use OCP\IDBConnection;
use Exception;
use DateTime;

class CommentService {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function createComment($work_id, $user_id, $message, $reply_comment_id) {

        try {
            $query = $this->db->getQueryBuilder();
            $query->insert("qlcv_comment")
                  ->values([
                      "work_id" => $query->createNamedParameter($work_id),
                      "user_id" => $query->createNamedParameter($user_id),
                      "message" => $query->createNamedParameter($message),
                      "reply_comment_id" => $query->createNamedParameter($reply_comment_id)
                  ])
                  ->execute();

            return ["status" => "success"];
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function getComments($work_id) {
        try {
            $query = $this->db->getQueryBuilder();
            $query->select('*')
                  ->from('qlcv_comment')
                  ->where($query->expr()->eq('work_id', $query->createNamedParameter($work_id)));
            $result = $query->execute();
            $data = $result->fetchAll();
    
            return $data;
        } catch (\Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }
}