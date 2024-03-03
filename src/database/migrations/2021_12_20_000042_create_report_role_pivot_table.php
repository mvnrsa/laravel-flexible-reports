<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportRolePivotTable extends Migration
{
    public function up()
    {
        Schema::create('flexible_report_role', function (Blueprint $table) {
            $table->unsignedBigInteger('report_id');
            $table->foreign('report_id', 'report_id_fk')->references('id')->on('flexible_reports')->onDelete('cascade');
            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id', 'role_id_fk')->references('id')->on('roles')->onDelete('cascade');
        });
    }
}
