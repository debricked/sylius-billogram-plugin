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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\Debricked\SyliusBillogramPlugin\Behat\Page\Admin\PaymentMethod\CreatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingPaymentMethodContext implements Context
{
    /**
     * @var CreatePageInterface
     */
    private $createPage;

    /**
     * @param CreatePageInterface $createPage
     */
    public function __construct(CreatePageInterface $createPage)
    {
        $this->createPage = $createPage;
    }

    /**
     * @Given I want to create a new Billogram payment method
     */
    public function iWantToCreateANewBillogramPaymentMethod(): void
    {
        $this->createPage->open(['factory' => 'billogram']);
    }

    /**
     * @When I fill the username with :username and the API key with :apiKey and the API url with :apiUrl
     */
    public function iConfigureItWithTestBillogramCredentials(string $username, string $apiKey, string $apiUrl): void
    {
        $this->createPage->setApiUsername($username);
        $this->createPage->setApiKey($apiKey);
        $this->createPage->setApiUrl($apiUrl);
    }

    /**
     * @Then I should be notified that :fields fields cannot be blank
     */
    public function iShouldBeNotifiedThatCannotBeBlank(string $fields): void
    {
        $fields = explode(',', $fields);

        foreach ($fields as $field) {
            Assert::true($this->createPage->containsErrorWithMessage(sprintf(
                '%s cannot be blank.',
                trim($field)
            )));
        }
    }

    /**
     * @Then I should be notified that :message
     */
    public function iShouldBeNotifiedThat(string $message): void
    {
        Assert::true($this->createPage->containsErrorWithMessage($message));
    }

}
