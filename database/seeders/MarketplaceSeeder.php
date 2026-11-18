<?php

namespace Database\Seeders;

use App\Models\CollectionCenter;
use App\Models\Commodity;
use App\Models\DealerListing;
use App\Models\DemandRequest;
use App\Models\HaulQuote;
use App\Models\InputBundle;
use App\Models\InsuranceProduct;
use App\Models\InsuranceQuote;
use App\Models\Market;
use App\Models\Price;
use App\Models\RateCard;
use App\Models\SupplyListing;
use App\Models\Truck;
use App\Models\User;
use App\Services\MatchingService;
use App\Services\TradeService;
use App\Services\WalletService;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $wallets = app(WalletService::class);
        $trades = app(TradeService::class);
        $matching = app(MatchingService::class);

        $admin = $this->person([
            'name' => 'Ada Okonkwo',
            'email' => 'ada.okonkwo@agrifimarket.test',
            'phone' => '08010000001',
            'role' => 'admin',
            'business_name' => 'AgrifiMarket desk',
            'state' => 'FCT',
            'city' => 'Abuja',
        ]);
        $buyer = $this->person([
            'name' => 'Ibrahim Bello',
            'email' => 'ibrahim.bello@agrifimarket.test',
            'phone' => '08010000002',
            'role' => 'buyer',
            'business_name' => 'Sahel Mills',
            'rc_number' => 'RC-2201841',
            'state' => 'Kano',
            'city' => 'Kano',
            'address' => 'Sharada industrial layout',
        ]);
        $aggregator = $this->person([
            'name' => 'Amina Yusuf',
            'email' => 'amina.yusuf@agrifimarket.test',
            'phone' => '08010000003',
            'role' => 'aggregator',
            'business_name' => 'Yusuf Grain Assembly',
            'state' => 'Kaduna',
            'city' => 'Giwa',
            'guarantor_name' => 'Sule Yusuf',
            'guarantor_phone' => '08010000013',
        ]);
        $farmer = $this->person([
            'name' => 'Chinedu Okafor',
            'email' => 'chinedu.okafor@agrifimarket.test',
            'phone' => '08010000004',
            'role' => 'farmer',
            'account_type' => 'individual',
            'business_name' => null,
            'state' => 'Benue',
            'city' => 'Makurdi',
        ]);
        $dealer = $this->person([
            'name' => 'Grace Danjuma',
            'email' => 'grace.danjuma@agrifimarket.test',
            'phone' => '08010000005',
            'role' => 'dealer',
            'business_name' => 'GreenInput Stores',
            'state' => 'Kaduna',
            'city' => 'Kaduna',
        ]);
        $logistics = $this->person([
            'name' => 'Musa Lawal',
            'email' => 'musa.lawal@agrifimarket.test',
            'phone' => '08010000006',
            'role' => 'logistics',
            'business_name' => 'Benue Haul',
            'state' => 'Kaduna',
            'city' => 'Kaduna',
        ]);
        $insurer = $this->person([
            'name' => 'Fatima Sule',
            'email' => 'fatima.sule@agrifimarket.test',
            'phone' => '08010000007',
            'role' => 'insurer',
            'business_name' => 'Sahel Cover',
            'state' => 'FCT',
            'city' => 'Abuja',
        ]);
        $this->person([
            'name' => 'Hassan Bello',
            'email' => 'hassan.bello@agrifimarket.test',
            'phone' => '08010000008',
            'role' => 'farmer',
            'account_type' => 'individual',
            'business_name' => null,
            'status' => 'under_review',
            'approved_at' => null,
            'state' => 'Kano',
            'city' => 'Dawanau',
        ]);

        foreach ([
            [$buyer, 40000000],
            [$aggregator, 1500000],
            [$farmer, 800000],
            [$dealer, 400000],
            [$logistics, 900000],
            [$insurer, 2500000],
        ] as [$user, $amount]) {
            $wallets->credit($user, $amount, 'Opening balance', 'OPEN-'.$user->id);
        }

        $crops = collect([
            ['Paddy rice', 'paddy-rice', 'grain', 'A wet-season staple milled for local consumption.', 'Harvest from October', 'Makurdi, Lafia'],
            ['White maize', 'white-maize', 'grain', 'Feed and food maize, usually traded at FAQ moisture.', 'August to October', 'Giwa, Minna, Dawanau'],
            ['Soybeans', 'soybeans', 'oilseed', 'Crushing stock for oil and cake.', 'October to December', 'Giwa, Kumo'],
            ['Sesame', 'sesame', 'oilseed', 'Cleaned seed for export and local crushers.', 'November to January', 'Potiskum, Dawanau'],
            ['Groundnut', 'groundnut', 'oilseed', 'Gargaja-type nuts for oil and confectionery.', 'October to December', 'Kano, Potiskum'],
            ['White sorghum', 'white-sorghum', 'grain', 'Food grain for flour and malt.', 'November', 'Potiskum, Hadejia'],
            ['Yellow sorghum', 'yellow-sorghum', 'grain', 'Used in malt and animal feed.', 'November', 'Potiskum'],
            ['Millet', 'millet', 'grain', 'Early cereal that moves before the main harvest.', 'September to October', 'Potiskum, Kumo'],
            ['White cowpea', 'white-cowpea', 'pulse', 'Beans for urban retail and processors.', 'October', 'Dawanau, Kumo'],
            ['Hibiscus', 'hibiscus', 'cash_crop', 'Dried calyces packed for drink makers.', 'December', 'Hadejia'],
            ['Rice bran', 'rice-bran', 'grain', 'Milling by-product for feed mills.', 'Year round', 'Kaduna'],
            ['Honey beans', 'honey-beans', 'pulse', 'Oloyin beans for retail packs.', 'October', 'Kano'],
        ])->mapWithKeys(function (array $row) {
            $crop = Commodity::create([
                'name' => $row[0],
                'slug' => $row[1],
                'category' => $row[2],
                'description' => $row[3],
                'season_note' => $row[4],
                'primary_markets' => $row[5],
            ]);

            return [$row[1] => $crop];
        });

        $markets = collect([
            ['Dawanau', 'Kano', 'Kano', 'KN'],
            ['Potiskum', 'Potiskum', 'Yobe', 'YB'],
            ['Giwa', 'Giwa', 'Kaduna', 'KD'],
            ['Hadejia', 'Hadejia', 'Jigawa', 'JG'],
            ['Kumo', 'Kumo', 'Gombe', 'GM'],
            ['Lafia', 'Lafia', 'Nasarawa', 'NS'],
            ['Makurdi', 'Makurdi', 'Benue', 'BN'],
            ['Minna', 'Minna', 'Niger', 'NG'],
        ])->mapWithKeys(function (array $row) {
            $market = Market::create(['name' => $row[0], 'city' => $row[1], 'state' => $row[2], 'code' => $row[3]]);

            return [$row[0] => $market];
        });

        $book = [
            ['white-maize', 'Giwa', 405],
            ['white-maize', 'Minna', 398],
            ['white-maize', 'Dawanau', 420],
            ['soybeans', 'Giwa', 575],
            ['soybeans', 'Kumo', 590],
            ['sesame', 'Potiskum', 1480],
            ['sesame', 'Dawanau', 1520],
            ['paddy-rice', 'Makurdi', 470],
            ['paddy-rice', 'Lafia', 455],
            ['millet', 'Potiskum', 360],
            ['millet', 'Kumo', 348],
            ['groundnut', 'Dawanau', 910],
            ['white-sorghum', 'Hadejia', 310],
            ['white-cowpea', 'Dawanau', 780],
            ['hibiscus', 'Hadejia', 1250],
            ['honey-beans', 'Dawanau', 1180],
        ];

        foreach ($book as [$slug, $marketName, $kg]) {
            foreach ([14, 1] as $daysAgo) {
                $price = $kg + ($daysAgo === 14 ? -12 : 0);
                $bag = 100;
                Price::create([
                    'commodity_id' => $crops[$slug]->id,
                    'market_id' => $markets[$marketName]->id,
                    'price_per_kg' => $price,
                    'price_per_bag' => $price * $bag,
                    'price_per_tonne' => $price * 1000,
                    'bag_weight_kg' => $bag,
                    'recorded_on' => now()->subDays($daysAgo)->toDateString(),
                    'source' => 'official',
                    'published_by' => $admin->id,
                ]);
            }
        }

        foreach ([
            ['Giwa receiving yard', 'Kaduna', 'Giwa', 'Along the Giwa grain road'],
            ['Dawanau intake', 'Kano', 'Dawanau', 'Market road, bay 4'],
            ['Makurdi river hub', 'Benue', 'Makurdi', 'Industrial layout, plot 12'],
            ['Potiskum north shed', 'Yobe', 'Potiskum', 'Kano road, opposite the mill'],
        ] as $hub) {
            CollectionCenter::create([
                'name' => $hub[0], 'state' => $hub[1], 'city' => $hub[2], 'address' => $hub[3], 'phone' => '08020000000', 'active' => true,
            ]);
        }

        $this->listing($aggregator, $crops['soybeans'], 40, 575, 'Kaduna', 'Giwa', 11, 99, 'FAQ', 'Cleaned soy from Giwa clusters.');
        $this->listing($aggregator, $crops['white-maize'], 60, 400, 'Kaduna', 'Giwa', 12, 98, 'FAQ', 'White maize, bagged at the yard.');
        $this->listing($aggregator, $crops['sesame'], 18, 1470, 'Yobe', 'Potiskum', 7.5, 99, 'Cleaned', 'Export-cleaned sesame.');
        $this->listing($farmer, $crops['paddy-rice'], 22, 465, 'Benue', 'Makurdi', 13, 97, 'FAQ', 'Paddy from the river farms.');
        $this->listing($farmer, $crops['millet'], 12, 350, 'Gombe', 'Kumo', 11, 98, 'Grade A', 'Early millet, dry and bagged.');

        $this->order($buyer, $crops['soybeans'], 30, 590, 'Kaduna', 'Kaduna', 12, 98, 'Crushing soy, pickup at Giwa or delivery to Sharada.');
        $this->order($buyer, $crops['sesame'], 15, 1500, 'Kano', 'Kano', 8, 98, 'Cleaned sesame for the export line.');
        $this->order($buyer, $crops['white-maize'], 50, 410, 'Niger', 'Minna', 13, 97, 'Feed maize for the mill.');
        $this->order($buyer, $crops['paddy-rice'], 20, 480, 'Benue', 'Makurdi', 14, 96, 'Paddy for the Makurdi line.');
        $this->order($buyer, $crops['hibiscus'], 8, 1300, 'Jigawa', 'Hadejia', 12, 97, 'Whole hibiscus, dry.');

        SupplyListing::query()->where('status', 'active')->each(fn (SupplyListing $supply) => $matching->refreshSupply($supply));

        $truck = Truck::create([
            'user_id' => $logistics->id,
            'registration_number' => 'KAD 442-AG',
            'driver_name' => 'Yakubu Danladi',
            'driver_phone' => '08030000001',
            'capacity_tonnes' => 30,
            'route_zone' => 'North West',
            'status' => 'verified',
            'availability' => 'available',
        ]);
        Truck::create([
            'user_id' => $logistics->id,
            'registration_number' => 'KAD 918-AG',
            'driver_name' => 'Ibrahim Sani',
            'driver_phone' => '08030000002',
            'capacity_tonnes' => 15,
            'route_zone' => 'North Central',
            'status' => 'pending',
            'availability' => 'available',
        ]);
        RateCard::create([
            'user_id' => $logistics->id,
            'origin_state' => 'Kaduna',
            'destination_state' => 'Kano',
            'rate_per_tonne' => 12000,
        ]);

        $product = InsuranceProduct::create([
            'user_id' => $insurer->id,
            'name' => 'Cargo all-risk, grain',
            'type' => 'cargo',
            'rate_percent' => 0.45,
            'description' => 'Cover from pickup to the buyer’s receiving point.',
            'active' => true,
        ]);

        foreach ([
            ['Hybrid maize seed, 10kg', 'seed', 'Samaru', 18500, 'bag', 240],
            ['NPK 15-15-15', 'fertilizer', 'Notore', 42000, '50kg bag', 800],
            ['Urea', 'fertilizer', 'Indorama', 39000, '50kg bag', 600],
            ['Knapsack sprayer', 'equipment', 'Fieldline', 28000, 'unit', 40],
        ] as $item) {
            DealerListing::create([
                'user_id' => $dealer->id,
                'name' => $item[0],
                'category' => $item[1],
                'brand' => $item[2],
                'price' => $item[3],
                'unit' => $item[4],
                'stock' => $item[5],
                'state' => 'Kaduna',
                'city' => 'Kaduna',
                'specifications' => 'Sealed dealer stock',
                'description' => 'Available for farm pickup or hub delivery.',
                'status' => 'active',
            ]);
        }
        DealerListing::create([
            'user_id' => $dealer->id,
            'name' => 'Atrazine, 1 litre',
            'category' => 'pesticide',
            'brand' => 'Fieldline',
            'price' => 6500,
            'unit' => 'bottle',
            'stock' => 120,
            'state' => 'Kaduna',
            'city' => 'Kaduna',
            'description' => 'Waiting for the operator to publish this bottle.',
            'status' => 'pending',
        ]);

        InputBundle::create([
            'name' => 'Maize hectare kit',
            'season' => '2026 wet season',
            'coverage_ha' => 1,
            'price' => 450000,
            'contribution_percent' => 30,
            'description' => 'Seed, NPK, urea, and a basic spray pack for one hectare of maize.',
            'items' => ['10kg hybrid seed', '4 bags NPK', '2 bags urea', 'Herbicide'],
            'active' => true,
        ]);
        InputBundle::create([
            'name' => 'Rice hectare kit',
            'season' => '2026 wet season',
            'coverage_ha' => 1,
            'price' => 520000,
            'contribution_percent' => 35,
            'description' => 'Seed and fertiliser sized for a hectare of lowland rice.',
            'items' => ['25kg seed', '4 bags NPK', '2 bags urea'],
            'active' => true,
        ]);

        $closedDemand = $this->order($buyer, $crops['white-maize'], 20, 420, 'Kano', 'Kano', 13, 97, 'Settled sample lot for the mill.');
        $closed = $trades->acceptDemand($aggregator, $closedDemand, [
            'quantity' => 20,
            'price_per_kg' => 420,
            'moisture' => 12,
            'purity' => 98,
            'grade' => 'FAQ',
            'delivery_mode' => 'platform',
            'state' => 'Kaduna',
            'city' => 'Giwa',
            'address' => 'Giwa receiving yard',
        ]);
        $closed = $trades->issueContract($closed, $buyer);
        $haul = HaulQuote::create([
            'trade_id' => $closed->id,
            'user_id' => $logistics->id,
            'truck_id' => $truck->id,
            'price_per_tonne' => 12000,
            'total' => 240000,
            'note' => 'Giwa yard to Sharada.',
            'status' => 'submitted',
        ]);
        $closed = $trades->acceptHaul($closed, $haul, $buyer);
        $cover = InsuranceQuote::create([
            'trade_id' => $closed->id,
            'user_id' => $insurer->id,
            'insurance_product_id' => $product->id,
            'premium' => round((float) $closed->commodity_value * 0.0045, 2),
            'cover_note' => 'Cargo all-risk',
            'status' => 'quoted',
        ]);
        $closed = $trades->acceptCover($closed, $cover, $buyer);
        $closed = $trades->transition($closed, 'in_transit', $logistics, 'Truck KAD 442-AG left Giwa.');
        $closed = $trades->transition($closed, 'delivered', $logistics, 'Received at Sharada.');
        $closed = $trades->transition($closed, 'quality_verified', $buyer, 'Moisture 12.1%. Accepted.');
        $closed = $trades->settle($closed, $buyer);
        $trades->transition($closed, 'closed', $buyer, 'File closed.');

        $openDemand = $this->order($buyer, $crops['soybeans'], 12, 580, 'Kano', 'Kano', 12, 98, 'Soy lot waiting on quality sign-off.');
        $moving = $trades->acceptDemand($aggregator, $openDemand, [
            'quantity' => 12,
            'price_per_kg' => 580,
            'moisture' => 11,
            'purity' => 99,
            'grade' => 'Grade A',
            'delivery_mode' => 'platform',
            'state' => 'Kaduna',
            'city' => 'Giwa',
            'address' => 'Giwa receiving yard',
        ]);
        $moving = $trades->issueContract($moving, $buyer);
        $haul = HaulQuote::create([
            'trade_id' => $moving->id,
            'user_id' => $logistics->id,
            'truck_id' => $truck->id,
            'price_per_tonne' => 12500,
            'total' => 150000,
            'note' => 'Second truck on the same lane.',
            'status' => 'submitted',
        ]);
        $moving = $trades->acceptHaul($moving, $haul, $buyer);
        $moving = $trades->transition($moving, 'in_transit', $logistics, 'Loaded and moving.');
        $trades->transition($moving, 'delivered', $logistics, 'At the mill gate, waiting for the buyer’s quality check.');
    }

    private function person(array $attrs): User
    {
        return User::create(array_merge([
            'password' => 'password',
            'account_type' => 'business',
            'status' => 'approved',
            'approved_at' => now(),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'rating' => 4.7,
            'address' => 'Commodity yard',
            'bank_name' => 'Desk settlement account',
            'bank_account_name' => $attrs['business_name'] ?? $attrs['name'],
            'bank_account_number' => '0123456789',
        ], $attrs));
    }

    private function listing(User $user, Commodity $commodity, float $tonnes, float $price, string $state, string $city, float $moisture, float $purity, string $grade, string $description): SupplyListing
    {
        $listing = SupplyListing::create([
            'code' => 'TMP',
            'user_id' => $user->id,
            'commodity_id' => $commodity->id,
            'quantity' => $tonnes,
            'price_per_kg' => $price,
            'moisture' => $moisture,
            'purity' => $purity,
            'grade' => $grade,
            'state' => $state,
            'city' => $city,
            'address' => $city.' yard',
            'status' => 'active',
            'description' => $description,
            'expires_on' => now()->addDays(21)->toDateString(),
        ]);
        $listing->update(['code' => 'SUP-'.str_pad((string) $listing->id, 5, '0', STR_PAD_LEFT)]);

        return $listing;
    }

    private function order(User $buyer, Commodity $commodity, float $tonnes, float $price, string $state, string $city, float $moisture, float $purity, string $description): DemandRequest
    {
        $order = DemandRequest::create([
            'code' => 'TMP',
            'user_id' => $buyer->id,
            'commodity_id' => $commodity->id,
            'quantity' => $tonnes,
            'quantity_filled' => 0,
            'price_per_kg' => $price,
            'moisture_max' => $moisture,
            'purity_min' => $purity,
            'grade' => 'FAQ',
            'delivery_type' => 'door',
            'state' => $state,
            'city' => $city,
            'address' => $city.' receiving point',
            'payment_days' => 7,
            'status' => 'active',
            'description' => $description,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(14)->toDateString(),
        ]);
        $order->update(['code' => 'DEM-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)]);

        return $order;
    }
}
