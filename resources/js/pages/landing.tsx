import { LoginDialog } from '@/components/login-dialog';
import { dashboard } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Landing() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
            </Head>
            <div className="bg-white font-sans">
                <header className="fixed top-0 left-0 z-[100] flex w-full items-center justify-between bg-[#2c3e50] px-24 py-5">
                    <Link href="/" className="logo relative ml-7 text-2xl font-medium text-white no-underline hover:text-[#a6b4c2]">
                        CBHLC
                    </Link>
                    <nav className="navigation flex items-center">
                        <Link href="/about" className="relative ml-10 text-lg font-medium text-white no-underline">
                            About
                        </Link>
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="ml-10 flex h-[45px] w-[130px] cursor-pointer items-center justify-center rounded-md border-2 border-white bg-transparent text-lg font-medium text-white transition-all duration-400 hover:bg-white hover:text-black"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <LoginDialog
                                trigger={
                                    <button className="ml-10 flex h-[45px] w-[130px] cursor-pointer items-center justify-center rounded-md border-2 border-white bg-transparent text-lg font-medium text-white transition-all duration-400 hover:bg-white hover:text-black">
                                        Login
                                    </button>
                                }
                            />
                        )}
                    </nav>
                </header>
                <section className="landing bg">
                    <div className="landing-bg flex flex-col items-center justify-center object-cover px-5 pt-[120px] pb-10 text-center">
                        <div className="hero-image">
                            <img src="landingp.png" alt="School Photo" className="h-[700px] w-[1500px] rounded-lg" />
                        </div>
                        <p className="bible-verse mt-5 text-lg font-bold text-black">
                            "Start children off on the way they should go..." <br />— Proverbs 22:6
                        </p>
                        <p className="copyright mt-2.5 text-sm text-black">©2025 CBHLC | All rights reserved |</p>
                        <div className="contact relative bottom-2.5 z-[100] mt-7 mr-[1500px] flex flex-wrap justify-center gap-4 text-base">
                            <a href="https://www.facebook.com/CBHLC.Pasig" className="text-4xl">
                                <i className="fab fa-facebook"></i>
                            </a>
                            <a href="https://www.instagram.com/awrabriguela/" className="text-4xl">
                                <i className="fab fa-instagram"></i>
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}
