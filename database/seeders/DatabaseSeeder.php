<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create Teacher Users
        $teachers = [
            [
                'name' => 'Dr. Ahmed Mohamed',
                'email' => 'ahmed@university.edu',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Prof. Sarah Johnson',
                'email' => 'sarah@university.edu',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Mr. John Smith',
                'email' => 'john@university.edu',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
            ],
        ];

        foreach ($teachers as $teacherData) {
            User::create($teacherData);
        }

        // Create Student Users
        $students = [
            [
                'name' => 'Mohamed Ali',
                'email' => 'mohamed@student.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ],
            [
                'name' => 'Fatima Hassan',
                'email' => 'fatima@student.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ],
            [
                'name' => 'Ahmed Khalid',
                'email' => 'ahmed@student.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah@student.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike@student.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ],
        ];

        foreach ($students as $studentData) {
            User::create($studentData);
        }

        // Create Courses
        $courses = [
            [
                'title' => 'Introduction to Programming',
                'description' => 'Learn the fundamentals of programming with Python',
                'teacher_id' => 2, // Dr. Ahmed
                'price' => 99.99,
            ],
            [
                'title' => 'Web Development Bootcamp',
                'description' => 'Complete guide to modern web development',
                'teacher_id' => 3, // Prof. Sarah
                'price' => 149.99,
            ],
            [
                'title' => 'Data Science Fundamentals',
                'description' => 'Introduction to data analysis and machine learning',
                'teacher_id' => 4, // Mr. John
                'price' => 199.99,
            ],
            [
                'title' => 'Mobile App Development',
                'description' => 'Build mobile apps for Android and iOS',
                'teacher_id' => 2, // Dr. Ahmed
                'price' => 129.99,
            ],
        ];

        foreach ($courses as $courseData) {
            Course::create($courseData);
        }

        // Create Lessons for each course
        $lessons = [
            // Course 1: Introduction to Programming
            [
                'course_id' => 1,
                'title' => 'Getting Started with Python',
                'video_url' => 'https://example.com/python-intro',
            ],
            [
                'course_id' => 1,
                'title' => 'Variables and Data Types',
                'video_url' => 'https://example.com/variables',
            ],
            [
                'course_id' => 1,
                'title' => 'Control Structures',
                'video_url' => 'https://example.com/control-structures',
            ],

            // Course 2: Web Development
            [
                'course_id' => 2,
                'title' => 'HTML Basics',
                'video_url' => 'https://example.com/html-basics',
            ],
            [
                'course_id' => 2,
                'title' => 'CSS Styling',
                'video_url' => 'https://example.com/css-styling',
            ],
            [
                'course_id' => 2,
                'title' => 'JavaScript Fundamentals',
                'video_url' => 'https://example.com/js-fundamentals',
            ],

            // Course 3: Data Science
            [
                'course_id' => 3,
                'title' => 'Introduction to Data Analysis',
                'video_url' => 'https://example.com/data-analysis',
            ],
            [
                'course_id' => 3,
                'title' => 'Statistics for Data Science',
                'video_url' => 'https://example.com/statistics',
            ],
        ];

        foreach ($lessons as $lessonData) {
            Lesson::create($lessonData);
        }

        // Create Orders
        $orders = [
            [
                'user_id' => 5, // Mohamed Ali
                'course_id' => 1,
                'price' => 99.99,
                'payment_status' => 'paid',
            ],
            [
                'user_id' => 6, // Fatima Hassan
                'course_id' => 1,
                'price' => 99.99,
                'payment_status' => 'paid',
            ],
            [
                'user_id' => 7, // Ahmed Khalid
                'course_id' => 2,
                'price' => 149.99,
                'payment_status' => 'paid',
            ],
            [
                'user_id' => 8, // Sarah Wilson
                'course_id' => 3,
                'price' => 199.99,
                'payment_status' => 'pending',
            ],
            [
                'user_id' => 9, // Mike Johnson
                'course_id' => 2,
                'price' => 149.99,
                'payment_status' => 'paid',
            ],
        ];

        foreach ($orders as $orderData) {
            Order::create($orderData);
        }

        $this->command->info('Database seeded successfully with test data!');
    }
}
