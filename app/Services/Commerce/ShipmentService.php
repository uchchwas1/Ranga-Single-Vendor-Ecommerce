<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Order;
use App\Models\Shipment;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\ShipmentStatus;
use App\Support\Enums\ShippingStatus;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

/**
 * Application service for creating and tracking shipments.
 */
class ShipmentService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly OrderManagementService $orders,
    ) {
    }

    /**
     * Dispatch a shipment for an order and advance its status.
     *
     * @param  array{tracking_number?: string|null, carrier?: string|null, carrier_url?: string|null, estimated_delivery?: string|null}  $data
     */
    public function ship(Order $order, array $data, ?string $actorId = null): Shipment
    {
        return DB::transaction(function () use ($order, $data, $actorId): Shipment {
            /** @var Shipment $shipment */
            $shipment = $order->shipments()->create([
                'tracking_number' => $data['tracking_number'] ?? null,
                'carrier' => $data['carrier'] ?? null,
                'carrier_url' => $data['carrier_url'] ?? null,
                'estimated_delivery' => $data['estimated_delivery'] ?? null,
                'shipped_at' => Date::now(),
                'status' => ShipmentStatus::Shipped,
            ]);

            $order->forceFill(['shipping_status' => ShippingStatus::Shipped])->save();
            $this->orders->changeStatus($order, OrderStatus::Shipped, __('commerce.shipment.dispatched'), true, $actorId);

            return $shipment;
        });
    }

    /**
     * Mark a shipment (and its order) as delivered.
     */
    public function markDelivered(Shipment $shipment, ?string $actorId = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $actorId): Shipment {
            $shipment->forceFill([
                'status' => ShipmentStatus::Delivered,
                'delivered_at' => Date::now(),
            ])->save();

            $order = $shipment->order;
            $order->forceFill(['shipping_status' => ShippingStatus::Delivered])->save();
            $this->orders->changeStatus($order, OrderStatus::Delivered, __('commerce.shipment.delivered'), true, $actorId);

            return $shipment;
        });
    }
}
