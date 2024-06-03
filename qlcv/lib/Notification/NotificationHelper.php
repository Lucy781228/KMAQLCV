<?php
declare(strict_types=1);
namespace OCA\QLCV\Notification;

use OCP\Notification\IManager;

class NotificationHelper {
    private $notificationManager;

    public function __construct(IManager $notificationManager) {
        $this->notificationManager = $notificationManager;
    }

    private function createAndSendNotification($recipientUserId, $subject, $subjectParams) {
        $notification = $this->notificationManager->createNotification();
        $notification->setApp('qlcv')
            ->setUser($recipientUserId)
            ->setDateTime(new \DateTime())
            ->setObject('type', 'id')
            ->setSubject($subject, $subjectParams);

        $this->notificationManager->notify($notification);
    }

    public function notifyRenameProject($recipientUserId, $oldName, $newName) {
        $this->createAndSendNotification($recipientUserId, 'rename-project', [$oldName, $newName]);
    }

    public function notifyChangeProjectStart($recipientUserId, $projectName) {
        $this->createAndSendNotification($recipientUserId, 'change-project-start', [$projectName]);
    }

    public function notifyChangeProjectEnd($recipientUserId, $projectName) {
        $this->createAndSendNotification($recipientUserId, 'change-project-end', [$projectName]);
    }

    public function notifyNewWork($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'new-work', [$projectName, $workName]);
    }

    public function notifyRenameWork($recipientUserId, $projectName, $oldName, $newName) {
        $this->createAndSendNotification($recipientUserId, 'rename-work', [$projectName, $oldName, $newName]);
    }

    public function notifyChangeWorkStart($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'change-wor-start', [$projectName, $workName]);
    }

    public function notifyChangeWorkEnd($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'change-work-end', [$projectName, $workName]);
    }

    public function notifyChangeWorkLabel($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'change-work-label', [$projectName, $workName]);
    }


    public function notifyChangeWorkDescription($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'change-work-description', [$projectName, $workName]);
    }

    public function notifyChangeWorkTasks($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'change-work-tasks', [$projectName, $workName]);
    }

    public function notify30dayWork($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, '30day-work', [$projectName, $workName]);
    }

    public function notify7dayWork($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, '7day-work', [$projectName, $workName]);
    }

    public function notifyDueWork($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'due-work', [$projectName, $workName]);
    }

    public function notifyApprovedWork($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'approved-work', [$projectName, $workName]);
    }

    public function notifyReturnedWork($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'returned-work', [$projectName, $workName]);
    }

    public function notifyNewComment($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'new-comment', [$projectName, $workName]);
    }

    public function notifyNewFile($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'new-file', [$projectName, $workName]);
    }

    public function notifyDeleteWork($recipientUserId, $projectName, $workName) {
        $this->createAndSendNotification($recipientUserId, 'delete-work', [$projectName, $workName]);
    }
}