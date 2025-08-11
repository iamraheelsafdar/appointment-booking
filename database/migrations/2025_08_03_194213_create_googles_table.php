<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('googles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('access_token')->nullable();
            $table->text('expires_in')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('scope')->nullable();
            $table->text('token_type')->nullable();
            $table->text('id_token')->nullable();
            $table->string('name')->nullable();
            $table->string('picture')->nullable();
            $table->string('email')->nullable();
            $table->string('token_created')->nullable();
            $table->integer('refresh_token_expires_in')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('googles');
    }
};
