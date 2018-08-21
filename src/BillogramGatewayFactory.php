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

namespace Debricked\SyliusBillogramPlugin;

use Billogram\BillogramClient;
use Billogram\HttpClientConfigurator;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class BillogramGatewayFactory extends GatewayFactory
{

    public const FACTORY_NAME = 'billogram';

    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'payum.factory_name' => self::FACTORY_NAME,
                'payum.factory_title' => 'Billogram',
            ]
        );

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'apiKey' => null,
                'apiUrl' => null,
                'username' => null,
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
                'apiKey',
                'apiUrl',
                'username',
            ];

            $config['payum.http_client'] = BillogramClient::configure(
                (new HttpClientConfigurator())->setAuth($config['username'], $config['apiKey'])->setEndpoint(
                    $config['apiUrl']
                )
            );

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var BillogramClient $billogramClient */
                $billogramClient = $config['payum.http_client'];

                return $billogramClient;
            };
        }
    }
}
