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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Page\External;

use Sylius\Behat\Page\PageInterface;

interface PaymentPageInterface extends PageInterface
{
    public function capture(): void;

    /**
     * @param array $postData
     */
    public function notify(array $postData): void;
}
