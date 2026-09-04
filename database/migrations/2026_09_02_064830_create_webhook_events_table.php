<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('provider_event_id');
            $table->string('event_type');
            $table->string('processing_status')->default('pending');
            $table->json('payload');
            $table->text('error_information')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['provider', 'provider_event_id']);
        });
    }
    public function down() {
        Schema::dropIfExists('webhook_events');
    }

};
