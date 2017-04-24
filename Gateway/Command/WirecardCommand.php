<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

namespace Wirecard\ElasticEngine\Gateway\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Psr\Log\LoggerInterface;
use Wirecard\ElasticEngine\Gateway\Request\TransactionFactory;
use Wirecard\ElasticEngine\Gateway\Service\TransactionServiceFactory;
use Wirecard\PaymentSdk\Transaction\Reservable;

/**
 * Class WirecardCommand
 * @package Wirecard\ElasticEngine\Gateway
 */
class WirecardCommand implements CommandInterface
{
    /**
     * @var
     */
    private $transactionFactory;

    /**
     * @var TransactionServiceFactory
     */
    private $transactionServiceFactory;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * WirecardCommand constructor.
     * @param $transactionFactory
     * @param TransactionServiceFactory $transactionServiceFactory
     * @param LoggerInterface $logger
     * @param HandlerInterface $handler
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionServiceFactory $transactionServiceFactory,
        LoggerInterface $logger,
        HandlerInterface $handler
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionServiceFactory = $transactionServiceFactory;
        $this->logger = $logger;
        $this->handler = $handler;
    }

    public function execute(array $commandSubject)
    {
        $transaction = $this->transactionFactory->create($commandSubject);
        $transactionService = $this->transactionServiceFactory->create($transaction::NAME);

        $action = 'pay';
        if($transaction instanceof Reservable) {
            $action = 'reserve';
        }

        try {
            $response = $transactionService->$action($transaction);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $response = null;
        }

        if ($this->handler) {
            $this->handler->handle($commandSubject, ['paymentSDK-php' => $response]);
        }
    }
}
