<?php

declare(strict_types=1);

namespace OCA\QLCV\Service;

use OCP\IDBConnection;

class FileService {
    private $db;
    private $rootFolder;
    private $shareManager;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function addFileRecord($fileId, $workId, $uploaded_by) {
        $query = $this->db->getQueryBuilder();
        $query->insert('qlcv_file')
              ->values([
                  'file_id' => $query->createNamedParameter($fileId),
                  'work_id' => $query->createNamedParameter($workId),
                  'uploaded_by' => $query->createNamedParameter($uploaded_by)
              ]);
        $query->execute();
    }

    public function deleteFileRecord($fileId) {
        $query = $this->db->getQueryBuilder();
        $query->delete('qlcv_file')
              ->where($query->expr()->eq('file_id', $query->createNamedParameter($fileId)));
        $query->execute();
    }

    public function getFileRecords($workId) {
        $query = $this->db->getQueryBuilder();
        $query->select(['file_id', 'uploaded_by'])
              ->from('qlcv_file')
              ->where($query->expr()->eq('work_id', $query->createNamedParameter($workId)));
        $result = $query->execute();
        $files = $result->fetchAll();
        return $files; 
    }
}