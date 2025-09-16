
export default function Registrar() {
    return (
        <>
            <div className="font-['Segoe_UI',_Tahoma,_Verdana,_sans-serif] m-0 p-0 bg-[#eef2f5] text-[#333]">
                <div className="flex min-h-screen">
                    <div className="w-[280px] bg-[#2c3e50] text-[#ecf0f1] shadow-[2px_0_10px_rgba(0,0,0,0.15)] py-5 flex flex-col justify-between">
                        <div>
                            <div className="text-center pt-2.5 pb-7">
                                <img src="ra_2022-06-19_22-17-45.jpg" alt="CBHLC Logo" className="w-[120px] h-auto rounded-full border-3 border-[#3498db]" />
                            </div>
                            <ul className="list-none p-0 m-0">
                                <li className="nav-item flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white" id="dashboardBtn">
                                    <i className="fas fa-chart-line mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                    <span className="font-medium">DASHBOARD</span>
                                </li>
                                <li className="nav-item flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out hover:bg-[#34495e] hover:text-white" id="enrollmentBtn">
                                    <i className="fas fa-user-plus mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                    <span className="font-medium">ENROLLMENT</span>
                                </li>

                                {/* BILLING DROPDOWN */}
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
                                    <i className="fas fa-book mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                    <span className="font-medium">STUDENT REPORT</span>
                                </li>

                                {/* REGISTRAR DROPDOWN */}
                                <li className="nav-item has-dropdown active flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out bg-[#34495e] text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)] scale-105" id="registrarBtn">
                                    <div className="nav-link">
                                        <i className="fas fa-building mr-5 text-xl text-[#3498db] transition-colors duration-300 ease-in-out"></i>
                                        <span className="font-medium">REGISTRAR</span>
                                        <i className="fas fa-chevron-down arrow"></i>
                                    </div>
                                    <ul className="dropdown hidden">
                                        <li>Student Records</li>
                                        <li>Enrollment List</li>
                                        <li>Clearances</li>
                                    </ul>
                                </li>
                            </ul>

                        </div>
                        <div className="logout mt-auto w-[280px]">
                            <li className="nav-item flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out bg-[#f5f5f5] text-black rounded-lg hover:bg-[#c0392b]" id="logoutBtn">
                                <i className="fas fa-sign-out-alt mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                                <span className="font-medium">LOGOUT</span>
                            </li>
                        </div>
                    </div>

                    <div className="flex-grow py-7 px-12 flex flex-col">
                        <div className="top-bar flex justify-between items-center py-4 border-b-2 border-gray-300 mb-5">
                            <h1 id="pageTitle" className="m-0 text-3xl text-[#2c3e50]">REGISTRAR</h1>
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

                        <div id="contentArea">
                            <div className="grid grid-cols-2 gap-7 mb-7">
                                <div className="bg-[#d1d8e0] rounded-xl min-h-[300px] flex justify-center items-center text-[#7f8c8d] text-lg shadow-[inset_0_2px_4px_rgba(0,0,0,0.1)] bg-center bg-cover bg-no-repeat" style={{ backgroundImage: 'url(\'ra_2022-06-19_22-17-45.jpg\')' }}>

                                </div>
                                <div className="bg-center bg-cover bg-no-repeat rounded-xl" style={{ backgroundImage: 'url(\'ra_2022-06-19_22-17-45.jpg\')' }}></div>
                            </div>

                            <div className="col-span-2 bg-white p-6 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                                <h2 className="mt-0 text-2xl text-[#2c3e50] border-b-2 border-[#ecf0f1] pb-2.5 flex justify-between items-center">
                                    Schedules
                                    <i className="fas fa-calendar-alt"></i>
                                </h2>
                                <div className="grid grid-cols-2 gap-7 mt-5">
                                    <div className="bg-[#f8f9fa] rounded-lg p-5 shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)]">
                                        <h3 className="m-0 mb-4 text-base text-[#7f8c8d] uppercase">SCHOOL EVENTS</h3>
                                        <ul className="list-none p-0 m-0">
                                            <li className="flex items-center py-4 border-b border-[#eef2f5] transition-all duration-200 ease-in-out"><i className="fas fa-bullhorn mr-5 text-xl text-[#3498db]"></i><span className="text-base font-medium">START OF CLASSES:</span></li>
                                            <li className="flex items-center py-4 border-b border-[#eef2f5] transition-all duration-200 ease-in-out"><i className="fas fa-users-cog mr-5 text-xl text-[#3498db]"></i><span className="text-base font-medium">PARENT ORIENTATION:</span></li>
                                            <li className="flex items-center py-4 border-b border-[#eef2f5] transition-all duration-200 ease-in-out"><i className="fas fa-building mr-5 text-xl text-[#3498db]"></i><span className="text-base font-medium">FOUNDATION DAY:</span></li>
                                            <li className="flex items-center py-4 border-b border-[#eef2f5] transition-all duration-200 ease-in-out"><i className="fas fa-gifts mr-5 text-xl text-[#3498db]"></i><span className="text-base font-medium">CHRISTMAS PROGRAM:</span></li>
                                            <li className="flex items-center py-4 border-b-0"><i className="fas fa-award mr-5 text-xl text-[#3498db]"></i><span className="text-base font-medium">GRADUATION DAY:</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/* end contentArea */}

                    </div>
                </div>
            </div>
        </>
    );
}
