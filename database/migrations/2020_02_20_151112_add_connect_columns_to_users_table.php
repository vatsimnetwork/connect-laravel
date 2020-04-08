<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConnectColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove non-needed columns
            $table->dropColumn('email_verified_at');
            $table->dropColumn('name');
            $table->dropColumn('password');
            
            // Add needed columns
            $table->string('fname', 191);
            $table->string('lname', 191);
            $table->text('access_token')->after('permissions')->nullable();
            $table->text('refresh_token')->after('access_token')->nullable();
            $table->unsignedBigInteger('token_expires')->after('refresh_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('token_expires');
            $table->dropColumn('refresh_token');
            $table->dropColumn('access_token');
            
            $table->string('name');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
        });
    }
}
