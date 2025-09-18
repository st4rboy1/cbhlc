import Sidebar from '../components/Sidebar';

export default function ProfileSettings() {
    return (
        <>
            <div className="m-0 bg-[#eef2f5] p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-[#333]">
                <div className="flex min-h-screen">
                    {/* Sidebar */}
                    <Sidebar currentPage="profilesettings" />

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
