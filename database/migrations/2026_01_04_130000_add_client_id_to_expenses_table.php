<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientIdToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // If the column doesn't exist, create it. If it exists, ensure it's the correct type and FK.
        if (!Schema::hasColumn('expenses', 'client_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->uuid('client_id')->nullable()->after('id');
            });
        } else {
            // convert existing column to CHAR(36) to match users.id (UUID)
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `expenses` MODIFY `client_id` CHAR(36) NULL;");
        }

        // Add foreign key only if it doesn't already exist.
        $fk = \Illuminate\Support\Facades\DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='expenses' AND COLUMN_NAME='client_id' AND REFERENCED_TABLE_NAME='users'");
        if (empty($fk)) {
            Schema::table('expenses', function (Blueprint $table) {
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
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
}
