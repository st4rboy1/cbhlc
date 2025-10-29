<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewStudentCreatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Student $student
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Student Added to System')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new student has been added to the system.')
            ->line('Student Details:')
            ->line('Student ID: '.$this->student->student_id)
            ->line('Name: '.$this->student->full_name)
            ->line('Grade Level: '.($this->student->grade_level instanceof \BackedEnum ? $this->student->grade_level->value : $this->student->grade_level))
            ->line('Email: '.$this->student->email)
            ->line('Added on: '.$this->student->created_at->format('F d, Y h:i A'))
            ->action('View Student', route('registrar.students.show', $this->student))
            ->line('Please review this new student record.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'student_number' => $this->student->student_id,
            'student_name' => $this->student->full_name,
            'grade_level' => $this->student->grade_level instanceof \BackedEnum ? $this->student->grade_level->value : $this->student->grade_level,
            'email' => $this->student->email,
            'created_at' => $this->student->created_at,
            'message' => 'New student '.$this->student->full_name.' has been added',
        ];
    }
}
