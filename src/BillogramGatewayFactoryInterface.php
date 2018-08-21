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

interface BillogramGatewayFactoryInterface
{

    public const CURRENCIES_AVAILABLE = ['SEK'];

    public const BILLOGRAM_STATE_UNATTESTED = 'Unattested';

    public const BILLOGRAM_STATE_SENDING = 'Sending';

    public const BILLOGRAM_STATE_UNPAID = 'Unpaid';

    public const BILLOGRAM_STATE_DELETED = 'Deleted';

}
