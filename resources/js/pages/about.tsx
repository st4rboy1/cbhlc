import { Link } from '@inertiajs/react';

export default function About() {
    return (
        <>
            <div className="font-sans">
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

                <section className="mt-[50px] bg-[#f9f9f9] p-[60px] px-[80px] text-center">
                    <div className="container mx-auto">
                        <h1 className="mb-2.5 text-4xl font-bold text-[#2c3e50]">About Us</h1>
                        <p className="mx-auto mb-12 max-w-2xl text-lg leading-relaxed text-[#555]">
                            <b>Christian Bible Heritage Learning</b> Center is dedicated to nurturing children in both academics and faith, helping
                            them grow into individuals with strong character and a brighter future.
                        </p>

                        <div className="ml-48 flex flex-wrap items-center justify-between gap-10">
                            {/* LEFT TEXT */}
                            <div className="mb-[450px] ml-0 h-[200px] flex-1 text-left">
                                <h2 className="mb-4 text-3xl text-[#2c3e50]">We’re here to enhance every child’s potential</h2>
                                <p className="text-base leading-relaxed text-[#444]">
                                    Our mission is to provide quality education rooted in Christian values. The school emphasizes both academic
                                    excellence and spiritual development, ensuring that each child receives holistic growth. Through engaging
                                    programs, announcements, and activities, we support learners in achieving success while living by faith.{' '}
                                </p>
                                <br />

                                <h2 className="mb-4 text-3xl text-[#2c3e50]">Achievements</h2>
                                <p className="text-base leading-relaxed text-[#444]">
                                    Our mission is to provide quality education rooted in Christian values. The school emphasizes both academic
                                    excellence and spiritual development, ensuring that each child receives holistic growth.
                                </p>
                                <br />
                                <h2 className="mb-4 text-3xl text-[#2c3e50]">Founded</h2>
                                <p className="text-base leading-relaxed text-[#444]">
                                    Our mission is to provide quality education rooted in Christian values. The school emphasizes both academic
                                    excellence and spiritual development, ensuring that each child receives holistic growth. Through engaging
                                    programs, announcements, and activities, we support learners in achieving success while living by faith.
                                </p>

                                <p className="font-bold text-[#2e7d32] italic">
                                    “Start children off on the way they should go...” <br /> — Proverbs 22:6
                                </p>
                            </div>

                            {/* RIGHT IMAGE */}
                            <div className="flex-1 text-center">
                                <img className="mb-2.5 max-w-[80%] rounded-xl shadow-md" src="landing.png" alt="CBHLC Students" />
                                <img className="mb-2.5 max-w-[80%] rounded-xl shadow-md" src="landing.png" alt="CBHLC Students" />
                            </div>
                        </div>

                        <div className="relative bottom-2.5 z-[100] mt-7 mr-[1500px] flex flex-wrap justify-center gap-4 text-base">
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
