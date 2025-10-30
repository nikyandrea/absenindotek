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
        Schema::create('monthly_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('year'); // Tahun
            $table->integer('month'); // Bulan (1-12)
            $table->string('type'); // 'deduction' atau 'incentive'
            $table->string('name'); // Nama adjustment (e.g., "Potongan BPJS", "Bonus Performa")
            $table->decimal('amount', 10, 2); // Nominal (positif untuk incentive, negatif untuk deduction)
            $table->text('notes')->nullable(); // Catatan
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Admin yang menambahkan
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'year', 'month']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_adjustments');
    }
};
