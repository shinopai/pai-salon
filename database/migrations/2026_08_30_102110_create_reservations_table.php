<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->string('reservation_number')->unique();

            $table->foreignId('customer_id')
                ->constrained('customers');

            $table->string('customer_name');
            $table->string('customer_email');

            $table->foreignId('staff_id')
                ->constrained('staffs');

            $table->foreignId('menu_id')
                ->constrained('menus');

            $table->timestamp('start_at');
            $table->timestamp('end_at');

            $table->string('status');

            $table->string('cancellation_token');

            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['staff_id', 'start_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
