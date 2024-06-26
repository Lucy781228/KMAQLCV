<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\QLCV\Service\CommentService;
use OCA\QLCV\Service\AuthorizationService;

class CommentController extends Controller {
    private $commentService;
    private $authorizationService;

    public function __construct(
        $AppName,
        IRequest $request,
        CommentService $commentService,
        AuthorizationService $authorizationService,
    ) {
        parent::__construct($AppName, $request);
        $this->commentService = $commentService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createComment(
        $work_id,
        $user_id,
        $message,
        $reply_comment_id
    ) {
        try {
            $this->authorizationService->hasAccessWork($work_id);
            $result = $this->commentService->createComment($work_id, $user_id, $message, $reply_comment_id);
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
    public function getComments($work_id) {
        try {
            $this->authorizationService->hasAccessWork($work_id);
            $data = $this->commentService->getComments($work_id);
            return new JSONResponse(['comments' => $data]);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }
}