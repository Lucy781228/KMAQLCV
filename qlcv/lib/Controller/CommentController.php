<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\QLCV\Service\CommentService;


use OCP\BackgroundJob\IJobList;
class CommentController extends Controller {
    private $commentService;

    public function __construct(
        $AppName,
        IRequest $request,
        CommentService $commentService,
    ) {
        parent::__construct($AppName, $request);
        $this->commentService = $commentService;
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
        $result = $this->commentService->createComment($work_id, $user_id, $message, $reply_comment_id);
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getComments($work_id) {
        $data = $this->commentService->getComments($work_id);
        return new JSONResponse(['comments' => $data]);
    }
}