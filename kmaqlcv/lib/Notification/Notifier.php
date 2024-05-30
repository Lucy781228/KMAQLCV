<?php
declare(strict_types=1);
namespace OCA\KMAQLCV\Notification;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
class Notifier implements INotifier
{
    /** @var IFactory */
    private $l10nFactory;
    /** @var IURLGenerator */
    private $urlGenerator;
    public function __construct(
        IFactory $l10nFactory,
        IURLGenerator $urlGenerator
    ) {
        $this->l10nFactory = $l10nFactory;
        $this->urlGenerator = $urlGenerator;
    }
    public function getID(): string
    {
        // Identifier of the notifier, only use [a-z0-9_]
        return "kmaqlcv";
    }
    public function getName(): string
    {
        // Human readable name describing the notifier
        return $this->l10nFactory->get("kmaqlcv")->t("KMA QLCV Notifications");
    }
    public function prepare(
        INotification $notification,
        string $languageCode
    ): INotification {
        if ($notification->getApp() !== "kmaqlcv") {
            throw new \InvalidArgumentException();
        }
        // Read the language from the notification
        $l = $this->l10nFactory->get("kmaqlcv", $languageCode);
        $params = $notification->getSubjectParameters();

        switch ($notification->getSubject()) {
            // Handle your notification subjects here

            case "create-work":
                $notification->setParsedSubject(
                    $l->t("Tác vụ %s đã được giao tới bạn.", [$params[0]])
                );
                break;

            case "rename-project":
                $notification->setParsedSubject(
                    $l->t("Dự án %s được đổi tên thành %s.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "change-project-start":
                $notification->setParsedSubject(
                    $l->t("Dự án %s đã cập nhật ngày bắt đầu.", [$params[0]])
                );
                break;

            case "change-project-end":
                $notification->setParsedSubject(
                    $l->t("Dự án %s đã cập nhật ngày kết thúc.", [$params[0]])
                );
                break;

            case "new-work":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Bạn được giao công việc %s.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "rename-work":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s được đổi tên thành %s.", [
                        $params[0],
                        $params[1],
                        $params[2],
                    ])
                );
                break;

            case "change-work-start":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s đã cập nhật ngày bắt đầu.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "change-work-end":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s đã cập nhật ngày kết thúc.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "change-work-label":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s đã cập nhật nhãn.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "change-work-description":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s đã cập nhật mô tả.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "change-work-tasks":
                $notification->setParsedSubject(
                    $l->t(
                        "Dự án %s: Công việc %s đã cập nhật danh sách tác vụ.",
                        [$params[0], $params[1]]
                    )
                );
                break;

            case "30day-work":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s còn 30 ngày đến hạn.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "7day-work":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s còn 7 ngày đến hạn.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "due-work":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s đã đến hạn.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "approved-work":
                $notification->setParsedSubject(
                    $l->t(
                        "Dự án %s: Công việc %s đã được duyệt và chuyển sang trạng thái hoàn thành.",
                        [$params[0], $params[1]]
                    )
                );
                break;

            case "returned-work":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s bị trả về.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "new-commnent":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s có bình luận mới.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "new-file":
                $notification->setParsedSubject(
                    $l->t("Dự án %s: Công việc %s có file mới.", [
                        $params[0],
                        $params[1],
                    ])
                );
                break;

            case "delete-work":
                $notification->setParsedSubject(
                    $l->t(
                        "Dự án %s: Công việc %s đã bị xóa. Hãy liên hệ chủ sở hữu của dự án.",
                        [$params[0], $params[1]]
                    )
                );
                break;
            default:
                // Unknown subject => throw
                throw new \InvalidArgumentException();
        }

        // Set the link and icon if applicable
        // $notification->setLink($this->urlGenerator->linkToRouteAbsolute('your.route.name'));
        // $notification->setIcon(
        //     $this->urlGenerator->imagePath("qlcv", "app.svg")
        // );
        return $notification;
    }
}
