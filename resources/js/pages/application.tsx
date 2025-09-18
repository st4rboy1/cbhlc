import { Link } from '@inertiajs/react';
import Sidebar from '../components/Sidebar';

export default function Application() {
    return (
        <>
            <div className="m-0 bg-[#eef2f5] p-0 font-['Segoe_UI',Tahoma,Geneva,Verdana,sans-serif] text-[#333]">
                <div className="flex min-h-screen">
                    {/* Sidebar */}
                    <Sidebar currentPage="application" />
                    {/*NAV SIDE BAR, SAME LANG TO SA LAHAT NG SIDES  */}
                    {/* Enrollment Main Content */}

                    <main className="flex flex-grow flex-col px-12 py-7">
                        <div className="top-bar mb-0 flex items-center justify-between border-b-2 border-gray-300 py-4">
                            <h1 className="m-0 text-3xl text-[#2c3e50]">ENROLL &gt; View My Application</h1>
                            <div className="user-info flex items-center">
                                <button className="icon-btn ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300">
                                    <i className="fas fa-bell"></i>
                                </button>
                                <span className="mr-5 font-medium text-[#555]">Welcome, Bronny!</span>
                                <button className="icon-btn ml-4 cursor-pointer border-none bg-transparent text-2xl text-[#95a5a6] transition-colors duration-300">
                                    <i className="fas fa-user-circle"></i>
                                </button>
                            </div>
                        </div>

                        <hr className="my-4 border-b-2 border-none border-gray-300" />
                        <h2 className="mb-4 text-2xl font-semibold">Edit Application</h2>

                        <form id="applicationForm" className="rounded-xl bg-white px-7 py-6 shadow-[0_4px_12px_rgba(0,0,0,0.1)]">
                            {/* Student details */}
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
                            </div>

                            {/* Guardian details */}
                            <h2 className="mb-4 text-2xl font-semibold">Guardian Contact Details</h2>
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

                            <nav className="navigation">
                                <Link href="/enrollment" style={{ fontSize: '20px', color: 'blue' }}>
                                    Save
                                </Link>
                            </nav>
                        </form>
                    </main>
                </div>
                {/* The script.js script would be handled by React's build process or public/index.html */}
                {/* <script src="script.js"></script> */}
            </div>
        </>
    );
}
