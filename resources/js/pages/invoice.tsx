export default function Invoice() {
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
                    </aside>
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
                                        <strong className="text-[#2c3e50]">Bronny James</strong> added you to the folder <b>Web App Designs 2019</b>
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
                    <div className="invoice-container mt-5 rounded-xl bg-white p-6 shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                        <div className="invoice-header mb-6 flex items-start justify-between">
                            <div className="invoice-logo">
                                <img src="ra_2022-06-19_22-17-45.jpg" alt="CBHLC Logo" className="logo h-24 w-24 rounded-full" />
                            </div>
                            <div className="school-info text-right">
                                <h3 className="text-xl font-bold text-[#2c3e50]">Christian Bible Heritage Learning Center</h3>
                                <p>123 School St, City, Country</p>
                                <p>Phone: (02) 123-4567</p>
                                <p>Email: info@cbhlc.edu</p>
                            </div>
                            <div className="invoice-details mb-6 flex items-start justify-between">
                                <div className="student-info">
                                    <p>
                                        <strong>Billed To:</strong>
                                    </p>
                                    <p>Bronny James</p>
                                    <p>Grade Level: Grade 2</p>
                                </div>
                                <div className="invoice-meta text-right">
                                    <p>
                                        <strong>Invoice #:</strong> INV-00123
                                    </p>
                                    <p>
                                        <strong>Invoice Date:</strong> October 20, 2023
                                    </p>
                                    <p>
                                        <strong>Due Date:</strong> October 31, 2023
                                    </p>
                                </div>

                                <table className="invoice-table w-full border-collapse">
                                    <thead>
                                        <tr className="bg-gray-100">
                                            <th className="p-3 text-left font-semibold text-gray-600">Description</th>
                                            <th className="p-3 text-right font-semibold text-gray-600">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr className="border-b">
                                            <td className="p-3">Tuition Fee (Monthly)</td>
                                            <td className="p-3 text-right">₱10,000.00</td>
                                        </tr>
                                        <tr className="border-b">
                                            <td className="p-3">Miscellaneous Fee</td>
                                            <td className="p-3 text-right">₱3,000.00</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colSpan={1} className="p-3 text-right font-semibold">
                                                Subtotal
                                            </td>
                                            <td className="p-3 text-right">₱13,000.00</td>
                                        </tr>
                                        <tr>
                                            <td colSpan={1} className="p-3 text-right font-semibold">
                                                Less: Discounts & Rebates
                                            </td>
                                            <td className="p-3 text-right">₱0.00</td>
                                        </tr>
                                        <tr>
                                            <td colSpan={1} className="total-amount p-3 text-right text-xl font-bold text-[#2c3e50]">
                                                TOTAL DUE
                                            </td>
                                            <td className="total-amount p-3 text-right text-xl font-bold text-[#2c3e50]">₱13,000.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div className="terms-notes mt-6">
                                <h4 className="mb-2 text-lg font-semibold">Payment Instructions:</h4>
                                <p>
                                    Please pay the total amount by the due date to avoid penalties. Payments can be made via online transfer or bank
                                    deposit to our official school accounts.
                                </p>
                                <p>Thank you for your prompt payment!</p>
                            </div>

                            <div className="invoice-actions mt-6 text-right">
                                <button className="print-btn mr-2 rounded-lg bg-[#3498db] px-4 py-2 text-white hover:bg-[#2980b9]">
                                    <i className="fas fa-print mr-2"></i> Print
                                </button>
                                <button className="download-btn rounded-lg bg-[#2ecc71] px-4 py-2 text-white hover:bg-[#27ae60]">
                                    <i className="fas fa-download mr-2"></i> Download
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
