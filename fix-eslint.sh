#!/bin/bash

# Fix Guardian billing
sed -i 's/export default function GuardianBillingIndex(props: any)/export default function GuardianBillingIndex(props: unknown)/' resources/js/pages/guardian/billing/index.tsx
sed -i 's/export default function GuardianBillingShow(props: any)/export default function GuardianBillingShow(props: unknown)/' resources/js/pages/guardian/billing/show.tsx

# Fix Guardian enrollments
sed -i 's/export default function GuardianEnrollmentsShow({ enrollment }: any)/export default function GuardianEnrollmentsShow({ enrollment }: { enrollment: unknown })/' resources/js/pages/guardian/enrollments/show.tsx
sed -i 's/export default function GuardianEnrollmentsEdit({ enrollment }: any)/export default function GuardianEnrollmentsEdit(props: { enrollment: unknown })/' resources/js/pages/guardian/enrollments/edit.tsx

# Fix Guardian enrollments/index.tsx links type
sed -i 's/links: any/links: unknown/' resources/js/pages/guardian/enrollments/index.tsx
sed -i 's/meta: any/meta: unknown/' resources/js/pages/guardian/enrollments/index.tsx

# Fix Guardian students
sed -i 's/export default function GuardianStudentsIndex(props: any)/export default function GuardianStudentsIndex(props: unknown)/' resources/js/pages/guardian/students/index.tsx
sed -i 's/export default function GuardianStudentsShow(props: any)/export default function GuardianStudentsShow(props: unknown)/' resources/js/pages/guardian/students/show.tsx
sed -i 's/export default function GuardianStudentsCreate(props: any)/export default function GuardianStudentsCreate(props: unknown)/' resources/js/pages/guardian/students/create.tsx
sed -i 's/export default function GuardianStudentsEdit(props: any)/export default function GuardianStudentsEdit(props: unknown)/' resources/js/pages/guardian/students/edit.tsx

# Fix Registrar views
sed -i 's/export default function RegistrarEnrollmentsIndex(props: any)/export default function RegistrarEnrollmentsIndex(props: unknown)/' resources/js/pages/registrar/enrollments/index.tsx
sed -i 's/export default function RegistrarEnrollmentsShow(props: any)/export default function RegistrarEnrollmentsShow(props: unknown)/' resources/js/pages/registrar/enrollments/show.tsx
sed -i 's/export default function RegistrarStudentsIndex(props: any)/export default function RegistrarStudentsIndex(props: unknown)/' resources/js/pages/registrar/students/index.tsx
sed -i 's/export default function RegistrarStudentsShow(props: any)/export default function RegistrarStudentsShow(props: unknown)/' resources/js/pages/registrar/students/show.tsx
sed -i 's/export default function RegistrarStudentsCreate(props: any)/export default function RegistrarStudentsCreate(props: unknown)/' resources/js/pages/registrar/students/create.tsx
sed -i 's/export default function RegistrarStudentsEdit(props: any)/export default function RegistrarStudentsEdit(props: unknown)/' resources/js/pages/registrar/students/edit.tsx

# Fix enrollments/create.tsx
sed -i 's/students: any\[\]/students: unknown[]/' resources/js/pages/guardian/enrollments/create.tsx
sed -i -E 's/\{\s*students,\s*gradeLevels,\s*quarters,\s*currentSchoolYear,\s*selectedStudentId\s*\}/props/' resources/js/pages/guardian/enrollments/create.tsx
