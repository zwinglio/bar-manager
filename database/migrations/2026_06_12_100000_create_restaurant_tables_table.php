<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('waiter_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('number');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('person_count')->default(1);
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'closed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
