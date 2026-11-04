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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();

            $table->decimal('amount', 10, 2);
            $table->timestamp('payment_date');

            $table->enum('payment_status', ['pending_verification', 'completed', 'failed', 'rejected'])->index();

            $table->string('transaction_reference')->unique();
            $table->string('payment_method')->default('gcash_manual');

            $table->string('gcash_reference_number')->unique();
            $table->string('gcash_sender_name');
            $table->string('gcash_sender_number');
            $table->string('screenshot_path')->nullable();

            $table->timestamp('submitted_at')->index();
            $table->foreignId('verified_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'payment_status']);
            $table->index('gcash_reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

