<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rentals')) {
            return;
        }

        Schema::table('rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rentals', 'penalty_late_fee')) {
                $table->unsignedBigInteger('penalty_late_fee')->default(0)->after('deposit_paid');
            }
            if (!Schema::hasColumn('rentals', 'penalty_cleaning_fee')) {
                $table->unsignedBigInteger('penalty_cleaning_fee')->default(0)->after('penalty_late_fee');
            }
            if (!Schema::hasColumn('rentals', 'penalty_damage_fee')) {
                $table->unsignedBigInteger('penalty_damage_fee')->default(0)->after('penalty_cleaning_fee');
            }
            if (!Schema::hasColumn('rentals', 'penalty_total_due')) {
                $table->unsignedBigInteger('penalty_total_due')->default(0)->after('penalty_damage_fee');
            }
            if (!Schema::hasColumn('rentals', 'penalty_paid')) {
                $table->unsignedBigInteger('penalty_paid')->default(0)->after('penalty_total_due');
            }
            if (!Schema::hasColumn('rentals', 'penalty_status')) {
                $table->enum('penalty_status', ['NONE','DUE','PAID'])->default('NONE')->after('penalty_paid');
            }
            if (!Schema::hasColumn('rentals', 'penalty_notes')) {
                $table->text('penalty_notes')->nullable()->after('penalty_status');
            }
        });

        if (Schema::hasColumn('rentals', 'status')) {
            DB::statement("ALTER TABLE rentals MODIFY COLUMN status ENUM('PENDING_PAYMENT','ACTIVE','RETURN_REQUESTED','AWAITING_PENALTY','RETURNED','PURCHASED','CANCELLED') NOT NULL DEFAULT 'PENDING_PAYMENT'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('rentals')) {
            return;
        }

        if (Schema::hasColumn('rentals', 'status')) {
            DB::statement("ALTER TABLE rentals MODIFY COLUMN status ENUM('PENDING_PAYMENT','ACTIVE','RETURN_REQUESTED','RETURNED','PURCHASED','CANCELLED') NOT NULL DEFAULT 'PENDING_PAYMENT'");
        }

        Schema::table('rentals', function (Blueprint $table) {
            foreach ([
                'penalty_notes',
                'penalty_status',
                'penalty_paid',
                'penalty_total_due',
                'penalty_damage_fee',
                'penalty_cleaning_fee',
                'penalty_late_fee',
            ] as $column) {
                if (Schema::hasColumn('rentals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
