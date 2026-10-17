<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default categories
        DB::table('categories')->insert([
            ['name' => 'OTC', 'description' => 'Over the Counter'],
            ['name' => 'P', 'description' => 'Pharmacy'],
            ['name' => 'POM', 'description' => 'Prescription Only Medicine'],
            ['name' => 'Controlled', 'description' => 'Controlled Substances'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
