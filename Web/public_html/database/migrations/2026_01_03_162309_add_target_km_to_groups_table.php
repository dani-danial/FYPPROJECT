<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
{
    Schema::table('groups', function (Blueprint $table) {
        // Add the missing column
        $table->decimal('target_km', 8, 2)->default(0.00)->after('name'); 
        
        // Check if you are missing any other columns from your Android code:
        // status? creator_id? icon_url?
        // If 'status' is missing too, add: $table->string('status')->default('active');
    });
}

public function down()
{
    Schema::table('groups', function (Blueprint $table) {
        $table->dropColumn('target_km');
    });
}
};
