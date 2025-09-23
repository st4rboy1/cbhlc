<?php

use App\Models\Student;
use App\Services\BaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

// Create a concrete implementation for testing
class TestService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new Student);
    }

    // Expose protected method for testing
    public function testApplyFilters($query, array $filters): void
    {
        $this->applyFilters($query, $filters);
    }
}

beforeEach(function () {
    $this->service = new TestService;
});

test('paginate returns paginated results', function () {
    // Create test data
    Student::factory()->count(25)->create();

    $result = $this->service->paginate([], 10);

    expect($result)->toBeInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class);
    expect($result->count())->toBe(10);
    expect($result->total())->toBe(25);
});

test('paginate applies filters correctly', function () {
    // Create test data
    Student::factory()->create(['grade_level' => 'Grade 1']);
    Student::factory()->create(['grade_level' => 'Grade 2']);
    Student::factory()->create(['grade_level' => 'Grade 3']);

    $result = $this->service->paginate(['grade_level' => 'Grade 1'], 10);

    expect($result->count())->toBe(1);
    expect($result->first()->grade_level->value)->toBe('Grade 1');
});

test('paginate applies array filters with whereIn', function () {
    // Create test data
    Student::factory()->create(['grade_level' => 'Grade 1']);
    Student::factory()->create(['grade_level' => 'Grade 2']);
    Student::factory()->create(['grade_level' => 'Grade 3']);

    $result = $this->service->paginate(['grade_level' => ['Grade 1', 'Grade 2']], 10);

    expect($result->count())->toBe(2);
});

test('find returns model by id', function () {
    $student = Student::factory()->create();

    $result = $this->service->find($student->id);

    expect($result)->toBeInstanceOf(Student::class);
    expect($result->id)->toBe($student->id);
});

test('find throws exception for non-existent id', function () {
    $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

    $this->service->find(999999);
});

test('create creates new record', function () {
    $data = [
        'student_id' => 'STU001',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birthdate' => '2010-01-01',
        'gender' => 'Male',
        'grade_level' => 'Grade 1',
        'address' => '123 Test Street',
    ];

    $result = $this->service->create($data);

    expect($result)->toBeInstanceOf(Student::class);
    expect($result->first_name)->toBe('John');
    expect($result->last_name)->toBe('Doe');
    $this->assertDatabaseHas('students', ['student_id' => 'STU001']);
});

test('create uses database transaction', function () {
    $data = [
        'student_id' => 'STU002',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'birthdate' => '2010-01-01',
        'gender' => 'Female',
        'grade_level' => 'Grade 2',
        'address' => '456 Test Avenue',
    ];

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->service->create($data);
});

test('update modifies existing record', function () {
    $student = Student::factory()->create([
        'first_name' => 'Original',
    ]);

    $result = $this->service->update($student->id, [
        'first_name' => 'Updated',
    ]);

    expect($result)->toBeInstanceOf(Student::class);
    expect($result->first_name)->toBe('Updated');
    $this->assertDatabaseHas('students', [
        'id' => $student->id,
        'first_name' => 'Updated',
    ]);
});

test('update returns fresh model instance', function () {
    $student = Student::factory()->create();

    $result = $this->service->update($student->id, [
        'first_name' => 'NewName',
    ]);

    expect($result->first_name)->toBe('NewName');
    expect($result)->toBeInstanceOf(Student::class);
});

test('delete removes record', function () {
    $student = Student::factory()->create();

    $result = $this->service->delete($student->id);

    expect($result)->toBe(true);
    $this->assertDatabaseMissing('students', ['id' => $student->id]);
});

test('delete uses database transaction', function () {
    $student = Student::factory()->create();

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->service->delete($student->id);
});

test('applyFilters ignores null and empty values', function () {
    Student::factory()->count(3)->create();

    $query = Student::query();
    $this->service->testApplyFilters($query, [
        'field1' => null,
        'field2' => '',
        'field3' => 'value',
    ]);

    // The query should only have one where clause
    $bindings = $query->getQuery()->wheres;
    expect(count($bindings))->toBe(1);
    expect($bindings[0]['column'])->toBe('field3');
});

test('logActivity logs to Laravel log', function () {
    Log::spy();

    $this->service = new TestService;

    // Use reflection to access protected method
    $reflection = new ReflectionClass($this->service);
    $method = $reflection->getMethod('logActivity');
    $method->setAccessible(true);

    $method->invoke($this->service, 'test_action', ['data' => 'test']);

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Service action: test_action', \Mockery::any());
});
