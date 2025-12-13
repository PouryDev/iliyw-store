export function toNumber(value, fallback = 0) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
}

/**
 * Applies campaign discount rules and guarantees non-negative prices.
 * @param {number} basePrice - Starting price (product or variant).
 * @param {object|null} campaign - Campaign object containing type, discount_value, etc.
 * @returns {{ finalPrice: number, originalPrice: number|null, discountAmount: number }}
 */
export function calculateCampaignPrice(basePrice, campaign) {
    const safeBase = Math.max(0, toNumber(basePrice, 0));

    if (!campaign) {
        return {
            finalPrice: safeBase,
            originalPrice: null,
            discountAmount: 0,
        };
    }

    let finalPrice = safeBase;
    const discountValue = Math.max(0, toNumber(campaign.discount_value, 0));

    if (campaign.type === 'percentage') {
        const raw = Math.round(safeBase * (1 - discountValue / 100));
        finalPrice = Math.max(0, raw);

        if (typeof campaign.max_discount_amount === 'number') {
            const maxDiscount = Math.max(0, toNumber(campaign.max_discount_amount, 0));
            const actualDiscount = safeBase - finalPrice;
            if (actualDiscount > maxDiscount) {
                finalPrice = Math.max(0, safeBase - maxDiscount);
            }
        }
    } else if (campaign.type === 'fixed') {
        finalPrice = Math.max(0, safeBase - discountValue);
    }

    return {
        finalPrice,
        originalPrice: safeBase,
        discountAmount: safeBase - finalPrice,
    };
}

