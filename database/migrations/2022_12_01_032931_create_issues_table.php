<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('index')->default(0);
            $table->integer('proj_id');
            $table->string('issue');
            $table->string('category');
            $table->json('action')->default("[]");
            $table->string('priority')->comment('Critical / High / Medium / Low');
            $table->timestamp('open_date')->useCurrent();
            $table->timestamp('close_date')->nullable();
            $table->string('initiator')->nullable();
            $table->json('issue_owners')->default("[]");
            $table->integer('sheet_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issues');
    }
};
