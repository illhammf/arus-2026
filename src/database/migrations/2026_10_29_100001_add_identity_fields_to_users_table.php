<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('status', 20)->default('active')->after('phone_verified_at');
            $table->string('currency', 3)->default('IDR')->after('status');
            $table->string('locale', 10)->default('id')->after('currency');
            $table->string('timezone', 64)->default('Asia/Jakarta')->after('locale');
            $table->timestamp('last_login_at')->nullable()->after('timezone');
        });

        // Existing administrators keep using their email. Regular users can
        // register with a phone number only.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'status',
                'currency',
                'locale',
                'timezone',
                'last_login_at',
            ]);
        });
    }
};
