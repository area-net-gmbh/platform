<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Customer\Rule;

use Shopware\Core\Checkout\CheckoutRuleScope;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleConfig;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @package business-ops
 */
class IsNewsletterRecipientRule extends Rule
{
    public const RULE_NAME = 'customerIsNewsletterRecipient';

    protected bool $isNewsletterRecipient;

    /**
     * @internal
     */
    public function __construct(bool $isNewsletterRecipient = true)
    {
        parent::__construct();
        $this->isNewsletterRecipient = $isNewsletterRecipient;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$scope instanceof CheckoutRuleScope) {
            return false;
        }

        if (!$customer = $scope->getSalesChannelContext()->getCustomer()) {
            return false;
        }

        if ($this->isNewsletterRecipient) {
            return $this->matchIsNewsletterRecipient($customer, $scope->getSalesChannelContext());
        }

        return !$this->matchIsNewsletterRecipient($customer, $scope->getSalesChannelContext());
    }

    public function getConstraints(): array
    {
        return [
            'isNewsletterRecipient' => RuleConstraints::bool(true),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->booleanField('isNewsletterRecipient');
    }

    private function matchIsNewsletterRecipient(CustomerEntity $customer, SalesChannelContext $context): bool
    {
        $salesChannelIds = $customer->getNewsletterSalesChannelIds();

        return \is_array($salesChannelIds) && \in_array($context->getSalesChannelId(), $salesChannelIds, true);
    }
}
