<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('type', 20);
            $table->string('status', 20)->default('posted');
            $table->decimal('amount', 18, 2);
            $table->char('currency', 3)->default('IDR');
            $table->dateTime('occurred_at');
            $table->string('description');
            $table->text('notes')->nullable();
            $table->uuid('transfer_group_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'occurred_at']);
            $table->index(['account_id', 'occurred_at']);
            $table->index(['user_id', 'category_id', 'occurred_at']);
            $table->index(['user_id', 'type', 'occurred_at']);
            $table->index('transfer_group_id');
            $table->unique(['transfer_group_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
