<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SharedController extends Controller
{
    public function docs()
    {
        return Inertia::render('shared/docs', [
            'sections' => [
                ['title' => 'Getting Started', 'content' => 'Introduction to the system...'],
                ['title' => 'User Guide', 'content' => 'How to use the system...'],
                ['title' => 'FAQ', 'content' => 'Frequently asked questions...'],
            ],
        ]);
    }

    public function help()
    {
        return Inertia::render('shared/help', [
            'topics' => [
                ['id' => 1, 'title' => 'Account Setup', 'category' => 'Getting Started'],
                ['id' => 2, 'title' => 'Enrollment Process', 'category' => 'Enrollment'],
                ['id' => 3, 'title' => 'Document Upload', 'category' => 'Documents'],
                ['id' => 4, 'title' => 'Billing Information', 'category' => 'Billing'],
            ],
            'faqs' => [
                ['question' => 'How do I enroll?', 'answer' => 'Follow the enrollment process...'],
                ['question' => 'What documents are required?', 'answer' => 'Birth certificate, report cards...'],
            ],
        ]);
    }

    public function support()
    {
        return Inertia::render('shared/support', [
            'contact' => [
                'email' => 'support@cbhlc.edu',
                'phone' => '(123) 456-7890',
                'hours' => 'Monday-Friday, 8:00 AM - 5:00 PM',
            ],
            'tickets' => [
                ['id' => 1, 'subject' => 'Sample ticket', 'status' => 'open', 'created_at' => '2025-01-20'],
            ],
        ]);
    }

    public function resources()
    {
        return Inertia::render('shared/resources', [
            'categories' => [
                ['id' => 1, 'name' => 'Study Materials', 'count' => 25],
                ['id' => 2, 'name' => 'Video Tutorials', 'count' => 15],
                ['id' => 3, 'name' => 'Practice Tests', 'count' => 10],
            ],
            'resources' => [
                ['id' => 1, 'title' => 'Math Basics', 'type' => 'PDF', 'category' => 'Study Materials'],
                ['id' => 2, 'title' => 'Science Introduction', 'type' => 'Video', 'category' => 'Video Tutorials'],
            ],
        ]);
    }

    public function parentGuide()
    {
        return Inertia::render('shared/parent-guide', [
            'sections' => [
                ['title' => 'Welcome', 'content' => 'Welcome to CBHLC parent guide...'],
                ['title' => 'School Policies', 'content' => 'Important school policies...'],
                ['title' => 'Academic Calendar', 'content' => 'School year calendar...'],
                ['title' => 'Communication', 'content' => 'How to communicate with school...'],
            ],
            'quickLinks' => [
                ['title' => 'Enrollment Guide', 'url' => '/enrollments'],
                ['title' => 'Tuition Information', 'url' => '/tuition'],
                ['title' => 'Contact Us', 'url' => '/support'],
            ],
        ]);
    }
}
