import { Icon } from '@/components/icon';
import { LoginDialog } from '@/components/login-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { BookOpen, CheckCircle, Facebook, GraduationCap, Heart, Instagram, Users } from 'lucide-react';

export default function Landing() {
    const { auth } = usePage<SharedData>().props;

    const features = [
        {
            icon: BookOpen,
            title: 'Quality Education',
            description: 'Comprehensive academic programs rooted in Christian values and modern teaching methodologies.',
        },
        {
            icon: Heart,
            title: 'Character Building',
            description: 'Nurturing students to develop strong moral character and leadership skills through faith-based education.',
        },
        {
            icon: Users,
            title: 'Community Focus',
            description: 'Building a supportive community where every student feels valued and encouraged to reach their potential.',
        },
        {
            icon: GraduationCap,
            title: 'Holistic Development',
            description: 'Fostering intellectual, spiritual, social, and emotional growth in every aspect of student life.',
        },
    ];

    const achievements = [
        'Excellence in Christian Education',
        'Strong Community Partnership',
        'Dedicated Faculty & Staff',
        'Student-Centered Approach',
    ];

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
                {/* Navigation Header */}
                <header className="fixed top-0 left-0 z-50 w-full border-b bg-white/80 backdrop-blur-md">
                    <div className="container mx-auto flex h-16 items-center justify-between px-6">
                        <Link href="/" className="flex items-center space-x-2 text-xl font-bold text-slate-800 transition-colors hover:text-blue-600">
                            <Icon iconNode={GraduationCap} className="h-6 w-6" />
                            <span>CBHLC</span>
                        </Link>
                        <nav className="flex items-center space-x-8">
                            <Link href="/about" className="font-medium text-slate-600 transition-colors hover:text-slate-800">
                                About
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
                                Christian Bible Heritage Learning Center
                            </Badge>
                            <h1 className="mb-6 text-4xl leading-tight font-bold text-slate-800 md:text-6xl lg:text-7xl">
                                Nurturing Hearts,
                                <br />
                                <span className="text-blue-600">Shaping Futures</span>
                            </h1>
                            <p className="mx-auto mb-8 max-w-2xl text-lg text-slate-600 md:text-xl">
                                Providing quality Christian education that develops academic excellence, strong character, and faithful leaders for
                                tomorrow.
                            </p>
                            <div className="flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <Button size="lg" className="px-8 py-3">
                                    Start Enrollment
                                </Button>
                                <Button variant="outline" size="lg" className="px-8 py-3" asChild>
                                    <Link href="/about">Learn More</Link>
                                </Button>
                            </div>
                        </div>

                        {/* Hero Image */}
                        <div className="mt-16">
                            <div className="overflow-hidden rounded-2xl border bg-white p-2 shadow-2xl">
                                <img src="landingp.png" alt="CBHLC Campus" className="h-[400px] w-full rounded-xl object-cover md:h-[600px]" />
                            </div>
                        </div>

                        {/* Bible Verse */}
                        <div className="mt-16 text-center">
                            <blockquote className="text-2xl font-semibold text-slate-700 md:text-3xl">
                                "Start children off on the way they should go..."
                            </blockquote>
                            <cite className="mt-2 block text-lg text-slate-500">— Proverbs 22:6</cite>
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="bg-white py-20">
                    <div className="container mx-auto px-6">
                        <div className="text-center">
                            <h2 className="mb-4 text-3xl font-bold text-slate-800 md:text-4xl">Why Choose CBHLC?</h2>
                            <p className="mx-auto mb-12 max-w-2xl text-lg text-slate-600">
                                Discover what makes our Christian education unique and transformative for every student.
                            </p>
                        </div>

                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                            {features.map((feature, index) => (
                                <Card
                                    key={index}
                                    className="border-none bg-gradient-to-br from-blue-50 to-indigo-50 shadow-md transition-shadow hover:shadow-lg"
                                >
                                    <CardHeader className="text-center">
                                        <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white">
                                            <Icon iconNode={feature.icon} className="h-6 w-6" />
                                        </div>
                                        <CardTitle className="text-slate-800">{feature.title}</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <CardDescription className="text-center text-slate-600">{feature.description}</CardDescription>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Achievements Section */}
                <section className="bg-slate-50 py-20">
                    <div className="container mx-auto px-6">
                        <div className="text-center">
                            <h2 className="mb-4 text-3xl font-bold text-slate-800 md:text-4xl">Our Commitment to Excellence</h2>
                            <p className="mx-auto mb-12 max-w-2xl text-lg text-slate-600">
                                Building on years of dedication to Christian education and student success.
                            </p>
                        </div>

                        <div className="mx-auto max-w-4xl">
                            <div className="grid gap-6 md:grid-cols-2">
                                {achievements.map((achievement, index) => (
                                    <div key={index} className="flex items-center space-x-3">
                                        <Icon iconNode={CheckCircle} className="h-6 w-6 text-green-600" />
                                        <span className="text-lg text-slate-700">{achievement}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                {/* Call to Action Section */}
                <section className="bg-gradient-to-r from-blue-600 to-indigo-600 py-20 text-white">
                    <div className="container mx-auto px-6 text-center">
                        <div className="mx-auto max-w-3xl">
                            <h2 className="mb-4 text-3xl font-bold md:text-4xl">Ready to Begin Your Journey?</h2>
                            <p className="mb-8 text-lg text-blue-100">
                                Join our community of learners and discover the difference Christian education makes. Start your enrollment process
                                today.
                            </p>
                            <div className="flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <Button size="lg" variant="secondary" className="px-8 py-3">
                                    Enroll Now
                                </Button>
                                <Button size="lg" variant="outline" className="border-white px-8 py-3 text-white hover:bg-white hover:text-blue-600">
                                    Contact Us
                                </Button>
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
                            <p className="mb-6 text-slate-400">Christian Bible Heritage Learning Center</p>
                            <div className="mb-6 flex justify-center space-x-6">
                                <a
                                    href="https://www.facebook.com/CBHLC.Pasig"
                                    className="text-slate-400 transition-colors hover:text-white"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <Icon iconNode={Facebook} className="h-6 w-6" />
                                </a>
                                <a
                                    href="https://www.instagram.com/awrabriguela/"
                                    className="text-slate-400 transition-colors hover:text-white"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <Icon iconNode={Instagram} className="h-6 w-6" />
                                </a>
                            </div>
                            <p className="text-sm text-slate-500">©2025 CBHLC | All rights reserved</p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
