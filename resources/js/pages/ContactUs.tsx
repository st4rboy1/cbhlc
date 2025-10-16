import { Icon } from '@/components/icon';
import { PublicNav } from '@/components/public-nav';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { Clock, Facebook, GraduationCap, Instagram, Mail, MapPin, Phone, Youtube } from 'lucide-react';

interface SchoolInformation {
    id: number;
    key: string;
    value: string | null;
    type: string;
    group: string;
    label: string;
    description: string | null;
    order: number;
}

interface GroupedInformation {
    contact?: SchoolInformation[];
    hours?: SchoolInformation[];
    social?: SchoolInformation[];
    about?: SchoolInformation[];
}

interface Props {
    schoolInformation?: GroupedInformation;
}

export default function ContactUs({ schoolInformation }: Props) {
    // Helper function to get value by key
    const getValue = (group: keyof GroupedInformation, key: string, defaultValue: string = '') => {
        const item = schoolInformation?.[group]?.find((item) => item.key === key);
        return item?.value || defaultValue;
    };

    const contactInfo = [
        {
            icon: Phone,
            title: 'Phone',
            details: [getValue('contact', 'school_phone', '+63 123 456 7890'), getValue('contact', 'school_mobile', '+63 987 654 3210')].filter(
                Boolean,
            ),
        },
        {
            icon: Mail,
            title: 'Email',
            details: [getValue('contact', 'school_email', 'christianbibleheritage@gmail.com')].filter(Boolean),
        },
        {
            icon: MapPin,
            title: 'Address',
            details: [getValue('contact', 'school_address', 'Bayabas Ext. NAPICO Manggahan 1611 Pasig, Philippines')].filter(Boolean),
        },
        {
            icon: Clock,
            title: 'Office Hours',
            details: [
                getValue('hours', 'office_hours_weekday', 'Monday to Friday: 8:00 AM - 5:00 PM'),
                getValue('hours', 'office_hours_saturday'),
                getValue('hours', 'office_hours_sunday'),
            ].filter(Boolean),
        },
    ];

    const facebookUrl = getValue('social', 'facebook_url', 'https://www.facebook.com/CBHLC.Pasig');
    const instagramUrl = getValue('social', 'instagram_url');
    const youtubeUrl = getValue('social', 'youtube_url');
    const schoolName = getValue('contact', 'school_name', 'Christian Bible Heritage Learning Center');
    const schoolDescription = getValue(
        'about',
        'school_description',
        'Providing quality Christian education that develops academic excellence, strong character, and faithful leaders.',
    );
    const schoolEmail = getValue('contact', 'school_email', 'christianbibleheritage@gmail.com');
    const schoolPhone = getValue('contact', 'school_phone', '+63 123 456 7890');
    const schoolAddress = getValue('contact', 'school_address', 'Manggahan, Pasig City');

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
                                    <CardDescription>Find us at Manggahan, Pasig City</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="aspect-video overflow-hidden rounded-lg bg-slate-200">
                                        <iframe
                                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.4438825869047!2d121.09235231484308!3d14.570845789827672!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c7f9e9f9e9e9%3A0x0!2sBayabas%20Ext.%2C%20Manggahan%2C%20Pasig%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1234567890"
                                            width="100%"
                                            height="100%"
                                            style={{ border: 0 }}
                                            allowFullScreen
                                            loading="lazy"
                                            referrerPolicy="no-referrer-when-downgrade"
                                            title="CBHLC Location Map"
                                        ></iframe>
                                    </div>
                                    <div className="mt-4">
                                        <a
                                            href="https://www.google.com/maps/search/?api=1&query=Bayabas+Ext.+NAPICO+Manggahan+1611+Pasig+Philippines"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex items-center justify-center space-x-2 rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition-colors hover:bg-blue-700"
                                        >
                                            <Icon iconNode={MapPin} className="h-4 w-4" />
                                            <span>Open in Google Maps</span>
                                        </a>
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
                                        <div className="flex flex-wrap gap-3">
                                            {facebookUrl && (
                                                <a
                                                    href={facebookUrl}
                                                    className="flex items-center space-x-2 rounded-lg bg-blue-600 px-6 py-3 text-white transition-colors hover:bg-blue-700"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <Icon iconNode={Facebook} className="h-5 w-5" />
                                                    <span className="font-medium">Facebook</span>
                                                </a>
                                            )}
                                            {instagramUrl && (
                                                <a
                                                    href={instagramUrl}
                                                    className="flex items-center space-x-2 rounded-lg bg-pink-600 px-6 py-3 text-white transition-colors hover:bg-pink-700"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <Icon iconNode={Instagram} className="h-5 w-5" />
                                                    <span className="font-medium">Instagram</span>
                                                </a>
                                            )}
                                            {youtubeUrl && (
                                                <a
                                                    href={youtubeUrl}
                                                    className="flex items-center space-x-2 rounded-lg bg-red-600 px-6 py-3 text-white transition-colors hover:bg-red-700"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    <Icon iconNode={Youtube} className="h-5 w-5" />
                                                    <span className="font-medium">YouTube</span>
                                                </a>
                                            )}
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
                                            <span className="text-lg font-semibold">{schoolPhone}</span>
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
                                <p className="mb-4 text-slate-400">{schoolName}</p>
                                <p className="text-sm text-slate-500">{schoolDescription}</p>
                            </div>

                            <div className="text-center md:text-left">
                                <h3 className="mb-4 text-lg font-semibold">Contact Us</h3>
                                <div className="space-y-3">
                                    <div className="flex items-center justify-center space-x-2 md:justify-start">
                                        <Icon iconNode={Phone} className="h-4 w-4 text-blue-400" />
                                        <span className="text-sm text-slate-400">{schoolPhone}</span>
                                    </div>
                                    <div className="flex items-center justify-center space-x-2 md:justify-start">
                                        <Icon iconNode={Mail} className="h-4 w-4 text-blue-400" />
                                        <span className="text-sm text-slate-400">{schoolEmail}</span>
                                    </div>
                                    <div className="flex items-center justify-center space-x-2 md:justify-start">
                                        <Icon iconNode={MapPin} className="h-4 w-4 text-blue-400" />
                                        <span className="text-sm text-slate-400">{schoolAddress}</span>
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
                                {facebookUrl && (
                                    <a
                                        href={facebookUrl}
                                        className="text-slate-400 transition-colors hover:text-white"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Icon iconNode={Facebook} className="h-6 w-6" />
                                    </a>
                                )}
                                {instagramUrl && (
                                    <a
                                        href={instagramUrl}
                                        className="text-slate-400 transition-colors hover:text-white"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Icon iconNode={Instagram} className="h-6 w-6" />
                                    </a>
                                )}
                                {youtubeUrl && (
                                    <a
                                        href={youtubeUrl}
                                        className="text-slate-400 transition-colors hover:text-white"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <Icon iconNode={Youtube} className="h-6 w-6" />
                                    </a>
                                )}
                            </div>
                            <p className="text-sm text-slate-500">©2025 CBHLC | All rights reserved</p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
