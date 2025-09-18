export default function ProfileSettings() {
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
                                <span>DASHBOARD</span>
                            </li>

                            <li
                                className="nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white"
                                id="enrollmentBtn"
                            >
                                <i className="fas fa-user-plus mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                <span>ENROLLMENT</span>
                            </li>

                            {/* BILLING WITH DROPDOWN */}
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
                                <span>STUDENT REPORT</span>
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

                    <div className="flex flex-grow flex-col px-12 py-7">
                        <div className="top-bar mb-5 flex items-center justify-between border-b-2 border-gray-300 py-4">
                            <h1 id="pageTitle" className="m-0 text-3xl text-[#2c3e50]">
                                INVOICE
                            </h1>
                            <div className="user-info relative flex items-center">
                                <button
                                    className="icon-btn relative ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300"
                                    id="openModal"
                                >
                                    <i className="fas fa-bell"></i>
                                    <span className="badge absolute top-[-5px] right-[-5px] rounded-full bg-red-500 px-1.5 py-0.5 text-xs text-white">
                                        3
                                    </span>
                                </button>
                                <span className="mr-5 font-medium text-[#555]">Welcome, Bronny!</span>
                                <button className="icon-btn relative ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300">
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
                        <div className="Profile-Settings">
                            <div className="filter-group flex min-w-[180px] flex-col">
                                <div className="profile-menu-container">
                                    <div className="profile-header flex items-center">
                                        <img src="ra_2022-06-19_22-17-45.jpg" alt="Profile Avatar" className="avatar mr-4 h-12 w-12 rounded-full" />
                                        <div>
                                            <h3 className="m-0 text-lg font-semibold">Bronny</h3>
                                            <p style={{ margin: '0', fontSize: '0.9rem', color: '#000000' }}>View profile</p>
                                        </div>
                                    </div>

                                    <div className="profile-list mt-4">
                                        <div className="profile-option flex cursor-pointer items-center rounded-lg p-3 hover:bg-gray-200">
                                            <i className="fas fa-user mr-3 w-6 text-center"></i>
                                            <span>Profile</span>
                                        </div>
                                        <div className="profile-option flex cursor-pointer items-center justify-center rounded-lg p-3 hover:bg-gray-200">
                                            <i className="fas fa-users mr-3 w-6 text-center" style={{ marginRight: '10px' }}></i>
                                            <span>See all profiles</span>
                                        </div>
                                        <div className="profile-option flex cursor-pointer items-center rounded-lg p-3 hover:bg-gray-200">
                                            <i className="fas fa-cog mr-3 w-6 text-center"></i>
                                            <span>Settings & privacy</span>
                                            <i className="fas fa-chevron-right arrow ml-auto"></i>
                                        </div>
                                        <div className="profile-option flex cursor-pointer items-center rounded-lg p-3 hover:bg-gray-200">
                                            <i className="fas fa-question-circle mr-3 w-6 text-center"></i>
                                            <span>Help & support</span>
                                            <i className="fas fa-chevron-right arrow ml-auto"></i>
                                        </div>
                                        <div className="profile-option flex cursor-pointer items-center rounded-lg p-3 hover:bg-gray-200">
                                            <i className="fas fa-adjust mr-3 w-6 text-center"></i>
                                            <span>Display & accessibility</span>
                                            <i className="fas fa-chevron-right arrow ml-auto"></i>
                                        </div>
                                        <div className="profile-option flex cursor-pointer items-center rounded-lg p-3 hover:bg-gray-200">
                                            <i className="fas fa-comment-dots mr-3 w-6 text-center"></i>
                                            <span>Give feedback</span>
                                        </div>
                                        <div className="profile-option logout-option flex cursor-pointer items-center rounded-lg p-3 hover:bg-gray-200">
                                            <i className="fas fa-sign-out-alt mr-3 w-6 text-center"></i>
                                            <span>Log Out</span>
                                        </div>

                                        <div className="privacy-links mt-4 text-xs text-gray-500">
                                            <a href="#" className="hover:underline">
                                                Privacy
                                            </a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">
                                                Terms
                                            </a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">
                                                Advertising
                                            </a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">
                                                Ad Choices
                                            </a>
                                            <i className="fas fa-caret-right ml-1"></i>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">
                                                Cookies
                                            </a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">
                                                More
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
