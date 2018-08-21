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

namespace Debricked\SyliusBillogramPlugin\Action\Api;

use Billogram\BillogramClient;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var BillogramClient
     */
    protected $billogramClient;

    /**
     * {@inheritdoc}
     */
    public function setApi($billogramClient): void
    {
        if ($billogramClient instanceof BillogramClient === false) {
            throw new UnsupportedApiException('Not supported.Expected an instance of ' . BillogramClient::class);
        }

        $this->billogramClient = $billogramClient;
    }
}
