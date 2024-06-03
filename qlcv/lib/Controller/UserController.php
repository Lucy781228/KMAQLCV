<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later
namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\IDBConnection;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCP\IUserSession;
use OCP\IGroupManager;

class UserController extends Controller
{
    private $db;

    protected $userSession;

    protected $groupManager;

    public function __construct(
        $AppName,
        IRequest $request,
        IDBConnection $db,
        IUserSession $userSession,
        IGroupManager $groupManager
    ) {
        parent::__construct($AppName, $request, $userSession, $groupManager);
        $this->db = $db;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getUsers()
    {
        $query = $this->db->getQueryBuilder();
        $query->select("qlcb_uid", "full_name")
            ->from('qlcb_user');

        $result = $query->execute();
        $data = $result->fetchAll();
        return ['users' => $data];
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getFullName($qlcb_uid)
    {
        $query = $this->db->getQueryBuilder();

        $query
            ->select("full_name")
            ->from("qlcb_user")
            ->where(
                $query
                    ->expr()
                    ->eq("qlcb_uid", $query->createNamedParameter($qlcb_uid))
            );

        $result = $query->execute();
        $data = $result->fetch();

        return ["full_name" => $data];
    }
}