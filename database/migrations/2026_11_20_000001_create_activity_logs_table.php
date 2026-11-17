<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('action', 50)->index();
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 500);
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_notes');
        Schema::dropIfExists('activity_logs');
    }
}
