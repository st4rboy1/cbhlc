import Sidebar from '../components/Sidebar';

export default function Tuition() {
    return (
        <>
            <div className="m-0 bg-[#eef2f5] p-0 font-['Segoe_UI',_Tahoma,_Verdana,_sans-serif] text-black">
                <div className="flex min-h-screen">
                    {/* Sidebar */}
                    <Sidebar currentPage="tuition" />

                    <div className="flex flex-grow flex-col px-12 py-7">
                        <div className="top-bar mb-0 flex items-center justify-between border-b-2 border-gray-300 py-4">
                            <h1 id="pageTitle" className="m-0 text-3xl text-[#2c3e50]">
                                TUITION
                            </h1>
                            <div className="user-info flex items-center">
                                <button
                                    className="icon-btn ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300"
                                    id="openModal"
                                >
                                    <i className="fas fa-bell" style={{ color: 'gray' }}></i>
                                    <span className="badge absolute top-[-5px] right-[-5px] rounded-full bg-red-500 px-1.5 py-0.5 text-xs text-white">
                                        3
                                    </span>
                                </button>
                                <span className="mr-5 font-medium text-[#555]">Welcome, Bronny!</span>
                                <button className="icon-btn ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300">
                                    <i className="fas fa-user-circle"></i>
                                </button>

                                {/* Notification Dropdown */}
                                <div
                                    className="notification-dropdown absolute top-[50px] right-0 z-[1000] hidden w-[350px] rounded-xl bg-white p-4 shadow-[0_4px_12px_rgba(0,0,0,0.15)]"
                                    id="notificationDropdown"
                                >
                                    <h3 className="m-0 mb-2.5 border-b border-gray-300 pb-2 text-lg text-[#2c3e50]">Notifications</h3>
                                    <div className="my-3 flex items-start border-b border-gray-100 pb-3">
                                        <img src="ra_2022-06-19_22-17-45.jpg" alt="User" className="mr-3 h-10 w-10 flex-shrink-0 rounded-full" />
                                        <div className="notification-text flex-1 text-sm text-[#333]">
                                            <strong className="text-[#2c3e50]">Bronny James</strong> added you to the folder{' '}
                                            <b>Web App Designs 2019</b>
                                            <div className="time my-1 mb-1.5 text-xs text-[#777]">Today at 12:28 PM</div>
                                        </div>
                                    </div>

                                    <div className="my-3 flex items-start border-b border-gray-100 pb-3">
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

                            <div className="repContent my-5 max-w-4xl rounded-lg bg-[#f9f9f9] p-5 shadow-[0_4px_10px_rgba(0,0,0,0.1)]">
                                <h1 className="text-[#2c3e50] underline">READY FOR PAYMENT!</h1>
                                <p>Visit cashier on-site to finish the process.</p>
                                <h3 className="underline">
                                    {' '}
                                    <u>Student Information</u>
                                </h3>
                                <p>
                                    {' '}
                                    <b>Age: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Gender: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Section: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Birthdate: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Student Name: </b>Manero Sj Rodriguez.
                                </p>

                                <h3 className="underline">
                                    {' '}
                                    <u>Student Information</u>
                                </h3>
                                <p>
                                    {' '}
                                    <b>Age: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Gender: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Section: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Birthdate: </b>Manero Sj Rodriguez.
                                </p>
                                <p>
                                    {' '}
                                    <b>Student Name: </b>Manero Sj Rodriguez.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
