import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { send } from '@/routes/verification';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit } from '@/routes/profile';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

interface Guardian {
    id: number;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    contact_number: string | null;
    address: string | null;
    occupation: string | null;
    employer: string | null;
}

export default function Profile({ mustVerifyEmail, status, guardian }: { mustVerifyEmail: boolean; status?: string; guardian?: Guardian | null }) {
    const { auth } = usePage<SharedData>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Account Information" description="Update your account details" />

                    <Form
                        action={ProfileController.update.url()}
                        method="patch"
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>

                                    <Input
                                        id="name"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="Full name"
                                    />

                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>

                                    <Input
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder="Email address"
                                    />

                                    <InputError className="mt-2" message={errors.email} />
                                </div>

                                {guardian && (
                                    <>
                                        <div className="border-t pt-6">
                                            <h3 className="mb-4 text-lg font-semibold">Guardian Profile</h3>
                                            <p className="mb-4 text-sm text-muted-foreground">Update your guardian information</p>
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="first_name">First Name</Label>
                                                <Input
                                                    id="first_name"
                                                    name="first_name"
                                                    defaultValue={guardian.first_name}
                                                    placeholder="First name"
                                                />
                                                <InputError message={errors.first_name} />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="middle_name">Middle Name</Label>
                                                <Input
                                                    id="middle_name"
                                                    name="middle_name"
                                                    defaultValue={guardian.middle_name || ''}
                                                    placeholder="Middle name (optional)"
                                                />
                                                <InputError message={errors.middle_name} />
                                            </div>
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="last_name">Last Name</Label>
                                            <Input id="last_name" name="last_name" defaultValue={guardian.last_name} placeholder="Last name" />
                                            <InputError message={errors.last_name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="contact_number">Contact Number</Label>
                                            <Input
                                                id="contact_number"
                                                name="contact_number"
                                                defaultValue={guardian.contact_number || ''}
                                                placeholder="Phone number"
                                            />
                                            <InputError message={errors.contact_number} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="address">Address</Label>
                                            <Input id="address" name="address" defaultValue={guardian.address || ''} placeholder="Complete address" />
                                            <InputError message={errors.address} />
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="occupation">Occupation</Label>
                                                <Input
                                                    id="occupation"
                                                    name="occupation"
                                                    defaultValue={guardian.occupation || ''}
                                                    placeholder="Your occupation"
                                                />
                                                <InputError message={errors.occupation} />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="employer">Employer</Label>
                                                <Input
                                                    id="employer"
                                                    name="employer"
                                                    defaultValue={guardian.employer || ''}
                                                    placeholder="Company name (optional)"
                                                />
                                                <InputError message={errors.employer} />
                                            </div>
                                        </div>
                                    </>
                                )}

                                {mustVerifyEmail && auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Click here to resend the verification email.
                                            </Link>
                                        </p>

                                        {status === 'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                A new verification link has been sent to your email address.
                                            </div>
                                        )}
                                    </div>
                                )}

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>Save</Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">Saved</p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
