import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { request } from '@/routes/password';
import { Form } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { useState } from 'react';
import { RegisterDialog } from './register-dialog';

interface LoginDialogProps {
    canResetPassword?: boolean;
    trigger?: React.ReactNode;
}

export function LoginDialog({ canResetPassword = true, trigger }: LoginDialogProps) {
    const [isLoginOpen, setIsLoginOpen] = useState(false);
    const [isRegisterOpen, setIsRegisterOpen] = useState(false);

    const handleRegisterClick = () => {
        setIsLoginOpen(false);
        setIsRegisterOpen(true);
    };

    const handleLoginClick = () => {
        setIsRegisterOpen(false);
        setIsLoginOpen(true);
    };

    return (
        <>
            <Dialog open={isLoginOpen} onOpenChange={setIsLoginOpen}>
                <DialogTrigger asChild>{trigger || <Button variant="outline">Login</Button>}</DialogTrigger>
                <DialogContent className="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Log in to your account</DialogTitle>
                        <DialogDescription>Enter your email and password below to log in</DialogDescription>
                    </DialogHeader>
                    <Form
                        action={AuthenticatedSessionController.store.url()}
                        method="post"
                        resetOnSuccess={['password']}
                        onSuccess={() => setIsLoginOpen(false)}
                        className="flex flex-col gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-6">
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Email address</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            required
                                            autoFocus
                                            tabIndex={1}
                                            autoComplete="email"
                                            placeholder="email@example.com"
                                        />
                                        <InputError message={errors.email} />
                                    </div>

                                    <div className="grid gap-2">
                                        <div className="flex items-center">
                                            <Label htmlFor="password">Password</Label>
                                            {canResetPassword && (
                                                <TextLink href={request()} className="ml-auto text-sm" tabIndex={5}>
                                                    Forgot password?
                                                </TextLink>
                                            )}
                                        </div>
                                        <Input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            tabIndex={2}
                                            autoComplete="current-password"
                                            placeholder="Password"
                                        />
                                        <InputError message={errors.password} />
                                    </div>

                                    <div className="flex items-center space-x-3">
                                        <Checkbox id="remember" name="remember" tabIndex={3} />
                                        <Label htmlFor="remember">Remember me</Label>
                                    </div>

                                    <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing}>
                                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                        Log in
                                    </Button>
                                </div>

                                <div className="text-center text-sm text-muted-foreground">
                                    Don't have an account?{' '}
                                    <Button
                                        variant="link"
                                        className="p-0 text-sm"
                                        onClick={(e) => {
                                            e.preventDefault();
                                            handleRegisterClick();
                                        }}
                                        tabIndex={5}
                                    >
                                        Sign up
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
            <RegisterDialog isOpen={isRegisterOpen} onOpenChange={setIsRegisterOpen} onLoginClick={handleLoginClick} />
        </>
    );
}
