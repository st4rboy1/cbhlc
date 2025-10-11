<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalFilename = fake()->word().'.'.fake()->randomElement(['pdf', 'jpg', 'png']);

        return [
            'student_id' => Student::factory(),
            'document_type' => fake()->randomElement(DocumentType::cases()),
            'original_filename' => $originalFilename,
            'stored_filename' => fake()->uuid().'_'.$originalFilename,
            'file_path' => 'documents/'.fake()->uuid().'/'.fake()->uuid().'_'.$originalFilename,
            'file_size' => fake()->numberBetween(1024, 50 * 1024 * 1024), // 1KB to 50MB
            'mime_type' => fake()->randomElement(['application/pdf', 'image/jpeg', 'image/png']),
            'upload_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'verification_status' => VerificationStatus::PENDING,
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the document is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VerificationStatus::VERIFIED,
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the document is rejected.
     */
    public function rejected(string $reason = 'Document is not clear or incomplete'): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VerificationStatus::REJECTED,
            'verified_by' => User::factory(),
            'verified_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Indicate that the document is pending verification.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VerificationStatus::PENDING,
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate the document type is birth certificate.
     */
    public function birthCertificate(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => DocumentType::BIRTH_CERTIFICATE,
        ]);
    }

    /**
     * Indicate the document type is report card.
     */
    public function reportCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => DocumentType::REPORT_CARD,
        ]);
    }

    /**
     * Indicate the document type is Form 138.
     */
    public function form138(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => DocumentType::FORM_138,
        ]);
    }

    /**
     * Indicate the document type is good moral certificate.
     */
    public function goodMoral(): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type' => DocumentType::GOOD_MORAL,
        ]);
    }
}
