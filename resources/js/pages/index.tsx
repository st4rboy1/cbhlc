import { Head, Link } from '@inertiajs/react';

export default function Index() {
    return (
        <>
            <Head title="Welcome" />
            <div className="bg-white font-sans">
                <header className="fixed top-0 left-0 z-[100] flex w-full items-center justify-between bg-[#2c3e50] px-24 py-5">
                    <Link href="/" className="logo relative ml-7 text-2xl font-medium text-white no-underline hover:text-[#a6b4c2]">
                        CBHLC{' '}
                    </Link>
                    <nav className="navigation">
                        <Link href="/about" className="relative ml-10 text-lg font-medium text-white no-underline">
                            About
                        </Link>
                        <button className="btnLogin-popup ml-10 h-[45px] w-[130px] cursor-pointer rounded-md border-2 border-white bg-transparent text-lg font-medium text-white transition-all duration-400 hover:bg-white hover:text-black">
                            Login
                        </button>
                    </nav>
                </header>
                <div />

                <section className="landing bg">
                    <div className="landing-bg flex flex-col items-center justify-center object-cover px-5 pt-[120px] pb-10 text-center">
                        <div className="hero-image">
                            <img src="landingp.png" alt="School Photo" className="h-[700px] w-[1500px] rounded-lg" />
                        </div>
                        <p className="bible-verse mt-5 text-lg font-bold text-black">
                            “Start children off on the way they should go...” <br />— Proverbs 22:6
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
