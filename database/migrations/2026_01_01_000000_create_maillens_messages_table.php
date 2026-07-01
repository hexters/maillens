<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->table(), function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable()->index();
            $table->string('subject')->nullable();
            $table->json('from')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->json('reply_to')->nullable();
            $table->longText('html')->nullable();
            $table->longText('text')->nullable();
            $table->json('attachments')->nullable();
            $table->longText('raw')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->boolean('read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table());
    }

    protected function table(): string
    {
        return config('maillens.table', 'maillens_messages');
    }
};
