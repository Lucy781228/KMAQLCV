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
use OCP\Constants;
use OCA\QLCV\Service\FileService;
use OCA\QLCV\Service\AuthorizationService;

class FileController extends Controller
{
    private $userSession;
    private $rootFolder;
    private $shareManager;
    private $fileService;
    private $authorizationService;

    public function __construct(
        $AppName,
        IRequest $request,
        IUserSession $userSession,
        IRootFolder $rootFolder,
        IShareManager $shareManager,
        FileService $fileService,
        AuthorizationService $authorizationService,
    ) {
        parent::__construct($AppName, $request);
        $this->userSession = $userSession;
        $this->rootFolder = $rootFolder;
        $this->shareManager = $shareManager;
        $this->fileService = $fileService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createFile($file_id, $work_id)
    {
        try {
            $this->authorizationService->hasAccessWork($work_id);
            $user = $this->userSession->getUser();
            $currentUserId = $user->getUID();
            $result = $this->fileService->addFileRecord(
                $file_id,
                $work_id,
                $currentUserId
            );
            return new JSONResponse($result);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function shareFolder()
    {
        try {
            $owner = $this->request->getParam("owner");
            $assigned_to = $this->request->getParam("assigned_to");
            $work_id = $this->request->getParam("work_id");
            $userFolder = $this->rootFolder->getUserFolder($owner);
            $this->authorizationService->hasAccessWork($work_id);
    
            if (!$userFolder->nodeExists("QLCV")) {
                $userFolder->newFolder("QLCV");
            }
    
            $qlcvFolder = $userFolder->get("QLCV");
    
            if (!$qlcvFolder->nodeExists($work_id)) {
                $qlcvFolder->newFolder($work_id);
                $workFolder = $qlcvFolder->get($work_id);
                $share = $this->shareManager->newShare();
                $share->setSharedBy($owner);
                $share->setSharedWith($assigned_to);
                $share->setNode($workFolder);
                $share->setShareType(IShare::TYPE_USER);
                $share->setPermissions(
                    Constants::PERMISSION_READ | Constants::PERMISSION_CREATE
                );
                $this->shareManager->createShare($share);
            }
            return new JSONResponse([
                "success" => true,
            ]);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function setReadPermission($work_id, $assigned_to, $owner)
    {
        try {
            $this->authorizationService->hasAccessWork($work_id);
            $userFolder = $this->rootFolder->getUserFolder($owner);
            $qlcvFolder = $userFolder->get("QLCV");

            if ($qlcvFolder->nodeExists($work_id)) {
                $workFolder = $qlcvFolder->get($work_id);
                $shares = $this->shareManager->getSharesBy(
                    $owner,
                    IShare::TYPE_USER
                );
                foreach ($shares as $share) {
                    if (
                        $share->getNode()->getId() === $workFolder->getId() &&
                        $share->getSharedWith() === $assigned_to
                    ) {
                        $share->setPermissions(Constants::PERMISSION_READ);
                        $this->shareManager->updateShare($share);
                        return new JSONResponse([
                            "success" => true,
                        ]);
                    }
                }
            }
            return new JSONResponse(
                [
                    "success" => false,
                ],
                Http::STATUS_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function addCreatePermission($work_id, $assigned_to, $owner)
    {
        try {
            $this->authorizationService->hasAccessWork($work_id);
            $userFolder = $this->rootFolder->getUserFolder($owner);
            $qlcvFolder = $userFolder->get("QLCV");
            if ($qlcvFolder->nodeExists($work_id)) {
                $workFolder = $qlcvFolder->get($work_id);
                $shares = $this->shareManager->getSharesBy(
                    $owner,
                    IShare::TYPE_USER
                );
                foreach ($shares as $share) {
                    if (
                        $share->getNode()->getId() === $workFolder->getId() &&
                        $share->getSharedWith() === $assigned_to
                    ) {
                        $share->setPermissions(Constants::PERMISSION_READ | Constants::PERMISSION_CREATE);
                        $this->shareManager->updateShare($share);
                        return new JSONResponse([
                            "success" => true,
                        ]);
                    }
                }
            }
            return new JSONResponse(
                [
                    "success" => false,
                ],
                Http::STATUS_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function uploadFile()
    {
        try {
            $owner = $this->request->getParam("owner");
            $work_id = $this->request->getParam("work_id");
            $file = $this->request->getUploadedFile("file");
            $userFolder = $this->rootFolder->getUserFolder($owner);
            $this->authorizationService->hasAccessWork($work_id);
    
            if (!$userFolder->nodeExists("QLCV")) {
                $userFolder->newFolder("QLCV");
            }
    
            $qlcvFolder = $userFolder->get("QLCV");
    
            if (!$qlcvFolder->nodeExists($work_id)) {
                $qlcvFolder->newFolder($work_id);
            }
    
            $workFolder = $qlcvFolder->get($work_id);
            try {
                $newFile = $workFolder->newFile($file["name"]);
                $newFile->putContent(file_get_contents($file["tmp_name"]));
    
                return new JSONResponse([
                    "success" => true,
                    "fileId" => $newFile->getId(),
                ]);
            } catch (NotFoundException $e) {
                return new JSONResponse(
                    ["error" => "Could not create file or folder"],
                    Http::STATUS_INTERNAL_SERVER_ERROR
                );
            } catch (\Exception $e) {
                return new JSONResponse(
                    ["error" => $e->getMessage()],
                    Http::STATUS_INTERNAL_SERVER_ERROR
                );
            }
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function deleteFile($file_id, $owner)
    {
        try {
            $this->authorizationService->isWorkOwner($work_id);
            try {
                $qlcvFolder = $this->rootFolder->getUserFolder($owner)->get("QLCV");
                $fileNodes = $qlcvFolder->getById($file_id);
    
                $file = $fileNodes[0];
                $file->delete();
    
                $this->fileService->deleteFileRecord($file_id);
            } catch (\Exception $e) {
                return new JSONResponse(
                    ["error" => $e->getMessage()],
                    Http::STATUS_INTERNAL_SERVER_ERROR
                );
            }
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function downloadFile()
    {
        try {
            $file_id = $this->request->getParam("fileId");
            $owner = $this->request->getParam("owner");
            $work_id = $this->request->getParam("work_id");
            $this->authorizationService->hasAccessWork($work_id);
    
            try {
                $found = $this->findFileById($file_id, $owner, $work_id);
    
                $data = $found->getContent();
                $fileName = $found->getName();
                $fileMimeType = $found->getMimeType();
    
                return new DataDownloadResponse($data, $fileName, $fileMimeType);
            } catch (\Exception $e) {
                return new JSONResponse(
                    ["error" => $e->getMessage()],
                    Http::STATUS_INTERNAL_SERVER_ERROR
                );
            }
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getFiles()
    {
        try {
            $owner = $this->request->getParam("owner");
            $work_id = $this->request->getParam("work_id");
            $this->authorizationService->hasAccessWork($work_id);
            try {
                $dbFileIds = $this->fileService->getFileRecords($work_id);
                $fileList = [];
    
                foreach ($dbFileIds as $record) {
                    $found = $this->findFileById(
                        $record["file_id"],
                        $owner,
                        $work_id
                    );
                    if (!$found) {
                        $this->fileService->deleteFileRecord($record["file_id"]);
                    } else {
                        $fileList[] = [
                            "file_id" => $found->getId(),
                            "file_name" => $found->getName(),
                            "size" => $found->getSize(),
                            "mtime" => $found->getMTime(),
                            "uploaded_by" => $record["uploaded_by"],
                        ];
                    }
                }
                usort($fileList, function ($a, $b) {
                    return $a["mtime"] <=> $b["mtime"];
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
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    private function findFileById($fileId, $owner, $work_id)
    {
        $workFolder = null;
        $userFolder = $this->rootFolder->getUserFolder($owner);
        if ($userFolder->nodeExists("QLCV/$work_id")) {
            $workFolder = $userFolder->get("QLCV/$work_id");
        }
        if ($workFolder) {
            $files = $workFolder->getById((int) $fileId);
            if (!empty($files)) {
                return $files[0];
            }
        }

        return false;
    }
}
