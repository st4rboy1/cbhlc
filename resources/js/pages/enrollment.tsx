import { Link } from '@inertiajs/react';
import Sidebar from '../components/Sidebar';

export default function Enrollment() {
    return (
        <>
            <div className="m-0 bg-[#eef2f5] p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-[#333]">
                <div className="flex min-h-screen">
                    {/* Sidebar */}
                    <Sidebar currentPage="enrollment" />
                    {/*NAV SIDE BAR, SAME LANG TO SA LAHAT NG SIDES  */}
                    {/* Enrollment Main Content */}

                    <main className="flex flex-grow flex-col px-12 py-7">
                        <div className="top-bar mb-0 flex items-center justify-between border-b-2 border-gray-300 py-4">
                            <h1 className="m-0 text-3xl text-[#2c3e50]">ENROLL</h1>
                            <div className="user-info flex items-center">
                                <button
                                    className="icon-btn ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300"
                                    id="openModal"
                                >
                                    <i className="fas fa-bell"></i>
                                    <span className="badge absolute top-[-5px] right-[-5px] rounded-full bg-red-500 px-1.5 py-0.5 text-xs text-white">
                                        3
                                    </span>
                                </button>
                                <span className="mr-5 font-medium text-[#555]">Welcome, Bronny!</span>
                                <button className="icon-btn ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300">
                                    <i className="fas fa-user-circle"></i>
                                </button>
                                <div
                                    className="notification-dropdown absolute top-[50px] right-0 z-[1000] hidden w-[350px] rounded-xl bg-white p-4 shadow-[0_4px_12px_rgba(0,0,0,0.15)]"
                                    id="notificationDropdown"
                                >
                                    <h3 className="m-0 mb-2.5 border-b border-gray-300 pb-2 text-lg text-[#2c3e50]">Notifications</h3>
                                    <div className="notification-item new my-3 flex items-start border-b border-gray-100 pb-3">
                                        <img src="ra_2022-06-19_22-17-45.jpg" alt="User" className="mr-3 h-10 w-10 flex-shrink-0 rounded-full" />
                                        <div className="notification-text flex-1 text-sm text-[#333]">
                                            <strong className="text-[#2c3e50]">Bronny James</strong> added you to the folder{' '}
                                            <b>Web App Designs 2019</b>
                                            <div className="time my-1 mb-1.5 text-xs text-[#777]">Today at 12:28 PM</div>
                                        </div>
                                    </div>

                                    <div className="notification-item new my-3 flex items-start border-b border-gray-100 pb-3">
                                        <img src="ra_2022-06-19_22-17-45.jpg" alt="User" className="mr-3 h-10 w-10 flex-shrink-0 rounded-full" />
                                        <div className="notification-text flex-1 text-sm text-[#333]">
                                            <strong className="text-[#2c3e50]">Bronny James</strong> invited you to the folder{' '}
                                            <b>EMEA Major Deal Close Plans</b>
                                            <div className="time my-1 mb-1.5 text-xs text-[#777]">Yesterday at 5:15 PM</div>
                                        </div>
                                    </div>

                                    <div className="notification-item my-3 flex items-start border-b-0 pb-3">
                                        <div className="circle-avatar mr-3 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#2c3e50] text-sm font-bold text-white">
                                            AS
                                        </div>
                                        <div className="notification-text flex-1 text-sm text-[#333]">
                                            <strong className="text-[#2c3e50]">Bronny James</strong> added you to{' '}
                                            <b>Enterprise Corporation Contracts.pdf</b>
                                            <div className="time my-1 mb-1.5 text-xs text-[#777]">Sep 20 at 3:13 PM</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr className="my-4 border-b-2 border-none border-gray-300" />

                        <form id="applicationForm" className="rounded-xl bg-white px-7 py-6 shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                            {/* Student details */}
                            <h2 style={{ fontSize: '25px' }} className="mb-4 text-xl text-[#1d3557]">
                                Student Details{' '}
                            </h2>
                            <div className="form-section mb-5 flex flex-wrap gap-5">
                                <div className="form-group flex min-w-[250px] flex-1 flex-col gap-3">
                                    <label className="mb-1 text-sm font-semibold text-[#333]">Grade Level</label>
                                    <select
                                        required
                                        className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                    >
                                        <option value="">Choose Grade</option>
                                        <option>Grade 1</option>
                                        <option>Grade 2</option>
                                        <option>Grade 3</option>
                                        <option>Grade 4</option>
                                        <option>Grade 5</option>
                                        <option>Grade 6</option>
                                    </select>
                                </div>
                                <label className="mb-1 text-sm font-semibold text-[#333]">LRN Number</label>
                                <input
                                    type="text"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />
                            </div>

                            <div className="form-group flex min-w-[250px] flex-1 flex-col gap-3">
                                <label className="mb-1 text-sm font-semibold text-[#333]">Surname*</label>
                                <input
                                    type="text"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />

                                <label className="mb-1 text-sm font-semibold text-[#333]">Given Name*</label>
                                <input
                                    type="text"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />

                                <label className="mb-1 text-sm font-semibold text-[#333]">Middle Name</label>
                                <input
                                    type="text"
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />
                            </div>

                            <div className="form-group flex min-w-[250px] flex-1 flex-col gap-3">
                                <label className="mb-1 text-sm font-semibold text-[#333]">Date of Birth*</label>
                                <input
                                    type="date"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />

                                <label className="mb-1 text-sm font-semibold text-[#333]">Gender*</label>
                                <select
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                >
                                    <option value="">Choose Gender</option>
                                    <option>Male</option>
                                    <option>Female</option>
                                </select>

                                <label className="mb-1 text-sm font-semibold text-[#333]">Address*</label>
                                <input
                                    type="text"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />
                            </div>

                            {/* Guardian details */}
                            <h2 className="mb-4 text-xl text-[#1d3557]">Guardian Contact Details</h2>
                            <div className="form-group flex min-w-[250px] flex-1 flex-col gap-3">
                                <label className="mb-1 text-sm font-semibold text-[#333]">Surname*</label>
                                <input
                                    type="text"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />

                                <label className="mb-1 text-sm font-semibold text-[#333]">Given Name*</label>
                                <input
                                    type="text"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />

                                <label className="mb-1 text-sm font-semibold text-[#333]">Middle Name*</label>
                                <input
                                    type="text"
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />
                            </div>

                            <div className="form-group flex min-w-[250px] flex-1 flex-col gap-3">
                                <label className="mb-1 text-sm font-semibold text-[#333]">Cellphone Number*</label>
                                <input
                                    type="tel"
                                    required
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />

                                <label className="mb-1 text-sm font-semibold text-[#333]">Email Address</label>
                                <input
                                    type="email"
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />

                                <label className="mb-1 text-sm font-semibold text-[#333]">Relation to Student</label>
                                <input
                                    type="text"
                                    className="rounded-md border border-gray-300 px-3 py-2.5 text-sm transition-all duration-200 outline-none focus:border-[#457b9d]"
                                />
                            </div>

                            {/* File upload */}
                            <div className="upload my-5">
                                <label className="mb-1 text-sm font-semibold text-[#333]">Upload Documents*</label>
                                <br />
                                <input type="file" required className="mt-2.5 text-sm" />
                            </div>

                            <button
                                type="submit"
                                className="btn-submit inline-block cursor-pointer rounded-lg border-none bg-[#1d3557] px-5 py-3 text-base text-white transition-all duration-300 hover:bg-[#457b9d]"
                            >
                                Submit
                            </button>
                            <nav className="navigation">
                                <br />
                                <Link href="/application" style={{ color: 'blue' }}>
                                    Edit Submitted Application
                                </Link>
                            </nav>
                        </form>
                    </main>
                </div>
            </div>
        </>
    );
}
