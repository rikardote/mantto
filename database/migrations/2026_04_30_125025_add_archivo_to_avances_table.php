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
        Schema::table('avances', function (Blueprint $table) {
            $table->string('archivo_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('avances', function (Blueprint $table) {
            $table->dropColumn('archivo_path');
        });
    }
};
