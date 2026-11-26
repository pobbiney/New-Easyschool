<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addLegacyUserColumns();

        $createdCategories = ! Schema::hasTable('user_cat');
        if ($createdCategories) {
            Schema::create('user_cat', function (Blueprint $table) {
                $table->id('cat_id');
                $table->string('cat_name', 100);
                $table->string('status', 10);
            });
        }

        $createdLinks = ! Schema::hasTable('user_links');
        if ($createdLinks) {
            Schema::create('user_links', function (Blueprint $table) {
                $table->increments('link_id');
                $table->string('link_url', 250)->nullable();
                $table->string('link_name', 250)->nullable();
                $table->string('link_target', 50)->nullable();
                $table->string('link_image', 250)->nullable();
                $table->integer('link_parent')->nullable()->default(0);
                $table->string('page_id', 30);
                $table->string('page_id_sub', 30)->nullable();
                $table->string('status', 10)->default('Active');
            });
        }

        $createdCategoryLinks = ! Schema::hasTable('user_cat_links');
        if ($createdCategoryLinks) {
            Schema::create('user_cat_links', function (Blueprint $table) {
                $table->integer('cat_id');
                $table->integer('link_id');
                $table->primary(['cat_id', 'link_id']);
            });
        }

        if (! Schema::hasTable('usr_user_log')) {
            Schema::create('usr_user_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_id', 200);
                $table->dateTime('login_date');
                $table->timestamp('logout_date')->useCurrent()->useCurrentOnUpdate();
                $table->string('login_ip', 100);
                $table->date('date')->nullable();
            });
        }

        if ($createdCategories) {
            DB::table('user_cat')->insert([
                'cat_id' => 1,
                'cat_name' => 'System Administrator',
                'status' => 'Active',
            ]);
        }

        if ($createdLinks) {
            DB::table('user_links')->insert($this->legacyMenuLinks());
        }

        if ($createdCategoryLinks) {
            DB::table('user_cat_links')->insert(array_map(
                fn (int $linkId): array => ['cat_id' => 1, 'link_id' => $linkId],
                range(1, 7),
            ));
        }
    }

    public function down(): void
    {
        // Compatibility baseline: existing installations may predate migration
        // tracking, so rollback must never remove their legacy operational schema.
    }

    private function addLegacyUserColumns(): void
    {
        $columns = [
            'phone' => fn (Blueprint $table) => $table->string('phone')->nullable(),
            'ghana_card' => fn (Blueprint $table) => $table->string('ghana_card')->nullable(),
            'cat_id' => fn (Blueprint $table) => $table->integer('cat_id')->default(2),
            'status' => fn (Blueprint $table) => $table->string('status')->default('Active'),
            'user_cat' => fn (Blueprint $table) => $table->string('user_cat')->default('2'),
            'staff_id' => fn (Blueprint $table) => $table->string('staff_id')->nullable(),
            'reset_otp' => fn (Blueprint $table) => $table->string('reset_otp')->nullable(),
            'otp_expires_at' => fn (Blueprint $table) => $table->timestamp('otp_expires_at')->nullable(),
            'login_attempts' => fn (Blueprint $table) => $table->integer('login_attempts')->default(0),
            'locked_until' => fn (Blueprint $table) => $table->timestamp('locked_until')->nullable(),
            'photo' => fn (Blueprint $table) => $table->string('photo')->nullable(),
            'updatedby' => fn (Blueprint $table) => $table->integer('updatedby')->nullable(),
            'department_id' => fn (Blueprint $table) => $table->string('department_id')->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            if (! Schema::hasColumn('users', $name)) {
                Schema::table('users', $definition);
            }
        }
    }

    /** @return list<array<string, int|string|null>> */
    private function legacyMenuLinks(): array
    {
        return [
            ['link_id' => 1, 'link_url' => '#', 'link_name' => 'Staff Management', 'link_target' => null, 'link_image' => 'ri-user-settings-line', 'link_parent' => 0, 'page_id' => 'staff', 'page_id_sub' => 'staff', 'status' => 'Active'],
            ['link_id' => 2, 'link_url' => 'add-staff', 'link_name' => 'Add New Staff', 'link_target' => null, 'link_image' => null, 'link_parent' => 1, 'page_id' => 'staff', 'page_id_sub' => 'add-staff', 'status' => 'Active'],
            ['link_id' => 3, 'link_url' => '#', 'link_name' => 'Student Management', 'link_target' => null, 'link_image' => 'ri-graduation-cap-line', 'link_parent' => 0, 'page_id' => 'student', 'page_id_sub' => 'student', 'status' => 'Active'],
            ['link_id' => 4, 'link_url' => 'student', 'link_name' => 'Register New Student', 'link_target' => null, 'link_image' => null, 'link_parent' => 3, 'page_id' => 'student', 'page_id_sub' => 'register-student', 'status' => 'Active'],
            ['link_id' => 5, 'link_url' => 'list-staff', 'link_name' => 'Staff List', 'link_target' => null, 'link_image' => null, 'link_parent' => 1, 'page_id' => 'staff', 'page_id_sub' => 'staff-list', 'status' => 'Active'],
            ['link_id' => 6, 'link_url' => '#', 'link_name' => 'Course Setup', 'link_target' => null, 'link_image' => 'ri-book-2-line', 'link_parent' => 0, 'page_id' => 'course', 'page_id_sub' => 'course', 'status' => 'Active'],
            ['link_id' => 7, 'link_url' => 'add-course', 'link_name' => 'Add Course / Subject', 'link_target' => null, 'link_image' => null, 'link_parent' => 6, 'page_id' => 'course', 'page_id_sub' => 'add-course', 'status' => 'Active'],
        ];
    }
};
