import { Icon } from '@/components/icon';
import { LoginDialog } from '@/components/login-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { dashboard } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Award, BookOpen, Eye, Facebook, GraduationCap, Heart, Instagram, Mail, MapPin, Phone, Star, Target, Users } from 'lucide-react';

export default function About() {
    const { auth } = usePage<SharedData>().props;

    const values = [
        {
            icon: Heart,
            title: 'Faith-Centered',
            description:
                'Building strong spiritual foundations through daily devotions, prayer, and biblical teachings integrated into all aspects of learning.',
        },
        {
            icon: BookOpen,
            title: 'Academic Excellence',
            description:
                'Providing high-quality education with innovative teaching methods and comprehensive curriculum that prepares students for future success.',
        },
        {
            icon: Users,
            title: 'Community Focus',
            description: 'Creating a supportive environment where students, families, and teachers work together as a unified Christian community.',
        },
        {
            icon: Target,
            title: 'Character Development',
            description: 'Nurturing integrity, responsibility, and moral values that guide students to become ethical leaders in their communities.',
        },
    ];

    const achievements = [
        {
            icon: Award,
            title: 'Excellence in Education',
            description: 'Consistently delivering quality Christian education with proven academic results.',
        },
        {
            icon: Users,
            title: 'Strong Community',
            description: 'Building lasting relationships between students, families, and educators.',
        },
        {
            icon: Star,
            title: 'Student Success',
            description: 'Preparing graduates who excel academically and demonstrate strong Christian values.',
        },
    ];

    return (
        <>
            <Head title="About Us" />
            <div className="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
                {/* Navigation Header */}
                <header className="fixed top-0 left-0 z-50 w-full border-b bg-white/80 backdrop-blur-md">
                    <div className="container mx-auto flex h-16 items-center justify-between px-6">
                        <Link href="/" className="flex items-center space-x-2 text-xl font-bold text-slate-800 transition-colors hover:text-blue-600">
                            <Icon iconNode={GraduationCap} className="h-6 w-6" />
                            <span>CBHLC</span>
                        </Link>
                        <nav className="flex items-center space-x-8">
                            <Link href="/about" className="font-medium text-blue-600">
                                About
                            </Link>
                            <Link href="/" className="font-medium text-slate-600 transition-colors hover:text-slate-800">
                                Home
                            </Link>
                            {auth.user ? (
                                <Button asChild variant="default">
                                    <Link href={dashboard()}>Dashboard</Link>
                                </Button>
                            ) : (
                                <LoginDialog trigger={<Button variant="outline">Login</Button>} />
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero Section */}
                <section className="pt-16">
                    <div className="container mx-auto px-6 py-20">
                        <div className="text-center">
                            <Badge variant="secondary" className="mb-6">
                                About CBHLC
                            </Badge>
                            <h1 className="mb-6 text-4xl leading-tight font-bold text-slate-800 md:text-5xl">
                                Christian Bible Heritage
                                <br />
                                <span className="text-blue-600">Learning Center</span>
                            </h1>
                            <p className="mx-auto mb-8 max-w-3xl text-lg text-slate-600 md:text-xl">
                                Dedicated to nurturing children in both academics and faith, helping them grow into individuals with strong character
                                and a brighter future rooted in Christian values.
                            </p>
                        </div>

                        {/* Hero Images */}
                        <div className="mt-16 grid gap-6 md:grid-cols-2">
                            <div className="overflow-hidden rounded-2xl border bg-white p-2 shadow-lg">
                                <img src="landing.png" alt="CBHLC Campus" className="h-[300px] w-full rounded-xl object-cover" />
                            </div>
                            <div className="overflow-hidden rounded-2xl border bg-white p-2 shadow-lg">
                                <img src="landing.png" alt="CBHLC Students" className="h-[300px] w-full rounded-xl object-cover" />
                            </div>
                        </div>
                    </div>
                </section>

                {/* Mission & Vision Section */}
                <section className="bg-white py-20">
                    <div className="container mx-auto px-6">
                        <div className="grid gap-12 lg:grid-cols-2">
                            {/* Mission */}
                            <Card className="border-none bg-gradient-to-br from-blue-50 to-indigo-50 shadow-lg">
                                <CardHeader>
                                    <div className="flex items-center space-x-3">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white">
                                            <Icon iconNode={Target} className="h-6 w-6" />
                                        </div>
                                        <CardTitle className="text-2xl text-slate-800">Our Mission</CardTitle>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <CardDescription className="text-base text-slate-600">
                                        To provide quality education rooted in Christian values, emphasizing both academic excellence and spiritual
                                        development. We ensure that each child receives holistic growth through engaging programs, activities, and a
                                        nurturing environment that supports learners in achieving success while living by faith.
                                    </CardDescription>
                                </CardContent>
                            </Card>

                            {/* Vision */}
                            <Card className="border-none bg-gradient-to-br from-green-50 to-emerald-50 shadow-lg">
                                <CardHeader>
                                    <div className="flex items-center space-x-3">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-green-600 text-white">
                                            <Icon iconNode={Eye} className="h-6 w-6" />
                                        </div>
                                        <CardTitle className="text-2xl text-slate-800">Our Vision</CardTitle>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <CardDescription className="text-base text-slate-600">
                                        To be a leading Christian educational institution that shapes future leaders with strong moral character,
                                        academic excellence, and unwavering faith. We envision graduates who make positive contributions to society
                                        while upholding biblical principles in all aspects of their lives.
                                    </CardDescription>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Bible Verse */}
                        <div className="mt-16 text-center">
                            <Card className="mx-auto max-w-3xl border-none bg-gradient-to-r from-amber-50 to-yellow-50 shadow-lg">
                                <CardContent className="py-12">
                                    <blockquote className="text-2xl font-semibold text-slate-700 md:text-3xl">
                                        "Start children off on the way they should go..."
                                    </blockquote>
                                    <cite className="mt-4 block text-lg text-slate-500">— Proverbs 22:6</cite>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </section>

                {/* Core Values Section */}
                <section className="bg-slate-50 py-20">
                    <div className="container mx-auto px-6">
                        <div className="text-center">
                            <h2 className="mb-4 text-3xl font-bold text-slate-800 md:text-4xl">Our Core Values</h2>
                            <p className="mx-auto mb-12 max-w-2xl text-lg text-slate-600">
                                The fundamental principles that guide our educational approach and community life.
                            </p>
                        </div>

                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                            {values.map((value, index) => (
                                <Card key={index} className="border-none bg-white shadow-md transition-shadow hover:shadow-lg">
                                    <CardHeader className="text-center">
                                        <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white">
                                            <Icon iconNode={value.icon} className="h-7 w-7" />
                                        </div>
                                        <CardTitle className="text-slate-800">{value.title}</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <CardDescription className="text-center text-slate-600">{value.description}</CardDescription>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Achievements Section */}
                <section className="bg-white py-20">
                    <div className="container mx-auto px-6">
                        <div className="text-center">
                            <h2 className="mb-4 text-3xl font-bold text-slate-800 md:text-4xl">Our Achievements</h2>
                            <p className="mx-auto mb-12 max-w-2xl text-lg text-slate-600">
                                Building on years of dedication to Christian education and community impact.
                            </p>
                        </div>

                        <div className="grid gap-8 md:grid-cols-3">
                            {achievements.map((achievement, index) => (
                                <Card
                                    key={index}
                                    className="border-none bg-gradient-to-br from-purple-50 to-pink-50 shadow-md transition-shadow hover:shadow-lg"
                                >
                                    <CardHeader className="text-center">
                                        <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-purple-600 text-white">
                                            <Icon iconNode={achievement.icon} className="h-6 w-6" />
                                        </div>
                                        <CardTitle className="text-slate-800">{achievement.title}</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <CardDescription className="text-center text-slate-600">{achievement.description}</CardDescription>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Contact Information Section */}
                <section className="bg-gradient-to-r from-blue-600 to-indigo-600 py-20 text-white">
                    <div className="container mx-auto px-6">
                        <div className="text-center">
                            <h2 className="mb-4 text-3xl font-bold md:text-4xl">Get In Touch</h2>
                            <p className="mx-auto mb-12 max-w-2xl text-lg text-blue-100">
                                We'd love to hear from you. Contact us to learn more about our programs and enrollment process.
                            </p>
                        </div>

                        <div className="mx-auto max-w-4xl">
                            <div className="grid gap-8 md:grid-cols-3">
                                <div className="text-center">
                                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                                        <Icon iconNode={MapPin} className="h-6 w-6" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold">Address</h3>
                                    <p className="text-blue-100">
                                        Pasig City, Metro Manila
                                        <br />
                                        Philippines
                                    </p>
                                </div>

                                <div className="text-center">
                                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                                        <Icon iconNode={Phone} className="h-6 w-6" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold">Phone</h3>
                                    <p className="text-blue-100">
                                        Contact us for inquiries
                                        <br />
                                        Mon - Fri: 8:00 AM - 5:00 PM
                                    </p>
                                </div>

                                <div className="text-center">
                                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white/20">
                                        <Icon iconNode={Mail} className="h-6 w-6" />
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold">Email</h3>
                                    <p className="text-blue-100">
                                        admissions@cbhlc.edu.ph
                                        <br />
                                        info@cbhlc.edu.ph
                                    </p>
                                </div>
                            </div>

                            <Separator className="my-12 bg-white/20" />

                            <div className="text-center">
                                <h3 className="mb-6 text-xl font-semibold">Follow Us</h3>
                                <div className="flex justify-center space-x-6">
                                    <a
                                        href="https://www.facebook.com/CBHLC.Pasig"
                                        className="flex h-12 w-12 items-center justify-center rounded-full bg-white/20 text-white transition-colors hover:bg-white hover:text-blue-600"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Icon iconNode={Facebook} className="h-6 w-6" />
                                    </a>
                                    <a
                                        href="https://www.instagram.com/awrabriguela/"
                                        className="flex h-12 w-12 items-center justify-center rounded-full bg-white/20 text-white transition-colors hover:bg-white hover:text-blue-600"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Icon iconNode={Instagram} className="h-6 w-6" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="bg-slate-800 py-12 text-white">
                    <div className="container mx-auto px-6">
                        <div className="text-center">
                            <div className="mb-6 flex items-center justify-center space-x-2">
                                <Icon iconNode={GraduationCap} className="h-8 w-8" />
                                <span className="text-2xl font-bold">CBHLC</span>
                            </div>
                            <p className="mb-4 text-slate-400">Christian Bible Heritage Learning Center</p>
                            <p className="text-sm text-slate-500">©2025 CBHLC | All rights reserved</p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
