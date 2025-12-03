<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_logs', function (Blueprint $table) {
            // Cho phép admin_id nullable để log user actions
            $table->unsignedInteger('admin_id')->nullable()->change();
            // Thêm user_id để log cả user actions (nullable vì có thể là admin action)
            $table->unsignedInteger('user_id')->nullable()->after('admin_id');
            // Thêm foreign key cho user_id
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            // Khôi phục admin_id NOT NULL (cần xử lý dữ liệu trước)
            $table->unsignedInteger('admin_id')->nullable(false)->change();
        });
    }
};
