import { Icon } from '@/components/icon';
import { PublicNav } from '@/components/public-nav';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { Clock, Facebook, GraduationCap, Mail, MapPin, Phone } from 'lucide-react';

export default function ContactUs() {
    const contactInfo = [
        {
            icon: Phone,
            title: 'Phone',
            details: ['+63 123 456 7890', '+63 987 654 3210'],
        },
        {
            icon: Mail,
            title: 'Email',
            details: ['christianbibleheritage@gmail.com'],
        },
        {
            icon: MapPin,
            title: 'Address',
            details: ['Bayabas Ext. NAPICO Manggahan 1611 Pasig, Philippines'],
        },
        {
            icon: Clock,
            title: 'Office Hours',
            details: [],
        },
    ];

    return (
        <>
            <Head title="Contact Us" />
            <div className="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
                <PublicNav currentPage="contact" />

                {/* Header Section */}
                <section className="pt-16">
                    <div className="container mx-auto px-6 py-20">
                        <div className="text-center">
                            <h1 className="mb-4 text-4xl font-bold text-slate-800 md:text-5xl lg:text-6xl">Get In Touch</h1>
                            <p className="mx-auto max-w-2xl text-lg text-slate-600 md:text-xl">
                                Have questions about enrollment, programs, or our school? We're here to help!
                            </p>
                        </div>
                    </div>
                </section>

                {/* Contact Information Cards */}
                <section className="bg-white py-12">
                    <div className="container mx-auto px-6">
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                            {contactInfo.map((info, index) => (
                                <Card key={index} className="border-none bg-gradient-to-br from-blue-50 to-indigo-50 shadow-md">
                                    <CardHeader className="text-center">
                                        <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white">
                                            <Icon iconNode={info.icon} className="h-6 w-6" />
                                        </div>
                                        <CardTitle className="text-lg text-slate-800">{info.title}</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-1 text-center">
                                            {info.details.map((detail, idx) => (
                                                <p key={idx} className="text-sm text-slate-600">
                                                    {detail}
                                                </p>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Map & Additional Info Section */}
                <section className="py-20">
                    <div className="container mx-auto px-6">
                        <div className="grid gap-12 lg:grid-cols-2">
                            {/* Map */}
                            <Card className="shadow-lg">
                                <CardHeader>
                                    <CardTitle className="text-xl text-slate-800">Visit Our Campus</CardTitle>
                                    <CardDescription>Find us at the heart of Pasig City</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="aspect-video overflow-hidden rounded-lg bg-slate-200">
                                        <iframe
                                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123646.8!2d121.0!3d14.5!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTTCsDMwJzAwLjAiTiAxMjHCsDAwJzAwLjAiRQ!5e0!3m2!1sen!2sph!4v1234567890"
                                            width="100%"
                                            height="100%"
                                            style={{ border: 0 }}
                                            allowFullScreen
                                            loading="lazy"
                                            referrerPolicy="no-referrer-when-downgrade"
                                        ></iframe>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Social Media & Quick Info */}
                            <div className="space-y-6">
                                {/* Social Media */}
                                <Card className="shadow-lg">
                                    <CardHeader>
                                        <CardTitle className="text-xl text-slate-800">Connect With Us</CardTitle>
                                        <CardDescription>Follow us on social media for updates and news</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="flex items-center space-x-4">
                                            <a
                                                href="https://www.facebook.com/CBHLC.Pasig"
                                                className="flex items-center space-x-2 rounded-lg bg-blue-600 px-6 py-3 text-white transition-colors hover:bg-blue-700"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <Icon iconNode={Facebook} className="h-5 w-5" />
                                                <span className="font-medium">Visit Our Facebook Page</span>
                                            </a>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Quick Info */}
                                <Card className="bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg">
                                    <CardHeader>
                                        <CardTitle className="text-xl">Need Immediate Assistance?</CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="mb-4 text-blue-100">
                                            For urgent matters or enrollment inquiries, please call us directly during office hours.
                                        </p>
                                        <div className="flex items-center space-x-2">
                                            <Icon iconNode={Phone} className="h-5 w-5" />
                                            <span className="text-lg font-semibold">+63 123 456 7890</span>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Contact Methods */}
                                <Card className="shadow-lg">
                                    <CardHeader>
                                        <CardTitle className="text-xl text-slate-800">How to Reach Us</CardTitle>
                                        <CardDescription>Multiple ways to get in touch</CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <div className="flex items-start space-x-3">
                                            <Icon iconNode={Phone} className="mt-1 h-5 w-5 text-blue-600" />
                                            <div>
                                                <p className="font-medium text-slate-800">Call Us</p>
                                                <p className="text-sm text-slate-600">Available during office hours</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start space-x-3">
                                            <Icon iconNode={Mail} className="mt-1 h-5 w-5 text-blue-600" />
                                            <div>
                                                <p className="font-medium text-slate-800">Email Us</p>
                                                <p className="text-sm text-slate-600">We'll respond within 24-48 hours</p>
                                            </div>
                                        </div>
                                        <div className="flex items-start space-x-3">
                                            <Icon iconNode={MapPin} className="mt-1 h-5 w-5 text-blue-600" />
                                            <div>
                                                <p className="font-medium text-slate-800">Visit Us</p>
                                                <p className="text-sm text-slate-600">Walk-ins welcome during office hours</p>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="bg-slate-800 py-12 text-white">
                    <div className="container mx-auto px-6">
                        <div className="mb-8 grid gap-8 md:grid-cols-3">
                            <div className="text-center md:text-left">
                                <div className="mb-4 flex items-center justify-center space-x-2 md:justify-start">
                                    <Icon iconNode={GraduationCap} className="h-8 w-8" />
                                    <span className="text-2xl font-bold">CBHLC</span>
                                </div>
                                <p className="mb-4 text-slate-400">Christian Bible Heritage Learning Center</p>
                                <p className="text-sm text-slate-500">
                                    Providing quality Christian education that develops academic excellence, strong character, and faithful leaders.
                                </p>
                            </div>

                            <div className="text-center md:text-left">
                                <h3 className="mb-4 text-lg font-semibold">Contact Us</h3>
                                <div className="space-y-3">
                                    <div className="flex items-center justify-center space-x-2 md:justify-start">
                                        <Icon iconNode={Phone} className="h-4 w-4 text-blue-400" />
                                        <span className="text-sm text-slate-400">+63 123 456 7890</span>
                                    </div>
                                    <div className="flex items-center justify-center space-x-2 md:justify-start">
                                        <Icon iconNode={Mail} className="h-4 w-4 text-blue-400" />
                                        <span className="text-sm text-slate-400">info@cbhlc.edu.ph</span>
                                    </div>
                                    <div className="flex items-center justify-center space-x-2 md:justify-start">
                                        <Icon iconNode={MapPin} className="h-4 w-4 text-blue-400" />
                                        <span className="text-sm text-slate-400">Pasig City, Metro Manila</span>
                                    </div>
                                </div>
                            </div>

                            <div className="text-center md:text-left">
                                <h3 className="mb-4 text-lg font-semibold">Quick Links</h3>
                                <div className="space-y-2">
                                    <Link href="/" className="block text-sm text-slate-400 transition-colors hover:text-white">
                                        Home
                                    </Link>
                                    <Link href="/about" className="block text-sm text-slate-400 transition-colors hover:text-white">
                                        About Us
                                    </Link>
                                    <Link href="/contact" className="block text-sm text-slate-400 transition-colors hover:text-white">
                                        Contact
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div className="border-t border-slate-700 pt-8 text-center">
                            <div className="mb-6 flex justify-center space-x-6">
                                <a
                                    href="https://www.facebook.com/CBHLC.Pasig"
                                    className="text-slate-400 transition-colors hover:text-white"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <Icon iconNode={Facebook} className="h-6 w-6" />
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
