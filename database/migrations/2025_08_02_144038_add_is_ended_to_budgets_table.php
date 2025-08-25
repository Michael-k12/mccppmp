<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('budgets', function (Blueprint $table) {
        $table->date('milestone_date')->nullable()->after('is_ended');
    });
}

public function down()
{
    Schema::table('budgets', function (Blueprint $table) {
        $table->dropColumn('milestone_date');
    });
}

};
