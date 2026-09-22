export function formatMoney(value) {
    try {
        return Number(value || 0).toLocaleString('fa-IR');
    } catch {
        return '0';
    }
}

export function getOrderItemUnitPrice(item) {
    return Number(item?.unit_price ?? item?.price ?? 0);
}

export function getOrderItemLineTotal(item) {
    if (item?.line_total != null && item.line_total !== '') {
        return Number(item.line_total);
    }
    return getOrderItemUnitPrice(item) * Number(item?.quantity || 0);
}

export function getOrderItemTitle(item) {
    return item?.product_title || item?.product?.title || item?.product?.name || 'محصول';
}

export function getOrderItemVariantLabel(item) {
    if (item?.variant_display_name) {
        return item.variant_display_name;
    }

    const color = item?.color_name || item?.color?.name;
    const size = item?.size_name || item?.size?.name;
    const parts = [];
    if (color) parts.push(`رنگ: ${color}`);
    if (size) parts.push(`سایز: ${size}`);
    return parts.join(' • ');
}

export function getOrderItemImage(item) {
    const path = item?.product_image
        || item?.product?.images?.[0]?.url
        || item?.product?.images?.[0]?.path
        || null;

    if (!path) return null;
    if (/^https?:\/\//i.test(path) || path.startsWith('/')) return path;
    return `/storage/${path}`;
}

export function getOrderProductsTotal(order) {
    const original = Number(order?.original_amount);
    if (original > 0) return original;
    return Number(order?.amount ?? order?.total_amount ?? 0);
}

export function getOrderPayableTotal(order) {
    const payable = Number(order?.final_amount);
    if (payable > 0) return payable;
    return Number(order?.total_amount ?? order?.amount ?? 0);
}
