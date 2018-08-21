<?php
/**
 * This file is part of the SyliusBillogramPlugin.
 *
 * Copyright (c) debricked AB
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Debricked\SyliusBillogramPlugin\spec;

use Billogram\BillogramClient;
use Billogram\HttpClientConfigurator;

/**
 * TODO: Add description
 *
 * @author Oscar Reimer <oscar.reimer@eit.lth.se>
 */
class BillogramClientHelper
{

    public static function createBillogramClient(): BillogramClient
    {
        $username = \getenv('billogram_api_username');
        if ($username === false) {
            throw new \InvalidArgumentException(
                'Environment variable "billogram_api_username" for setting API username is not set'
            );
        }
        $authKey = \getenv('billogram_api_password');
        if ($authKey === false) {
            throw new \InvalidArgumentException(
                'Environment variable "billogram_api_password" for setting API auth key is not set'
            );
        }

        return BillogramClient::configure(
            (new HttpClientConfigurator())->setAuth($username, $authKey)->setEndpoint(
                'https://sandbox.billogram.com/api/v2'
            )
        );
    }

}