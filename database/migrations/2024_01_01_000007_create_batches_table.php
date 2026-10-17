<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained('drugs');
            $table->string('batch_number');
            $table->integer('quantity');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
