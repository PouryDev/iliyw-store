<?php

namespace App\Actions\DiscountCode;

use App\Actions\BaseAction;
use App\Exceptions\InvalidDiscountCodeException;
use App\Repositories\Contracts\DiscountCodeRepositoryInterface;
use App\Models\User;

class ValidateDiscountCodeAction extends BaseAction
{
    public function __construct(
        protected DiscountCodeRepositoryInterface $discountCodeRepository
    ) {}

    /**
     * Validate discount code
     *
     * @param string $code
     * @param User|null $user
     * @param int $orderAmount
     * @return array ['valid' => bool, 'discount_code' => DiscountCode|null, 'message' => string|null]
     * @throws InvalidDiscountCodeException
     */
    public function execute(...$params): array
    {
        [$code, $user, $orderAmount] = $params;

        // Find discount code
        $discountCode = $this->discountCodeRepository->findByCode($code);

        if (!$discountCode) {
            throw InvalidDiscountCodeException::notFound();
        }

        // Check if active
        if (!$discountCode->is_active) {
            throw InvalidDiscountCodeException::inactive();
        }

        // Check start date
        if ($discountCode->starts_at && $discountCode->starts_at->isFuture()) {
            throw InvalidDiscountCodeException::notFound('کد تخفیف هنوز فعال نشده است');
        }

        // Check expiration
        if ($discountCode->expires_at && $discountCode->expires_at->isPast()) {
            throw InvalidDiscountCodeException::expired();
        }

        // Check usage limit
        if ($discountCode->usage_limit) {
            $usageCount = $discountCode->usages()->count();
            if ($usageCount >= $discountCode->usage_limit) {
                throw InvalidDiscountCodeException::usageLimitExceeded();
            }
        }

        // Check minimum order amount
        if ($discountCode->min_order_amount && $orderAmount < $discountCode->min_order_amount) {
            throw InvalidDiscountCodeException::minimumAmountNotMet($discountCode->min_order_amount);
        }

        // Check if user has already used this code
        if ($user) {
            $hasUsed = $discountCode->usages()
                ->where('user_id', $user->id)
                ->exists();

            if ($hasUsed) {
                throw InvalidDiscountCodeException::alreadyUsed();
            }
        }

        return [
            'valid' => true,
            'discount_code' => $discountCode,
            'message' => null,
        ];
    }
}

