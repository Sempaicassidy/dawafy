<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand_name')->nullable();
            $table->string('generic_name')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories');
            $table->string('tmda_reg_number')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('manufacture_lic_no')->nullable();
            $table->string('strength')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('reorder_level')->default(10);
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
