<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Staff roles (owner, manager, editor), a "choose your own password" flag, the last sign-in,
 * and the history of changes made in the admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->nullable()->after('is_admin');
            $table->boolean('must_change_password')->default(false)->after('role');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
        });

        // Everyone who could already use the admin keeps full access.
        DB::table('users')->where('is_admin', true)->update(['role' => 'owner']);

        // The starter account is created with the password "password". Anyone still using it
        // is asked to choose their own at the next sign-in.
        foreach (DB::table('users')->where('is_admin', true)->get(['id', 'password']) as $admin) {
            if (Hash::check('password', (string) $admin->password)) {
                DB::table('users')->where('id', $admin->id)->update(['must_change_password' => true]);
            }
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 120)->nullable(); // kept even if the account is deleted later
            $table->string('action', 120);
            $table->string('subject', 255)->nullable();
            $table->string('details', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'must_change_password', 'last_login_at']);
        });
    }
};
