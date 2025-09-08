import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="About Us" />
            <header>
                <a href="/" className="logo">CBHLC</a>
                <nav>
                    <a href="/about">About</a>
                    <button className="width[130px] height[45px] background-transparent">Login</button>
                </nav>
            </header>

            <section className="about-section">
                <div className="container">
                    <h1 className="about-title">About Us</h1>
                    <p className="about-subtitle">
                        <b>Christian Bible Heritage Learning</b> Center is dedicated to nurturing children in both academics and faith,
                        helping them grow into individuals with strong character and a brighter future.
                    </p>

                    <div className="about-content">
                        {/* LEFT TEXT */}
                        <div className="text-box">
                            <h2>We’re here to enhance every child’s potential</h2>
                            <p>
                                Our mission is to provide quality education rooted in Christian values.
                                The school emphasizes both academic excellence and spiritual development,
                                ensuring that each child receives holistic growth.
                                Through engaging programs, announcements, and activities,
                                we support learners in achieving success while living by faith.
                            </p>
                            <br />
                            <h2>Achievements</h2>
                            <p>
                                Our mission is to provide quality education rooted in Christian values.
                                The school emphasizes both academic excellence and spiritual development,
                                ensuring that each child receives holistic growth.
                            </p>
                            <br />
                            <h2>Founded</h2>
                            <p>
                                Our mission is to provide quality education rooted in Christian values.
                                The school emphasizes both academic excellence and spiritual development,
                                ensuring that each child receives holistic growth.
                                Through engaging programs, announcements, and activities,
                                we support learners in achieving success while living by faith.
                            </p>
                            <p className="bible-verse">
                                “Start children off on the way they should go...” <br /> — Proverbs 22:6
                            </p>
                        </div>

                        {/* RIGHT IMAGE */}
                        <div className="image-box">
                            <img src="landing.png" alt="CBHLC Students" />
                            <img src="landing.png" alt="CBHLC Students" />
                        </div>
                    </div>

                    <div className="contact">
                        <a href="https://www.facebook.com/CBHLC.Pasig"><i className="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/awrabriguela/"><i className="fab fa-instagram"></i></a>
                    </div>
                </div>
            </section>

            {/* LOGIN / REGISTER / FORGOT */}
            <div className="wrapper" style={{ display: 'none' }}>
                <span className="icon-close">
                    {/* You may need to import IonIcon or use a different icon library */}
                    {/* Replace IonIcon with a React icon */}
                    <span style={{ fontSize: '2rem', cursor: 'pointer' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="feather feather-x">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </span>
                </span>

                {/* LOGIN */}
                <div className="form-box login">
                    <h2>Login</h2>
                    <br />
                    <form id="loginForm">
                        <div className="input-box">
                            <input type="text" placeholder="Username" required />
                        </div>
                        <div className="input-box">
                            <input type="password" placeholder="Password" required />
                        </div>
                        <div className="forgot-pass">
                            <a href="#" className="forgot-pass-btn">Forgot password?</a>
                        </div>
                        <br /><br /><br />
                        <button type="submit" className="btn">Login</button>
                        <div className="login-link">
                            <br />
                            <p>Don’t have an account? <a href="#" className="register-link-btn">Register</a></p>
                        </div>
                    </form>
                </div>

                {/* RESET PASSWORD */}
                <div className="form-box password">
                    <h2 className="reset-password">Reset Password</h2>
                    <form action="#">
                        <div className="input-box">
                            <input type="text" placeholder="Old Password" required />
                        </div>
                        <div className="input-box">
                            <input type="text" placeholder="New Password" required />
                        </div>
                        <div className="input-box">
                            <input type="password" placeholder="Confirm Password" required />
                        </div>
                        <button type="submit" className="btn">Confirm</button>
                        <div className="register-link">
                            <p>Already have an account? <a href="#" className="login-link-btn">Login</a></p>
                        </div>
                    </form>
                </div>

                {/* REGISTER */}
                <div className="form-box register">
                    <h2 className="registration-text">Create Account</h2>
                    <form id="registerForm">
                        <br />
                        <div className="input-box">
                            <input type="text" name="name" placeholder="Name" required />
                        </div>
                        <div className="input-box">
                            <input type="email" name="email" placeholder="Email" required />
                        </div>
                        <div className="input-box">
                            <input type="password" name="password" placeholder="Password" required />
                        </div>
                        <div className="input-box">
                            <select name="role" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="parent">Parent</option>
                                <option value="registrar">Registrar</option>
                            </select>
                        </div>
                        <button type="submit" className="btn" id="registerBtn">Register</button>
                        <div className="register-link">
                            <p>Already have an account? <a href="#" className="login-link-btn">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}

