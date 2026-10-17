<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dda_register', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_item_id')->constrained('transaction_items');
            $table->string('patient_name');
            $table->enum('id_type', ['NIDA', 'Passport', 'Voters ID']);
            $table->string('id_number');
            $table->string('doctor_name');
            $table->foreignId('approved_by_user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dda_register');
    }
};
