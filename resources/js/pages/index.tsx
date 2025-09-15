import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Index() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <div className="bg-white font-sans">
                <header className="fixed top-0 left-0 w-full py-5 px-24 flex justify-between items-center z-[100] bg-[#2c3e50]">
                    <a href="index.html" className="logo relative text-2xl text-white no-underline font-medium ml-7 hover:text-[#a6b4c2]">CBHLC </a>
                    <nav className="navigation">
                        <a href="about.html" className="relative text-lg text-white no-underline font-medium ml-10">About</a>
                        <button className="btnLogin-popup w-[130px] h-[45px] bg-transparent border-2 border-white rounded-md cursor-pointer text-lg text-white font-medium ml-10 transition-all duration-400 hover:bg-white hover:text-black">Login</button>
                    </nav>
                </header>
                <div />

                <section className="landing bg">
                    <div className="landing-bg flex justify-center items-center flex-col text-center pt-[120px] px-5 pb-10 object-cover">
                        <div className="hero-image">
                            <img src="landingp.png" alt="School Photo" className="w-[1500px] h-[700px] rounded-lg" />
                        </div>
                        <p className="bible-verse mt-5 text-lg font-bold text-black">
                            “Start children off on the way they should go...” <br />
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
