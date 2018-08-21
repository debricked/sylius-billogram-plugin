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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Debricked\SyliusBillogramPlugin\BillogramGatewayFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class BillogramContext implements Context
{
    /**
     * @var ShowPageInterface
     */
    private $orderDetails;

    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepository;

    /**
     * @var ExampleFactoryInterface
     */
    private $paymentMethodExampleFactory;

    /**
     * @var FactoryInterface
     */
    private $paymentMethodTranslationFactory;

    /**
     * @var ObjectManager
     */
    private $paymentMethodManager;

    /**
     * @param ShowPageInterface                $orderDetails
     * @param SharedStorageInterface           $sharedStorage
     * @param PaymentMethodRepositoryInterface $paymentMethodRepository
     * @param ExampleFactoryInterface          $paymentMethodExampleFactory
     * @param FactoryInterface                 $paymentMethodTranslationFactory
     * @param ObjectManager                    $paymentMethodManager
     */
    public function __construct(
        ShowPageInterface $orderDetails,
        SharedStorageInterface $sharedStorage,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        ExampleFactoryInterface $paymentMethodExampleFactory,
        FactoryInterface $paymentMethodTranslationFactory,
        ObjectManager $paymentMethodManager
    ) {
        $this->orderDetails = $orderDetails;
        $this->sharedStorage = $sharedStorage;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentMethodExampleFactory = $paymentMethodExampleFactory;
        $this->paymentMethodTranslationFactory = $paymentMethodTranslationFactory;
        $this->paymentMethodManager = $paymentMethodManager;
    }

    /**
     * @Given the store has a payment method :paymentMethodName with a code :paymentMethodCode and Billogram payment gateway
     */
    public function theStoreHasAPaymentMethodWithACodeAndBillogramPaymentGateway(
        string $paymentMethodName,
        string $paymentMethodCode
    ): void {
        $paymentMethod = $this->createPaymentMethodBillogram(
            $paymentMethodName,
            $paymentMethodCode,
            BillogramGatewayFactory::FACTORY_NAME,
            'Billogram'
        );

        $paymentMethod->getGatewayConfig()->setConfig(
            [
                'apiKey' => \getenv('billogram_api_password'),
                'username' => \getenv('billogram_api_username'),
                'apiUrl' => 'https://sandbox.billogram.com/api/v2',
            ]
        );

        $this->paymentMethodManager->flush();
    }

    /**
     * @Then I should be notified that my payment is being processed
     */
    public function iShouldBeNotifiedThatMyPaymentIsBeingProcessed()
    {
        $this->assertNotification('Payment is being processed.');
    }

    /**
     * @param string   $name
     * @param string   $code
     * @param string   $factoryName
     * @param string   $description
     * @param bool     $addForCurrentChannel
     * @param int|null $position
     *
     * @return PaymentMethodInterface
     */
    private function createPaymentMethodBillogram(
        string $name,
        string $code,
        string $factoryName,
        string $description = '',
        bool $addForCurrentChannel = true,
        int $position = null
    ): PaymentMethodInterface {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodExampleFactory->create(
            [
                'name' => ucfirst($name),
                'code' => $code,
                'description' => $description,
                'gatewayName' => $factoryName,
                'gatewayFactory' => $factoryName,
                'enabled' => true,
                'channels' => ($addForCurrentChannel && $this->sharedStorage->has(
                        'channel'
                    )) ? [$this->sharedStorage->get('channel')] : [],
            ]
        );

        if (null !== $position) {
            $paymentMethod->setPosition($position);
        }

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }

    /**
     * @param string $expectedNotification
     */
    private function assertNotification($expectedNotification)
    {
        $notifications = $this->orderDetails->getNotifications();
        $hasNotifications = '';

        foreach ($notifications as $notification) {
            $hasNotifications .= $notification;
            if ($notification === $expectedNotification) {
                return;
            }
        }

        throw new \RuntimeException(
            sprintf('There is no notificaiton with "%s". Got "%s"', $expectedNotification, $hasNotifications)
        );
    }
}
