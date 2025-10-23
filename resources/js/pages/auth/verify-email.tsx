// Components
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import { logout } from '@/routes';
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';

export default function VerifyEmail({ status, email }: { status?: string; email?: string }) {
    return (
        <AuthLayout
            title="Verify email"
            description={
                email
                    ? `We've sent a verification email to ${email}. Please check your inbox and click the link to verify your account.`
                    : 'Please verify your email address by clicking on the link we just emailed to you.'
            }
        >
            <Head title="Email verification" />

            {status === 'registration-success' && (
                <div className="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-center text-sm text-green-800">
                    <div className="mb-2 text-lg font-semibold">✅ Account Created Successfully!</div>
                    <p>
                        We've sent a verification email to your inbox. Please check your email and click the verification link to complete your
                        registration.
                    </p>
                </div>
            )}

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            )}

            <Form action={EmailVerificationNotificationController.store.url()} method="post" className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Resend verification email
                        </Button>

                        <TextLink href={logout()} className="mx-auto block text-sm">
                            Log out
                        </TextLink>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
