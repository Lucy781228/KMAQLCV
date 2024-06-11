<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\QLCV\Service\ProjectAnalystService;

class DataController extends Controller
{
    private $projectAnalystService;

    public function __construct(
        $AppName,
        IRequest $request,
        ProjectAnalystService $projectAnalystService
    ) {
        parent::__construct($AppName, $request);
        $this->projectAnalystService = $projectAnalystService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function countWorksPerProject($userId)
    {
        $startDate = $this->request->getParam('startDate');
        $endDate = $this->request->getParam('endDate');
        $data = $this->projectAnalystService->countWorksPerProject($startDate, $endDate, $userId);
        return new JSONResponse(['data' => $data]); 
    }
}
