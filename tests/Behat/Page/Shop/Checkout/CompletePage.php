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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Page\Shop\Checkout;

use Sylius\Behat\Page\Shop\Checkout\CompletePage as BaseCompletePage;

final class CompletePage extends BaseCompletePage implements CompletePageInterface
{
    /**
     * {@inheritdoc}
     */
    public function specifyDirectDebit(string $consumerName, string $iban): void
    {
        $this->getDocument()->fillField('Consumer name', $consumerName);
        $this->getDocument()->fillField('IBAN', $iban);
    }
}
