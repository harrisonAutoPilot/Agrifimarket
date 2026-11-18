<?php

return [
    'commission_rate' => 0.015,
    'platform_fee' => 5000,
    'haul_base_per_tonne' => 8500,
    'contact_email' => 'hello@agrifimarket.test',
    'contact_phone' => '+234 800 000 0146',
    'contact_city' => 'Abuja commodity desk',

    'roles' => [
        'admin' => 'Desk operator',
        'farmer' => 'Farmer',
        'aggregator' => 'Aggregator',
        'buyer' => 'Buyer',
        'dealer' => 'Agro-dealer',
        'logistics' => 'Haulier',
        'insurer' => 'Insurer',
    ],

    'register_roles' => [
        'farmer' => 'Farmer',
        'aggregator' => 'Aggregator',
        'buyer' => 'Buyer or processor',
        'dealer' => 'Agro-dealer',
        'logistics' => 'Haulier',
        'insurer' => 'Insurer',
    ],

    'account_types' => [
        'individual' => 'Individual',
        'business' => 'Business',
    ],

    'user_statuses' => [
        'pending' => 'Pending',
        'under_review' => 'Under review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
    ],

    'listing_statuses' => [
        'draft' => 'Draft',
        'active' => 'Active',
        'paused' => 'Paused',
        'matched' => 'Matched',
        'sold' => 'Sold',
        'expired' => 'Expired',
        'withdrawn' => 'Withdrawn',
        'rejected' => 'Rejected',
    ],

    'trade_statuses' => [
        'trade_agreed' => 'Trade agreed',
        'contract_issued' => 'Contract issued',
        'logistics_assigned' => 'Logistics assigned',
        'in_transit' => 'In transit',
        'delivered' => 'Delivered',
        'quality_verified' => 'Quality verified',
        'payment_settled' => 'Payment settled',
        'closed' => 'Closed',
        'disputed' => 'Disputed',
    ],

    'states' => [
        'Adamawa', 'Bauchi', 'Benue', 'Borno', 'Gombe', 'Jigawa', 'Kaduna', 'Kano',
        'Kebbi', 'Kogi', 'Kwara', 'Nasarawa', 'Niger', 'Ogun', 'Oyo', 'Plateau',
        'Sokoto', 'Taraba', 'Yobe', 'FCT',
    ],

    'zones' => [
        'North East', 'North West', 'North Central', 'South West', 'Nationwide',
    ],

    'grades' => ['FAQ', 'Grade A', 'Grade B', 'Cleaned'],

    'delivery_types' => [
        'pickup' => 'Pickup at hub or farm',
        'door' => 'Delivery to the buyer',
    ],

    'document_types' => [
        'identification' => 'Means of identification',
        'proof_of_address' => 'Proof of address',
        'cac' => 'CAC certificate',
        'guarantor' => 'Guarantor identification',
        'haulage_permit' => 'Haulage permit',
        'vehicle' => 'Vehicle particulars',
        'naicom' => 'NAICOM licence',
        'policy_sample' => 'Sample policy',
        'other' => 'Other supporting document',
    ],
];
