<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\QLCV\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCA\QLCV\Notification\Notifier;

class Application extends App implements IBootstrap{
	public const APP_ID = 'qlcv';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}


	public function register(IRegistrationContext $context): void {
        $context->registerNotifierService(Notifier::class);

        // $context->registerService(ProjectService::class, function($c) {
        //     return new ProjectService(
        //         $c->query(IDBConnection::class)
        //     );
        // });

        // $context->registerService(WorkService::class, function($c) {
        //     return new WorkService(
        //         $c->query(IDBConnection::class)
        //     );
        // });

        // $context->registerService(TaskService::class, function($c) {
        //     return new TaskService(
        //         $c->query(IDBConnection::class)
        //     );
        // });

        // $context->registerService(FileService::class, function($c) {
        //     return new FileService(
        //         $c->query(IDBConnection::class)
        //     );
        // });

        // $context->registerService(DataService::class, function($c) {
        //     return new DataService(
        //         $c->query(IDBConnection::class)
        //     );
        // });

        // $context->registerService(ProjectAnalystService::class, function($c) {
        //     return new ProjectAnalystService(
        //         $c->query(IDBConnection::class)
        //     );
        // });


        // $context->registerService(FileController::class, function($c) {
        //     return new FileController(
        //         $c->query('AppName'),
        //         $c->query(IRequest::class),
        //         $c->query(IUserSession::class),
        //         $c->query(IRootFolder::class)
        //     );
        // });

    }

    public function boot(IBootContext $context): void {
    }
}
