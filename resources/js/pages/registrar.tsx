import Sidebar from '../components/Sidebar';

export default function Registrar() {
    return (
        <>
            <div className="m-0 bg-[#eef2f5] p-0 font-['Segoe_UI',_Tahoma,_Verdana,_sans-serif] text-[#333]">
                <div className="flex min-h-screen">
                    {/* Sidebar */}
                    <Sidebar currentPage="registrar" />

                    <div className="flex flex-grow flex-col px-12 py-7">
                        <div className="top-bar mb-5 flex items-center justify-between border-b-2 border-gray-300 py-4">
                            <h1 id="pageTitle" className="m-0 text-3xl text-[#2c3e50]">
                                REGISTRAR
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

                        <div id="contentArea">
                            <div className="mb-7 grid grid-cols-2 gap-7">
                                <div
                                    className="flex min-h-[300px] items-center justify-center rounded-xl bg-[#d1d8e0] bg-cover bg-center bg-no-repeat text-lg text-[#7f8c8d] shadow-[inset_0_2px_4px_rgba(0,0,0,0.1)]"
                                    style={{ backgroundImage: "url('ra_2022-06-19_22-17-45.jpg')" }}
                                ></div>
                                <div
                                    className="rounded-xl bg-cover bg-center bg-no-repeat"
                                    style={{ backgroundImage: "url('ra_2022-06-19_22-17-45.jpg')" }}
                                ></div>
                            </div>

                            <div className="col-span-2 rounded-xl bg-white p-6 shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                                <h2 className="mt-0 flex items-center justify-between border-b-2 border-[#ecf0f1] pb-2.5 text-2xl text-[#2c3e50]">
                                    Schedules
                                    <i className="fas fa-calendar-alt"></i>
                                </h2>
                                <div className="mt-5 grid grid-cols-2 gap-7">
                                    <div className="rounded-lg bg-[#f8f9fa] p-5 shadow-[inset_0_1px_3px_rgba(0,0,0,0.05)]">
                                        <h3 className="m-0 mb-4 text-base text-[#7f8c8d] uppercase">SCHOOL EVENTS</h3>
                                        <ul className="m-0 list-none p-0">
                                            <li className="flex items-center border-b border-[#eef2f5] py-4 transition-all duration-200 ease-in-out">
                                                <i className="fas fa-bullhorn mr-5 text-xl text-[#3498db]"></i>
                                                <span className="text-base font-medium">START OF CLASSES:</span>
                                            </li>
                                            <li className="flex items-center border-b border-[#eef2f5] py-4 transition-all duration-200 ease-in-out">
                                                <i className="fas fa-users-cog mr-5 text-xl text-[#3498db]"></i>
                                                <span className="text-base font-medium">PARENT ORIENTATION:</span>
                                            </li>
                                            <li className="flex items-center border-b border-[#eef2f5] py-4 transition-all duration-200 ease-in-out">
                                                <i className="fas fa-building mr-5 text-xl text-[#3498db]"></i>
                                                <span className="text-base font-medium">FOUNDATION DAY:</span>
                                            </li>
                                            <li className="flex items-center border-b border-[#eef2f5] py-4 transition-all duration-200 ease-in-out">
                                                <i className="fas fa-gifts mr-5 text-xl text-[#3498db]"></i>
                                                <span className="text-base font-medium">CHRISTMAS PROGRAM:</span>
                                            </li>
                                            <li className="flex items-center border-b-0 py-4">
                                                <i className="fas fa-award mr-5 text-xl text-[#3498db]"></i>
                                                <span className="text-base font-medium">GRADUATION DAY:</span>
                                            </li>
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
