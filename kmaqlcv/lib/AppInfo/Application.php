<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\KMAQLCV\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCA\KMAQLCV\Notification\Notifier;

class Application extends App implements IBootstrap{
	public const APP_ID = 'kmaqlcv';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}


	public function register(IRegistrationContext $context): void {
        $context->registerNotifierService(Notifier::class);

    }

    public function boot(IBootContext $context): void {
    }
}