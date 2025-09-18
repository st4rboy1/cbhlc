import { Link } from '@inertiajs/react';

interface SidebarProps {
    currentPage?: string;
}

export default function Sidebar({ currentPage = '' }: SidebarProps) {
    const isActive = (page: string) => currentPage === page;

    return (
        <aside className="flex w-[280px] flex-col justify-between bg-[#2c3e50] py-5 text-[#ecf0f1] shadow-[2px_0_10px_rgba(0,0,0,0.15)]">
            <div className="pt-2.5 pb-7 text-center">
                <img src="/ra_2022-06-19_22-17-45.jpg" alt="CBHLC Logo" className="mx-auto h-auto w-[120px] rounded-full border-3 border-[#3498db]" />
            </div>

            <ul className="m-0 list-none p-0">
                <Link
                    href="/dashboard"
                    className={`nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out ${
                        isActive('dashboard')
                            ? 'scale-105 bg-[#34495e] text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)]'
                            : 'hover:bg-[#34495e] hover:text-white'
                    }`}
                >
                    <i
                        className={`fas fa-chart-line mr-5 text-xl transition-colors duration-300 ease-in-out ${
                            isActive('dashboard') ? 'text-[#3498db]' : 'text-[#bdc3c7]'
                        }`}
                    ></i>
                    <span className="font-medium">DASHBOARD</span>
                </Link>

                <Link
                    href="/enrollment"
                    className={`nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out ${
                        isActive('enrollment')
                            ? 'scale-105 bg-[#34495e] text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)]'
                            : 'hover:bg-[#34495e] hover:text-white'
                    }`}
                >
                    <i
                        className={`fas fa-user-plus mr-5 text-xl transition-colors duration-300 ease-in-out ${
                            isActive('enrollment') ? 'text-[#3498db]' : 'text-[#bdc3c7]'
                        }`}
                    ></i>
                    <span className="font-medium">ENROLLMENT</span>
                </Link>

                <div className="nav-item has-dropdown mx-4 my-1">
                    <Link
                        href="/tuition"
                        className={`flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out ${
                            isActive('tuition') || isActive('invoice')
                                ? 'scale-105 bg-[#34495e] text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)]'
                                : 'hover:bg-[#34495e] hover:text-white'
                        }`}
                    >
                        <i
                            className={`fas fa-file-invoice mr-5 text-xl transition-colors duration-300 ease-in-out ${
                                isActive('tuition') || isActive('invoice') ? 'text-[#3498db]' : 'text-[#bdc3c7]'
                            }`}
                        ></i>
                        <span className="font-medium">BILLING</span>
                    </Link>
                    <div className="ml-12 space-y-1">
                        <Link href="/tuition" className="block rounded px-4 py-2 text-sm hover:bg-[#34495e] hover:text-white">
                            Tuition Fee
                        </Link>
                        <Link href="/invoice" className="block rounded px-4 py-2 text-sm hover:bg-[#34495e] hover:text-white">
                            Generate Invoice
                        </Link>
                    </div>
                </div>

                <Link
                    href="/studentreport"
                    className={`nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out ${
                        isActive('studentreport')
                            ? 'scale-105 bg-[#34495e] text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)]'
                            : 'hover:bg-[#34495e] hover:text-white'
                    }`}
                >
                    <i
                        className={`fas fa-book mr-5 text-xl transition-colors duration-300 ease-in-out ${
                            isActive('studentreport') ? 'text-[#3498db]' : 'text-[#bdc3c7]'
                        }`}
                    ></i>
                    <span className="font-medium">STUDENT REPORT</span>
                </Link>

                <div className="nav-item has-dropdown mx-4 my-1">
                    <Link
                        href="/registrar"
                        className={`flex cursor-pointer items-center rounded-lg px-6 py-4 transition-all duration-300 ease-in-out ${
                            isActive('registrar')
                                ? 'scale-105 bg-[#34495e] text-white shadow-[0_4px_6px_rgba(0,0,0,0.2)]'
                                : 'hover:bg-[#34495e] hover:text-white'
                        }`}
                    >
                        <i
                            className={`fas fa-building mr-5 text-xl transition-colors duration-300 ease-in-out ${
                                isActive('registrar') ? 'text-[#3498db]' : 'text-[#bdc3c7]'
                            }`}
                        ></i>
                        <span className="font-medium">REGISTRAR</span>
                    </Link>
                    <div className="ml-12 space-y-1">
                        <Link href="/registrar" className="block rounded px-4 py-2 text-sm hover:bg-[#34495e] hover:text-white">
                            Student Records
                        </Link>
                        <Link href="/registrar" className="block rounded px-4 py-2 text-sm hover:bg-[#34495e] hover:text-white">
                            Enrollment List
                        </Link>
                        <Link href="/registrar" className="block rounded px-4 py-2 text-sm hover:bg-[#34495e] hover:text-white">
                            Clearances
                        </Link>
                    </div>
                </div>
            </ul>

            <div className="logout mt-auto w-[280px]">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    className="nav-item mx-4 my-1 flex cursor-pointer items-center rounded-lg bg-[#f5f5f5] px-6 py-4 text-black transition-all duration-300 ease-in-out hover:bg-[#c0392b] hover:text-white"
                >
                    <i className="fas fa-sign-out-alt mr-5 text-xl text-[#bdc3c7] transition-colors duration-300 ease-in-out"></i>
                    <span className="font-medium">LOGOUT</span>
                </Link>
            </div>
        </aside>
    );
}
