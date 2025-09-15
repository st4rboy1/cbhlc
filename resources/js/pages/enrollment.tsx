import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Enrollment() {
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
                                <i className="fas fa-chart-line mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i><span className="font-medium">DASHBOARD</span>
                            </li>


                            <li className="nav-item active flex items-center py-4 px-6 my-1 mx-4 rounded-lg cursor-pointer transition-all duration-300 ease-in-out bg-[#34495e] text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)] scale-105" id="enrollmentBtn">
                                <i className="fas fa-user-plus mr-5 text-xl text-[#3498db] transition-colors duration-300 ease-in-out"></i><span className="font-medium">ENROLLMENT</span>
                            </li>

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
                    {/*NAV SIDE BAR, SAME LANG TO SA LAHAT NG SIDES  */}
                    {/* Enrollment Main Content */}

                    <main className="flex-grow py-7 px-12 flex flex-col">
                        <div className="top-bar flex justify-between items-center py-4 border-b-2 border-gray-300 mb-0">
                            <h1 className="m-0 text-3xl text-[#2c3e50]">ENROLL</h1>
                            <div className="user-info flex items-center">
                                <button className="icon-btn bg-transparent border-none cursor-pointer text-2xl ml-4 text-[#95a5a6] transition-colors duration-300" id="openModal"><i className="fas fa-bell"></i>
                                    <span className="badge absolute top-[-5px] right-[-5px] bg-red-500 text-white text-xs py-0.5 px-1.5 rounded-full">3</span></button>
                                <span className="mr-5 font-medium text-[#555]">Welcome, Bronny!</span>
                                <button className="icon-btn bg-transparent border-none cursor-pointer text-2xl ml-4 text-[#95a5a6] transition-colors duration-300"><i className="fas fa-user-circle"></i></button>
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

                        <hr className="my-4 border-none border-b-2 border-gray-300" />


                        <form id="applicationForm" className="bg-white py-6 px-7 rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                            {/* Student details */}
                            <h2 style={{ fontSize: '25px' }} className="text-xl mb-4 text-[#1d3557]">Student Details </h2>
                            <div className="form-section flex flex-wrap gap-5 mb-5">
                                <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                    <label className="text-sm font-semibold mb-1 text-[#333]">Grade Level</label>
                                    <select required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]">
                                        <option value="">Choose Grade</option>
                                        <option>Grade 1</option>
                                        <option>Grade 2</option>
                                        <option>Grade 3</option>
                                        <option>Grade 4</option>
                                        <option>Grade 5</option>
                                        <option>Grade 6</option>
                                    </select>
                                </div>
                                <label className="text-sm font-semibold mb-1 text-[#333]">LRN Number</label>
                                <input type="text" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>

                            <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Surname*</label>
                                <input type="text" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Given Name*</label>
                                <input type="text" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Middle Name</label>
                                <input type="text" className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>

                            <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Date of Birth*</label>
                                <input type="date" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Gender*</label>
                                <select required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]">
                                    <option value="">Choose Gender</option>
                                    <option>Male</option>
                                    <option>Female</option>
                                </select>

                                <label className="text-sm font-semibold mb-1 text-[#333]">Address*</label>
                                <input type="text" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>


                            {/* Guardian details */}
                            <h2 className="text-xl mb-4 text-[#1d3557]">Guardian Contact Details</h2>
                            <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Surname*</label>
                                <input type="text" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Given Name*</label>
                                <input type="text" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Middle Name*</label>
                                <input type="text" className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>

                            <div className="form-group flex-1 min-w-[250px] flex flex-col gap-3">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Cellphone Number*</label>
                                <input type="tel" required className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Email Address</label>
                                <input type="email" className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />

                                <label className="text-sm font-semibold mb-1 text-[#333]">Relation to Student</label>
                                <input type="text" className="py-2.5 px-3 border border-gray-300 rounded-md text-sm outline-none transition-all duration-200 focus:border-[#457b9d]" />
                            </div>

                            {/* File upload */}
                            <div className="upload my-5">
                                <label className="text-sm font-semibold mb-1 text-[#333]">Upload Documents*</label><br />
                                <input type="file" required className="mt-2.5 text-sm" />
                            </div>

                            <button type="submit" className="btn-submit inline-block bg-[#1d3557] text-white border-none py-3 px-5 text-base rounded-lg cursor-pointer transition-all duration-300 hover:bg-[#457b9d]">Submit</button>
                            <nav className="navigation">
                                <br />
                                <a href="application.html" style={{ color: 'blue' }}>Edit Submitted Application</a>
                            </nav>
                        </form>


                    </main>
                </div>
            </div >
        </>
    );
}
