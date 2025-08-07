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
        Schema::create('maintenance_orders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['created', 'in_progress', 'pending_approval', 'approved', 'rejected'])->default('created');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_orders');
    }
};
