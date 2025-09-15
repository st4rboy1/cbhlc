import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function ProfileSettings() {
    return (
        <>
            <div className="font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] m-0 p-0 bg-[#eef2f5] text-[#333]">
                <div className="flex min-h-screen">
                    {/* Sidebar (same as dashboard) */}
                    <aside className="w-[280px] bg-[#2c3e50] text-[#ecf0f1] shadow-[2px_0_10px_rgba(0,0,0,0.15)] py-5 flex flex-col justify-between">
                        <div className="text-center pt-2.5 pb-7">
                            <img src="ra_2022-06-19_22-17-45.jpg" alt="CBHLC Logo" className="w-[120px] h-auto rounded-full border-3 border-[#3498db]" />
                        </div>
                        <ul className="list-none p-0 m-0">
                            <li className="nav-item flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white" id="dashboardBtn">
                                <i className="fas fa-chart-line mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i><span>DASHBOARD</span>
                            </li>

                            <li className="nav-item flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white" id="enrollmentBtn">
                                <i className="fas fa-user-plus mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i><span>ENROLLMENT</span>
                            </li>

                            {/* BILLING WITH DROPDOWN */}
                            <li className="nav-item has-dropdown flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white" id="billingBtn">
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

                            <li className="nav-item flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white" id="studRepBtn">
                                <i className="fas fa-book mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i><span>STUDENT REPORT</span>
                            </li>

                            <li className="nav-item has-dropdown flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white" id="registrarBtn">
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
                            <li className="nav-item flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out bg-[#f5f5f5] text-black rounded-lg hover:bg-[#c0392b]" id="logoutBtn">
                                <i className="fas fa-sign-out-alt mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i><span className="font-medium">LOGOUT</span>
                            </li>
                        </div>
                    </aside>


                    <div className="flex-grow py-7 px-12 flex flex-col">
                        <div className="top-bar flex justify-between items-center py-4 border-b-2 border-gray-300 mb-5">
                            <h1 id="pageTitle" className="m-0 text-3xl text-[#2c3e50]">INVOICE</h1>
                            <div className="user-info flex items-center relative">
                                <button className="icon-btn bg-transparent border-none cursor-pointer text-2xl ml-4 text-[#95a5a6] transition-colors duration-300 relative" id="openModal"><i className="fas fa-bell"></i>
                                    <span className="badge absolute top-[-5px] right-[-5px] bg-red-500 text-white text-xs py-0.5 px-1.5 rounded-full">3</span></button>
                                <span className="mr-5 font-medium text-[#555]">Welcome, Bronny!</span>
                                <button className="icon-btn bg-transparent border-none cursor-pointer text-2xl ml-4 text-[#95a5a6] transition-colors duration-300 relative"><i className="fas fa-user-circle"></i></button>
                                <div className="notification-dropdown hidden absolute top-[50px] right-0 w-[350px] bg-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.15)] p-4 z-[1000]" id="notificationDropdown">
                                    <h3 className="m-0 mb-2.5 text-lg text-[#2c3e50] border-b border-gray-300 pb-2">Notifications</h3>
                                    <div className="notification-item new flex items-start my-3 pb-3 border-b border-gray-100">
                                        <img src="ra_2022-06-19_22-17-45.jpg" alt="User" className="w-10 h-10 rounded-full flex-shrink-0 mr-3" />
                                        <div className="notification-text flex-1 text-sm text-[#333]">
                                            <strong className="text-[#2c3e50]">Bronny James</strong> added you to the folder <b>Web App Designs 2019</b>
                                            <div className="time text-xs text-[#777] my-1 mb-1.5">Today at 12:28 PM</div>

                                        </div>
                                    </div>

                                    <div className="notification-item new flex items-start my-3 pb-3 border-b border-gray-100">
                                        <img src="ra_2022-06-19_22-17-45.jpg" alt="User" className="w-10 h-10 rounded-full flex-shrink-0 mr-3" />
                                        <div className="notification-text flex-1 text-sm text-[#333]">
                                            <strong className="text-[#2c3e50]">Bronny James</strong> invited you to the folder <b>EMEA Major Deal Close Plans</b>
                                            <div className="time text-xs text-[#777] my-1 mb-1.5">Yesterday at 5:15 PM</div>

                                        </div>
                                    </div>

                                    <div className="notification-item flex items-start my-3 pb-3 border-b-0">
                                        <div className="circle-avatar w-10 h-10 rounded-full flex-shrink-0 mr-3 bg-[#2c3e50] text-white text-sm flex justify-center items-center font-bold">AS</div>
                                        <div className="notification-text flex-1 text-sm text-[#333]">
                                            <strong className="text-[#2c3e50]">Bronny James</strong> added you to <b>Enterprise Corporation Contracts.pdf</b>
                                            <div className="time text-xs text-[#777] my-1 mb-1.5">Sep 20 at 3:13 PM</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div className="Profile-Settings">
                            <div className="filter-group flex flex-col min-w-[180px]">
                                <div className="profile-menu-container">
                                    <div className="profile-header flex items-center">
                                        <img src="ra_2022-06-19_22-17-45.jpg" alt="Profile Avatar" className="avatar w-12 h-12 rounded-full mr-4" />
                                        <div>
                                            <h3 className="m-0 text-lg font-semibold">Bronny</h3>
                                            <p style={{ margin: '0', fontSize: '0.9rem', color: '#000000' }}>View profile</p>
                                        </div>
                                    </div>

                                    <div className="profile-list mt-4">
                                        <div className="profile-option flex items-center p-3 rounded-lg hover:bg-gray-200 cursor-pointer">
                                            <i className="fas fa-user w-6 text-center mr-3"></i>
                                            <span>Profile</span>
                                        </div>
                                        <div className="profile-option flex items-center p-3 rounded-lg hover:bg-gray-200 cursor-pointer justify-center">
                                            <i className="fas fa-users w-6 text-center mr-3" style={{ marginRight: '10px' }}></i>
                                            <span>See all profiles</span>
                                        </div>
                                        <div className="profile-option flex items-center p-3 rounded-lg hover:bg-gray-200 cursor-pointer">
                                            <i className="fas fa-cog w-6 text-center mr-3"></i>
                                            <span>Settings & privacy</span>
                                            <i className="fas fa-chevron-right arrow ml-auto"></i>
                                        </div>
                                        <div className="profile-option flex items-center p-3 rounded-lg hover:bg-gray-200 cursor-pointer">
                                            <i className="fas fa-question-circle w-6 text-center mr-3"></i>
                                            <span>Help & support</span>
                                            <i className="fas fa-chevron-right arrow ml-auto"></i>
                                        </div>
                                        <div className="profile-option flex items-center p-3 rounded-lg hover:bg-gray-200 cursor-pointer">
                                            <i className="fas fa-adjust w-6 text-center mr-3"></i>
                                            <span>Display & accessibility</span>
                                            <i className="fas fa-chevron-right arrow ml-auto"></i>
                                        </div>
                                        <div className="profile-option flex items-center p-3 rounded-lg hover:bg-gray-200 cursor-pointer">
                                            <i className="fas fa-comment-dots w-6 text-center mr-3"></i>
                                            <span>Give feedback</span>
                                        </div>
                                        <div className="profile-option logout-option flex items-center p-3 rounded-lg hover:bg-gray-200 cursor-pointer">
                                            <i className="fas fa-sign-out-alt w-6 text-center mr-3"></i>
                                            <span>Log Out</span>
                                        </div>


                                        <div className="privacy-links mt-4 text-xs text-gray-500">
                                            <a href="#" className="hover:underline">Privacy</a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">Terms</a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">Advertising</a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">Ad Choices</a>
                                            <i className="fas fa-caret-right ml-1"></i>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">Cookies</a>
                                            <span className="mx-1">·</span>
                                            <a href="#" className="hover:underline">More</a>
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
