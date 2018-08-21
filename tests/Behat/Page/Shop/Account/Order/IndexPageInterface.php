<?php
/**
 * This file is part of the SyliusBillogramPlugin.
 *
 * Copyright (c) debricked AB
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Page\Shop\Account\Order;

use Sylius\Behat\Page\Shop\Account\Order\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function cancelSubscription(): void;
}
