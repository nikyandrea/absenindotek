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
        Schema::create('incentive_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('month'); // format: YYYY-MM
            $table->enum('type', ['luar_kota', 'hari_libur', 'tepat_waktu', 'lain'])->default('lain');
            $table->decimal('amount', 10, 2);
            $table->text('reason');
            $table->enum('source', ['auto', 'manual'])->default('auto');
            $table->string('related_type')->nullable(); // polymorphic
            $table->unsignedBigInteger('related_id')->nullable(); // attendance_session_id, dll
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['user_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentive_adjustments');
    }
};
