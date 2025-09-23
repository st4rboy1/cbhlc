<?php

namespace App\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;

interface DashboardServiceInterface
{
    /**
     * Get dashboard data for guardian
     */
    public function getGuardianDashboardData(int $guardianId): array;

    /**
     * Get dashboard data for registrar
     */
    public function getRegistrarDashboardData(): array;

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStatistics(array $filters = []): array;

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 10): Collection;

    /**
     * Get pending tasks
     */
    public function getPendingTasks(string $role): Collection;

    /**
     * Get system announcements
     */
    public function getAnnouncements(bool $activeOnly = true): Collection;

    /**
     * Get quick stats
     */
    public function getQuickStats(): array;

    /**
     * Get enrollment trends
     */
    public function getEnrollmentTrends(string $period = 'monthly'): array;

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(): array;

    /**
     * Get grade level distribution
     */
    public function getGradeLevelDistribution(): array;
}
