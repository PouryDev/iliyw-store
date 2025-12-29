<?php

namespace App\Actions\Campaign;

use App\Actions\BaseAction;
use App\Models\OrderItem;
use App\Models\CampaignSale;
use App\Events\CampaignSaleRecorded;

class RecordCampaignSaleAction extends BaseAction
{
    /**
     * Record campaign sale from order item
     *
     * @param OrderItem $orderItem
     * @return CampaignSale|null
     */
    public function execute(...$params): ?CampaignSale
    {
        [$orderItem] = $params;

        if (!$orderItem->campaign_id) {
            return null;
        }

        $campaignSale = CampaignSale::create([
            'campaign_id' => $orderItem->campaign_id,
            'order_item_id' => $orderItem->id,
            'product_id' => $orderItem->product_id,
            'product_variant_id' => $orderItem->product_variant_id,
            'quantity' => $orderItem->quantity,
            'discount_amount' => $orderItem->campaign_discount_amount * $orderItem->quantity,
            'sale_amount' => $orderItem->line_total,
        ]);

        // Dispatch event
        event(new CampaignSaleRecorded($campaignSale));

        return $campaignSale;
    }
}

