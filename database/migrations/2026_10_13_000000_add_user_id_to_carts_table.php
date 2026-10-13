<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('carts')->whereNull('session_id')->pluck('id') as $cartId) {
            DB::table('carts')->where('id', $cartId)->update(['session_id' => (string) Str::uuid()]);
        }

        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
            $table->string('session_id')->nullable(false)->change();
        });
    }
};
