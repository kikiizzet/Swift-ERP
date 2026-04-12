<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    $employee = Employee::create([
        'name' => 'Verification Test ' . time(),
        'join_date' => date('Y-m-d'),
        'status' => 'active'
    ]);
    echo "SUCCESS: Created employee with number: " . $employee->employee_number . "\n";
    DB::rollBack();
    echo "Test rolled back successfully.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    DB::rollBack();
}
