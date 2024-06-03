<?php

declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\Files\NotFoundException;
use OCP\IUserSession;
use OCP\Files\IRootFolder;
use OCP\Share\IManager as IShareManager;
use OCP\Share\IShare;
use OCP\IDBConnection;
use OCP\Constants;

class FileController extends Controller
{
    private $userSession;
    private $rootFolder;
    private $shareManager;
    private $db;

    public function __construct(
        $AppName,
        IRequest $request,
        IUserSession $userSession,
        IRootFolder $rootFolder,
        IShareManager $shareManager,
        IDBConnection $db
    ) {
        parent::__construct($AppName, $request);
        $this->userSession = $userSession;
        $this->rootFolder = $rootFolder;
        $this->shareManager = $shareManager;
        $this->db = $db;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function uploadFile()
    {
        // Retrieve additional parameters from the request
        $share_with = $this->request->getParam("share_with");
        $work_id = $this->request->getParam("work_id");

        $file = $this->request->getUploadedFile("file");

        if ($file["error"] !== UPLOAD_ERR_OK) {
            return new JSONResponse(
                ["error" => "Upload error"],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }

        $user = $this->userSession->getUser();
        $userId = $user->getUID();
        $userFolder = $this->rootFolder->getUserFolder($userId);

        if (!$userFolder->nodeExists("QLCV")) {
            $userFolder->newFolder("QLCV");
        }

        $qlcvFolder = $userFolder->get("QLCV");

        try {
            $newFile = $qlcvFolder->newFile($file["name"]);
            $newFile->putContent(file_get_contents($file["tmp_name"]));

            // Share the file with the specified user if provided
            if ($share_with) {
                $share = $this->shareManager->newShare();
                $share->setSharedBy($user->getUID());
                $share->setSharedWith($share_with);
                $share->setNode($newFile);
                $share->setShareType(IShare::TYPE_USER);
                $share->setPermissions(Constants::PERMISSION_READ); // Grant read permissions
                $this->shareManager->createShare($share);
            }

            // Insert file record into the database
            $query = $this->db->getQueryBuilder();
            $query->insert("qlcv_file")->values([
                "file_id" => $query->createNamedParameter($newFile->getId()),
                "work_id" => $query->createNamedParameter($work_id)
            ]);
            $query->execute();
        } catch (NotFoundException $e) {
            return new JSONResponse(
                ["error" => "Could not create file"],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }

        return new JSONResponse([
            "success" => true,
            "message" => "File uploaded",
        ]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function deleteFile($file_id)
    {
        $currentUser = $this->userSession->getUser();

        try {
            // Access the QLCV folder within the user's directory
            $qlcvFolder = $this->rootFolder
                ->getUserFolder($currentUser->getUID())
                ->get("QLCV");
            $fileNodes = $qlcvFolder->getById($file_id);

            $file = $fileNodes[0];
            $file->delete();

            // Delete the file record from the database
            $query = $this->db->getQueryBuilder();
            $query
                ->delete("qlcv_file")
                ->where(
                    $query
                        ->expr()
                        ->eq("file_id", $query->createNamedParameter($file_id))
                );
            $query->execute();
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function downloadFile($file_id, $share_by)
    {
        $currentUser = $this->userSession->getUser();

        try {
            $found = $this->findFileById(
                $file_id,
                $currentUser->getUID(),
                $share_by
            );

            $data = $found->getContent();
            $fileName = $found->getName();
            $fileMimeType = $found->getMimeType();

            // Return a DataDownloadResponse to download the file
            return new DataDownloadResponse($data, $fileName, $fileMimeType);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getFiles($work_id, $share_by)
    {
        $currentUser = $this->userSession->getUser();

        try {
            // Lấy tất cả file_id từ cơ sở dữ liệu dựa trên work_id
            $query = $this->db->getQueryBuilder();
            $query
                ->select("file_id")
                ->from("qlcv_file")
                ->where(
                    $query
                        ->expr()
                        ->eq("work_id", $query->createNamedParameter($work_id))
                );
            $result = $query->execute();
            $dbFileIds = [];
            while ($row = $result->fetch()) {
                $dbFileIds[] = $row["file_id"];
            }
            $result->closeCursor();

            // Tạo mảng để lưu trữ thông tin file
            $fileList = [];

            // Kiểm tra và xóa bản ghi nếu file_id không tồn tại trong thư mục của người dùng hiện tại và share_by
            foreach ($dbFileIds as $dbFileId) {
                $found = $this->findFileById(
                    $dbFileId,
                    $currentUser->getUID(),
                    $share_by
                );
                if (!$found) {
                    // Xóa bản ghi từ cơ sở dữ liệu
                    $deleteQuery = $this->db->getQueryBuilder();
                    $deleteQuery
                        ->delete("qlcv_file")
                        ->where(
                            $deleteQuery
                                ->expr()
                                ->eq(
                                    "file_id",
                                    $deleteQuery->createNamedParameter(
                                        $dbFileId
                                    )
                                )
                        )
                        ->execute();
                } else {
                    // Thêm thông tin file vào danh sách
                    $fileList[] = [
                        "file_id" => $found->getId(),
                        "file_name" => $found->getName(),
                        "size" => $found->getSize(),
                        "mtime" => $found->getMTime(),
                        "owner" => $found->getOwner()->getUID(),
                    ];
                }
            }
            usort($fileList, function ($a, $b) {
                return $a['mtime'] <=> $b['mtime'];
            });

            return new JSONResponse([
                "success" => true,
                "files" => $fileList,
            ]);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                Http::STATUS_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function findFileById($fileId, $currentUserId, $share_by)
    {
        // Tìm file trong thư mục QLCV của người dùng hiện tại
        $userFolder = $this->rootFolder->getUserFolder($currentUserId);
        if (!$userFolder->nodeExists("QLCV")) {
            $userFolder->newFolder("QLCV");
        }

        $qlcvFolder = $userFolder->get("QLCV");
        $files = $qlcvFolder->getById($fileId);
        if (!empty($files)) {
            // Nếu file tồn tại, trả về file
            return $files[0];
        }

        // Tìm file trong số các file được chia sẻ với người dùng
        $shares = $this->shareManager->getSharesBy(
            $share_by,
            IShare::TYPE_USER,
            null,
            false,
            -1
        );
        foreach ($shares as $share) {
            if ($share->getNodeId() === (int) $fileId) {
                return $share->getNode();
            }
        }

        // Nếu không tìm thấy file, trả về false
        return false;
    }
}