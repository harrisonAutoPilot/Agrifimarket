<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('original_name');
            $table->string('path');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('commodities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('default_unit')->default('kg');
            $table->text('description')->nullable();
            $table->string('season_note')->nullable();
            $table->string('primary_markets')->nullable();
            $table->timestamps();
        });

        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('state');
            $table->string('code', 8);
            $table->timestamps();
        });

        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commodity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_per_kg', 12, 2);
            $table->decimal('price_per_bag', 14, 2);
            $table->decimal('price_per_tonne', 14, 2);
            $table->decimal('bag_weight_kg', 8, 2)->default(100);
            $table->date('recorded_on');
            $table->string('source')->default('official');
            $table->text('notes')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['commodity_id', 'recorded_on']);
        });

        Schema::create('collection_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('state');
            $table->string('city');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('supply_listings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained();
            $table->decimal('quantity', 12, 2);
            $table->decimal('price_per_kg', 12, 2);
            $table->decimal('moisture', 5, 2)->nullable();
            $table->decimal('purity', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('state');
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('status')->default('active')->index();
            $table->text('description')->nullable();
            $table->date('expires_on')->nullable();
            $table->timestamps();
        });

        Schema::create('demand_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained();
            $table->decimal('quantity', 12, 2);
            $table->decimal('quantity_filled', 12, 2)->default(0);
            $table->decimal('price_per_kg', 12, 2);
            $table->decimal('moisture_max', 5, 2)->nullable();
            $table->decimal('purity_min', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('delivery_type')->default('pickup');
            $table->string('state');
            $table->string('city');
            $table->string('address')->nullable();
            $table->unsignedSmallInteger('payment_days')->default(7);
            $table->string('status')->default('active')->index();
            $table->text('description')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();
        });

        Schema::create('vault_files', function (Blueprint $table) {
            $table->id();
            $table->morphs('fileable');
            $table->string('type')->default('attachment');
            $table->string('original_name');
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('supply_listing_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('demand_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('supplier_id')->constrained('users');
            $table->foreignId('commodity_id')->constrained();
            $table->decimal('quantity', 12, 2);
            $table->decimal('price_per_kg', 12, 2);
            $table->decimal('grade_factor', 8, 4)->default(1);
            $table->decimal('moisture', 5, 2)->nullable();
            $table->decimal('purity', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->string('delivery_mode')->default('platform');
            $table->string('delivery_state');
            $table->string('delivery_city');
            $table->string('delivery_address')->nullable();
            $table->foreignId('logistics_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('insurer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('logistics_cost', 14, 2)->default(0);
            $table->decimal('insurance_premium', 14, 2)->default(0);
            $table->decimal('commodity_value', 14, 2)->default(0);
            $table->decimal('commission', 14, 2)->default(0);
            $table->decimal('platform_fee', 14, 2)->default(0);
            $table->decimal('buyer_total', 14, 2)->default(0);
            $table->decimal('supplier_net', 14, 2)->default(0);
            $table->string('status')->default('trade_agreed')->index();
            $table->string('previous_status')->nullable();
            $table->text('quality_notes')->nullable();
            $table->timestamp('agreed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('trade_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('match_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('demand_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->json('reasons')->nullable();
            $table->string('status')->default('suggested');
            $table->timestamps();
            $table->unique(['supply_listing_id', 'demand_request_id']);
        });

        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number');
            $table->string('driver_name');
            $table->string('driver_phone');
            $table->decimal('capacity_tonnes', 8, 2);
            $table->string('route_zone');
            $table->string('status')->default('pending');
            $table->string('availability')->default('available');
            $table->timestamps();
        });

        Schema::create('rate_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('origin_state');
            $table->string('destination_state');
            $table->decimal('rate_per_tonne', 12, 2);
            $table->timestamps();
        });

        Schema::create('haul_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('truck_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price_per_tonne', 12, 2);
            $table->decimal('total', 14, 2);
            $table->text('note')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamps();
        });

        Schema::create('insurance_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->decimal('rate_percent', 6, 3);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('insurance_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_product_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('premium', 14, 2);
            $table->text('cover_note')->nullable();
            $table->string('policy_number')->nullable();
            $table->string('status')->default('quoted');
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 16, 2)->default(0);
            $table->string('currency', 3)->default('NGN');
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('amount', 16, 2);
            $table->decimal('balance_after', 16, 2);
            $table->string('reference');
            $table->string('narration');
            $table->string('status')->default('completed');
            $table->nullableMorphs('related');
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('supplier_id')->constrained('users');
            $table->decimal('amount', 16, 2);
            $table->string('status')->default('issued');
            $table->date('due_on')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payer_id')->constrained('users');
            $table->foreignId('payee_id')->constrained('users');
            $table->decimal('amount', 16, 2);
            $table->string('status')->default('completed');
            $table->string('reference');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->string('status')->default('open');
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('input_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('season');
            $table->decimal('coverage_ha', 8, 2);
            $table->decimal('price', 14, 2);
            $table->decimal('contribution_percent', 5, 2);
            $table->text('description')->nullable();
            $table->json('items')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('input_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('input_bundle_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('submitted');
            $table->decimal('contribution_amount', 14, 2);
            $table->decimal('financed_amount', 14, 2);
            $table->decimal('outstanding', 14, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('dealer_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category');
            $table->string('brand')->nullable();
            $table->decimal('price', 14, 2);
            $table->string('unit');
            $table->string('state');
            $table->string('city');
            $table->text('specifications')->nullable();
            $table->text('description')->nullable();
            $table->decimal('stock', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('calculator_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trade_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('commodity_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('price_per_kg', 12, 2);
            $table->decimal('grade_factor', 8, 4)->default(1);
            $table->decimal('logistics_cost', 14, 2)->default(0);
            $table->decimal('insurance_premium', 14, 2)->default(0);
            $table->decimal('fees', 14, 2)->default(0);
            $table->decimal('commission', 14, 2)->default(0);
            $table->decimal('trade_value', 14, 2);
            $table->decimal('landed_cost', 14, 2);
            $table->decimal('net_proceeds', 14, 2);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_snapshots');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('dealer_listings');
        Schema::dropIfExists('input_applications');
        Schema::dropIfExists('input_bundles');
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('insurance_quotes');
        Schema::dropIfExists('insurance_products');
        Schema::dropIfExists('haul_quotes');
        Schema::dropIfExists('rate_cards');
        Schema::dropIfExists('trucks');
        Schema::dropIfExists('match_suggestions');
        Schema::dropIfExists('trade_events');
        Schema::dropIfExists('trades');
        Schema::dropIfExists('vault_files');
        Schema::dropIfExists('demand_requests');
        Schema::dropIfExists('supply_listings');
        Schema::dropIfExists('collection_centers');
        Schema::dropIfExists('prices');
        Schema::dropIfExists('markets');
        Schema::dropIfExists('commodities');
        Schema::dropIfExists('documents');
    }
};
