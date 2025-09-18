import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Head } from '@inertiajs/react';
import { ChevronRight, Cookie, HelpCircle, LogOut, MessageSquare, Palette, Settings, Shield, User, Users } from 'lucide-react';
import PageLayout from '../components/PageLayout';

interface ProfileOption {
    icon: React.ReactNode;
    title: string;
    description?: string;
    hasArrow?: boolean;
    action?: () => void;
    variant?: 'default' | 'destructive';
}

export default function ProfileSettings() {
    const profileOptions: ProfileOption[] = [
        {
            icon: <User className="h-5 w-5" />,
            title: 'Profile',
            description: 'Manage your personal information',
        },
        {
            icon: <Users className="h-5 w-5" />,
            title: 'See all profiles',
            description: 'View family member profiles',
        },
        {
            icon: <Settings className="h-5 w-5" />,
            title: 'Settings & privacy',
            description: 'Account settings and privacy controls',
            hasArrow: true,
        },
        {
            icon: <HelpCircle className="h-5 w-5" />,
            title: 'Help & support',
            description: 'Get assistance and contact support',
            hasArrow: true,
        },
        {
            icon: <Palette className="h-5 w-5" />,
            title: 'Display & accessibility',
            description: 'Customize appearance and accessibility',
            hasArrow: true,
        },
        {
            icon: <MessageSquare className="h-5 w-5" />,
            title: 'Give feedback',
            description: 'Share your thoughts with us',
        },
    ];

    const quickLinks = [
        { title: 'Privacy', href: '#' },
        { title: 'Terms', href: '#' },
        { title: 'Advertising', href: '#' },
        { title: 'Ad Choices', href: '#' },
        { title: 'Cookies', href: '#' },
        { title: 'More', href: '#' },
    ];

    return (
        <>
            <Head title="Profile Settings" />
            <PageLayout title="PROFILE SETTINGS" currentPage="profilesettings">
                <div className="mx-auto max-w-2xl">
                    {/* Profile Header */}
                    <Card className="mb-6">
                        <CardContent className="p-6">
                            <div className="flex items-center gap-4">
                                <Avatar className="h-16 w-16">
                                    <AvatarImage src="ra_2022-06-19_22-17-45.jpg" alt="Profile Avatar" />
                                    <AvatarFallback className="bg-primary/10 text-lg font-semibold text-primary">BR</AvatarFallback>
                                </Avatar>
                                <div className="flex-1">
                                    <h2 className="text-xl font-semibold">Bronny</h2>
                                    <p className="text-muted-foreground">View profile</p>
                                </div>
                                <Button variant="outline" size="sm">
                                    Edit Profile
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Profile Options */}
                    <Card className="mb-6">
                        <CardHeader>
                            <CardTitle className="text-lg">Account Management</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="divide-y">
                                {profileOptions.map((option, index) => (
                                    <Button key={index} variant="ghost" className="h-auto w-full justify-start rounded-none p-4 hover:bg-muted/50">
                                        <div className="flex flex-1 items-center gap-3">
                                            <div className="text-muted-foreground">{option.icon}</div>
                                            <div className="flex-1 text-left">
                                                <p className="font-medium">{option.title}</p>
                                                {option.description && <p className="text-sm text-muted-foreground">{option.description}</p>}
                                            </div>
                                            {option.hasArrow && <ChevronRight className="h-4 w-4 text-muted-foreground" />}
                                        </div>
                                    </Button>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Account Actions */}
                    <Card className="mb-6">
                        <CardHeader>
                            <CardTitle className="text-lg">Account Actions</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <Button variant="destructive" className="w-full justify-start gap-3">
                                <LogOut className="h-4 w-4" />
                                Log Out
                            </Button>
                        </CardContent>
                    </Card>

                    {/* Quick Links */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Legal & Support</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap gap-2 text-sm text-muted-foreground">
                                {quickLinks.map((link, index) => (
                                    <span key={index} className="flex items-center">
                                        <Button variant="link" className="h-auto p-0 text-sm text-muted-foreground hover:text-foreground">
                                            {link.title}
                                        </Button>
                                        {index < quickLinks.length - 1 && <span className="mx-2">·</span>}
                                    </span>
                                ))}
                            </div>

                            <Separator className="my-4" />

                            <div className="space-y-3">
                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Shield className="h-4 w-4" />
                                    <span>Your data is protected by industry-standard security measures</span>
                                </div>

                                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <Cookie className="h-4 w-4" />
                                    <span>We use cookies to enhance your experience</span>
                                </div>
                            </div>

                            <div className="mt-4 rounded-lg bg-muted/50 p-3 text-xs text-muted-foreground">
                                <p>
                                    © 2024 Christian Bible Heritage Learning Center. All rights reserved. By using this application, you agree to our
                                    Terms of Service and Privacy Policy.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </PageLayout>
        </>
    );
}
