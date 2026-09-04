<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained()->restrictOnDelete();
            $table->foreignId('earning_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique('earning_id');
        });
    }
    public function down() {
        Schema::dropIfExists('payout_items');
    }

};
