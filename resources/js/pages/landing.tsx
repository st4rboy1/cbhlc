import { LoginDialog } from '@/components/login-dialog';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useRef } from 'react';

export default function Landing() {

    const loginTriggerRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('login') === 'true') {
            // Auto-click the login button to open the modal
            setTimeout(() => {
                loginTriggerRef.current?.click();
            }, 100);
            // Clean up the URL
            window.history.replaceState({}, '', '/');
        }
    }, []);

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
            </Head>
            <div className="bg-white font-sans">
                <header className="fixed top-0 left-0 w-full py-5 px-24 flex justify-between items-center z-[100] bg-[#2c3e50]">
                    <Link href="/" className="logo relative text-2xl text-white no-underline font-medium ml-7 hover:text-[#a6b4c2]">CBHLC</Link>
                    <nav className="navigation flex items-center">
                        <Link href="/about" className="relative text-lg text-white no-underline font-medium ml-10">About</Link>
                        <LoginDialog trigger={
                            <button ref={loginTriggerRef} className="w-[130px] h-[45px] bg-transparent border-2 border-white rounded-md cursor-pointer text-lg text-white font-medium ml-10 transition-all duration-400 hover:bg-white hover:text-black flex items-center justify-center">
                                Login
                            </button>
                        } />
                    </nav>
                </header>
                <section className="landing bg">
                    <div className="landing-bg flex justify-center items-center flex-col text-center pt-[120px] px-5 pb-10 object-cover">
                        <div className="hero-image">
                            <img src="landingp.png" alt="School Photo" className="w-[1500px] h-[700px] rounded-lg" />
                        </div>
                        <p className="bible-verse mt-5 text-lg font-bold text-black">
                            "Start children off on the way they should go..." <br />
                            — Proverbs 22:6
                        </p>
                        <p className="copyright mt-2.5 text-sm text-black">
                            ©2025 CBHLC | All rights reserved |
                        </p>
                        <div className="contact relative bottom-2.5 mr-[1500px] flex gap-4 text-base z-[100] justify-center mt-7 flex-wrap">
                            <a href="https://www.facebook.com/CBHLC.Pasig" className="text-4xl"><i className="fab fa-facebook"></i></a>
                            <a href="https://www.instagram.com/awrabriguela/" className="text-4xl"><i className="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}
