<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('flexible_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->longText('query')->nullable();
            $table->longText('columns')->nullable();
            $table->longText('parameters')->nullable();
			$table->longText('charts')->nullable();
			$table->string('pre_function')->nullable();
			$table->string('post_function')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
