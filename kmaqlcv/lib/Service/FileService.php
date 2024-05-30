<?php

declare(strict_types=1);

namespace OCA\KMAQLCV\Service;

use OCP\IDBConnection;

class FileService {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function addFileRecord($fileId, $workId) {
        $query = $this->db->getQueryBuilder();
        $query->insert('kmaqlcv_file')
              ->values([
                  'file_id' => $query->createNamedParameter($fileId),
                  'work_id' => $query->createNamedParameter($workId),
              ]);
        $query->execute();
    }

    public function deleteFileRecord($fileId) {
        $query = $this->db->getQueryBuilder();
        $query->delete('kmaqlcv_file')
              ->where($query->expr()->eq('file_id', $query->createNamedParameter($fileId)));
        $query->execute();
    }

    public function getFileRecords($workId) {
        $query = $this->db->getQueryBuilder();
        $query->select('file_id')
              ->from('kmaqlcv_file')
              ->where($query->expr()->eq('work_id', $query->createNamedParameter($workId)));
        $result = $query->execute();
        $files = $result->fetchAll();
        return $files;
    }
}