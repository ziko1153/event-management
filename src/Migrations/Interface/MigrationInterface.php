<?php

namespace App\Migrations\Interface;

interface MigrationInterface
{
    public function up(): void;
    public function down(): void;
}
