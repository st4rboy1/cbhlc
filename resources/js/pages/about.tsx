import { type SharedData } from '@/types';
import { Head, usePage, Link } from '@inertiajs/react';
import { LoginDialog } from '@/components/login-dialog';

export default function About() {
    return (
        <>
             <div className="font-sans">
      <header className="fixed top-0 left-0 w-full py-5 px-24 flex justify-between items-center z-[100] bg-[#2c3e50]">
        <Link href="/" className="logo relative text-2xl text-white no-underline font-medium ml-7 hover:text-[#a6b4c2]">CBHLC</Link>
        <nav className="navigation flex items-center">
          <Link href="/about" className="relative text-lg text-white no-underline font-medium ml-10">About</Link>
          <LoginDialog trigger={
            <button className="btnLogin-popup w-[130px] h-[45px] bg-transparent border-2 border-white rounded-md cursor-pointer text-lg text-white font-medium ml-10 transition-all duration-400 hover:bg-white hover:text-black flex items-center justify-center">
              Login
            </button>
          } />
        </nav>
      </header>


      <section className="p-[60px] px-[80px] bg-[#f9f9f9] text-center mt-[50px]">
        <div className="container mx-auto">
          <h1 className="text-4xl font-bold mb-2.5 text-[#2c3e50]">About Us</h1>
          <p className="max-w-2xl mx-auto mb-12 text-lg text-[#555] leading-relaxed">
            <b>Christian Bible Heritage Learning</b> Center is dedicated to nurturing children in both academics and faith,
            helping them grow into individuals with strong character and a brighter future.
          </p>

          <div className="flex justify-between items-start gap-10 flex-wrap">
            {/* LEFT TEXT */}
            <div className="flex-1 text-left">
              <h2 className="text-3xl text-[#2c3e50] mb-4">We're here to enhance every child's potential</h2>
              <p className="text-base text-[#444] leading-relaxed">
                Our mission is to provide quality education rooted in Christian values.
                The school emphasizes both academic excellence and spiritual development,
                ensuring that each child receives holistic growth.
                Through engaging programs, announcements, and activities,
                we support learners in achieving success while living by faith. </p><br />

              <h2 className="text-3xl text-[#2c3e50] mb-4">Achievements</h2>
              <p className="text-base text-[#444] leading-relaxed">
                Our mission is to provide quality education rooted in Christian values.
                The school emphasizes both academic excellence and spiritual development,
                ensuring that each child receives holistic growth.



              </p>
              <br />
              <h2 className="text-3xl text-[#2c3e50] mb-4">Founded</h2>
              <p className="text-base text-[#444] leading-relaxed">
                Our mission is to provide quality education rooted in Christian values.
                The school emphasizes both academic excellence and spiritual development,
                ensuring that each child receives holistic growth.
                Through engaging programs, announcements, and activities,
                we support learners in achieving success while living by faith.


              </p>
              
              <p className="italic text-[#2e7d32] font-bold">"Start children off on the way they should go..." <br /> — Proverbs 22:6</p>
            </div>


            {/* RIGHT IMAGE */}
            <div className="flex-1 text-center">
              <img className="max-w-[80%] rounded-xl shadow-md mb-2.5" src="landing.png" alt="CBHLC Students" />
              <img className="max-w-[80%] rounded-xl shadow-md mb-2.5" src="landing.png" alt="CBHLC Students" />
            </div>
          </div>

          <div className="flex gap-4 text-base justify-center mt-12 flex-wrap">
            <a href="https://www.facebook.com/CBHLC.Pasig" className="text-4xl"><i className="fab fa-facebook"></i></a>
            <a href="https://www.instagram.com/awrabriguela/" className="text-4xl"><i className="fab fa-instagram"></i></a>

          </div>
        </div>
      </section>
            </div>
        </>
    )
}

