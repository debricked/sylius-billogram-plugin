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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Page\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{

    /**
     * @param string $username
     */
    public function setApiUsername(string $username): void;

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void;

    /**
     * @param string $apiUrl
     */
    public function setApiUrl(string $apiUrl): void;

    /**
     * @param string $message
     * @param bool $strict
     *
     * @return bool
     */
    public function containsErrorWithMessage(string $message, bool $strict = true): bool;
}
