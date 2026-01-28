<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientIdToIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('incomes', 'client_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->uuid('client_id')->nullable()->after('id');
                $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('incomes', 'client_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            });
        }
    }
}
