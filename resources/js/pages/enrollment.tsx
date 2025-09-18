import { Link } from '@inertiajs/react';

export default function Enrollment() {
    return (
        <>
            <div className="m-0 bg-[#eef2f5] p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-[#333]">
                <div className="flex min-h-screen">
                    {/* Sidebar (same as dashboard) */}
                    <aside className="flex w-[280px] flex-col justify-between bg-[#2c3e50] py-5 text-[#ecf0f1] shadow-[2px_0_10px_rgba(0,0,0,0.15)]">
                        <div className="pt-2.5 pb-7 text-center">
                            <img
                                src="ra_2022-06-19_22-17-45.jpg"
                                alt="CBHLC Logo"
                                className="h-auto w-[120px] rounded-full border-3 border-[#3498db]"
                            />
                        </div>

                        <ul className="m-0 list-none p-0">
                            <li
                                className="nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white"
                                id="dashboardBtn"
                            >
                                <i className="fas fa-chart-line mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                <span className="font-medium">DASHBOARD</span>
                            </li>

                            <li
                                className="nav-item active mx-4 my-1 flex scale-105 cursor-pointer items-center rounded-lg bg-[#34495e] px-6 py-4 text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)] transition-all duration-300 ease-in-out"
                                id="enrollmentBtn"
                            >
                                <i className="fas fa-user-plus mr-5 text-xl text-[#3498db] transition-colors duration-300 ease-in-out"></i>
                                <span className="font-medium">ENROLLMENT</span>
                            </li>

                            <li
                                className="nav-item has-dropdown mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white"
                                id="billingBtn"
                            >
                                <div className="nav-link">
                                    <i className="fas fa-file-invoice mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                    <span className="font-medium">BILLING</span>
                                    <i className="fas fa-chevron-down arrow" style={{ marginLeft: '55px' }}></i>
                                </div>
                                <ul className="dropdown hidden">
                                    <li>Tuition Fee</li>

                                    <li>Generate Invoice</li>
                                </ul>
                            </li>
                            <li
                                className="nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white"
                                id="studRepBtn"
                            >
                                <i className="fas fa-book mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                <span className="font-medium">STUDENT REPORT</span>
                            </li>

                            <li
                                className="nav-item has-dropdown mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white"
                                id="registrarBtn"
                            >
                                <div className="nav-link">
                                    <i className="fas fa-building mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                    <span className="font-medium">REGISTRAR</span>
                                    <i className="fas fa-chevron-down arrow" style={{ marginLeft: '30px' }}></i>
                                </div>
                                <ul className="dropdown hidden">
                                    <li>Student Records</li>
                                    <li>Enrollment List</li>
                                    <li>Clearances</li>
                                </ul>
                            </li>
                        </ul>
                        <div className="logout mt-auto w-[280px]">
                            <li
                                className="nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg bg-[#f5f5f5] px-6 py-4 text-black transition-all duration-300 ease-in-out hover:bg-[#c0392b]"
                                id="logoutBtn"
                            >
                                <i className="fas fa-sign-out-alt mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                <span className="font-medium">LOGOUT</span>
                            </li>
                        </div>
                    </aside>
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
