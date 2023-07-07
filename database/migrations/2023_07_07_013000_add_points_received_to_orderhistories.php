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
        Schema::table('orderhistories', function (Blueprint $table) {
            $table->integer('points_adjustment')->after('initial_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orderhistories', function (Blueprint $table) {
            $table->integer('points_adjustment');
        });
    }
};
