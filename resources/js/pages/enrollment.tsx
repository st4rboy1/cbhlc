import PageLayout from '@/components/PageLayout';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Head } from '@inertiajs/react';
import { Upload } from 'lucide-react';
import { useState } from 'react';

export default function Enrollment() {
    const [activeTab, setActiveTab] = useState('student-info');
    const [uploadedFiles, setUploadedFiles] = useState<{ [key: string]: File | null }>({
        birthCertificate: null,
        reportCard: null,
        form138: null,
        goodMoral: null,
    });

    const handleFileUpload = (fileType: string, file: File | null) => {
        setUploadedFiles((prev) => ({
            ...prev,
            [fileType]: file,
        }));
    };

    return (
        <>
            <Head title="Enrollment" />
            <PageLayout title="ENROLLMENT" currentPage="enrollment">
                <div className="space-y-6">
                    {/* Progress Indicator */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Enrollment Progress</CardTitle>
                            <CardDescription>Complete all sections to submit your enrollment</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <Badge variant={activeTab === 'student-info' ? 'default' : 'secondary'}>1. Student Information</Badge>
                                <Separator className="w-8" />
                                <Badge variant={activeTab === 'guardian-info' ? 'default' : 'secondary'}>2. Guardian Information</Badge>
                                <Separator className="w-8" />
                                <Badge variant={activeTab === 'academic' ? 'default' : 'secondary'}>3. Academic Details</Badge>
                                <Separator className="w-8" />
                                <Badge variant={activeTab === 'documents' ? 'default' : 'secondary'}>4. Documents</Badge>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Main Enrollment Form */}
                    <Card>
                        <CardContent className="p-6">
                            <Tabs value={activeTab} onValueChange={setActiveTab}>
                                <TabsList className="grid w-full grid-cols-4">
                                    <TabsTrigger value="student-info">Student Info</TabsTrigger>
                                    <TabsTrigger value="guardian-info">Guardian Info</TabsTrigger>
                                    <TabsTrigger value="academic">Academic</TabsTrigger>
                                    <TabsTrigger value="documents">Documents</TabsTrigger>
                                </TabsList>

                                {/* Student Information Tab */}
                                <TabsContent value="student-info" className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="firstName">First Name</Label>
                                            <Input id="firstName" placeholder="Enter first name" />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="lastName">Last Name</Label>
                                            <Input id="lastName" placeholder="Enter last name" />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="middleName">Middle Name</Label>
                                            <Input id="middleName" placeholder="Enter middle name" />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="birthDate">Birth Date</Label>
                                            <Input id="birthDate" type="date" />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="gender">Gender</Label>
                                            <Select>
                                                <SelectTrigger id="gender">
                                                    <SelectValue placeholder="Select gender" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="male">Male</SelectItem>
                                                    <SelectItem value="female">Female</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="nationality">Nationality</Label>
                                            <Input id="nationality" placeholder="Enter nationality" />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="address">Complete Address</Label>
                                        <Input id="address" placeholder="Enter complete address" />
                                    </div>

                                    <div className="flex justify-end">
                                        <Button onClick={() => setActiveTab('guardian-info')}>Next: Guardian Information</Button>
                                    </div>
                                </TabsContent>

                                {/* Guardian Information Tab */}
                                <TabsContent value="guardian-info" className="space-y-4">
                                    <div className="space-y-4">
                                        <h3 className="text-lg font-semibold">Father's Information</h3>
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="fatherName">Full Name</Label>
                                                <Input id="fatherName" placeholder="Enter father's name" />
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="fatherOccupation">Occupation</Label>
                                                <Input id="fatherOccupation" placeholder="Enter occupation" />
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="fatherPhone">Contact Number</Label>
                                                <Input id="fatherPhone" type="tel" placeholder="Enter phone number" />
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="fatherEmail">Email Address</Label>
                                                <Input id="fatherEmail" type="email" placeholder="Enter email" />
                                            </div>
                                        </div>

                                        <Separator className="my-4" />

                                        <h3 className="text-lg font-semibold">Mother's Information</h3>
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <Label htmlFor="motherName">Full Name</Label>
                                                <Input id="motherName" placeholder="Enter mother's name" />
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="motherOccupation">Occupation</Label>
                                                <Input id="motherOccupation" placeholder="Enter occupation" />
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="motherPhone">Contact Number</Label>
                                                <Input id="motherPhone" type="tel" placeholder="Enter phone number" />
                                            </div>
                                            <div className="space-y-2">
                                                <Label htmlFor="motherEmail">Email Address</Label>
                                                <Input id="motherEmail" type="email" placeholder="Enter email" />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex justify-between">
                                        <Button variant="outline" onClick={() => setActiveTab('student-info')}>
                                            Previous
                                        </Button>
                                        <Button onClick={() => setActiveTab('academic')}>Next: Academic Details</Button>
                                    </div>
                                </TabsContent>

                                {/* Academic Information Tab */}
                                <TabsContent value="academic" className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="gradeLevel">Grade Level</Label>
                                            <Select>
                                                <SelectTrigger id="gradeLevel">
                                                    <SelectValue placeholder="Select grade level" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="kindergarten">Kindergarten</SelectItem>
                                                    <SelectItem value="grade1">Grade 1</SelectItem>
                                                    <SelectItem value="grade2">Grade 2</SelectItem>
                                                    <SelectItem value="grade3">Grade 3</SelectItem>
                                                    <SelectItem value="grade4">Grade 4</SelectItem>
                                                    <SelectItem value="grade5">Grade 5</SelectItem>
                                                    <SelectItem value="grade6">Grade 6</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="schoolYear">School Year</Label>
                                            <Input id="schoolYear" placeholder="e.g., 2024-2025" />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="previousSchool">Previous School</Label>
                                            <Input id="previousSchool" placeholder="Enter previous school name" />
                                        </div>
                                        <div className="space-y-2">
                                            <Label htmlFor="previousGrade">Previous Grade Level</Label>
                                            <Input id="previousGrade" placeholder="Enter previous grade level" />
                                        </div>
                                    </div>

                                    <div className="flex justify-between">
                                        <Button variant="outline" onClick={() => setActiveTab('guardian-info')}>
                                            Previous
                                        </Button>
                                        <Button onClick={() => setActiveTab('documents')}>Next: Upload Documents</Button>
                                    </div>
                                </TabsContent>

                                {/* Documents Tab */}
                                <TabsContent value="documents" className="space-y-4">
                                    <Alert>
                                        <AlertDescription>
                                            Please upload clear, readable copies of the required documents in JPEG or PNG format (max 50MB per file).
                                        </AlertDescription>
                                    </Alert>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Card>
                                            <CardHeader className="pb-3">
                                                <CardTitle className="text-base">Birth Certificate</CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div className="space-y-2">
                                                    <Label htmlFor="birthCert" className="cursor-pointer">
                                                        <div className="flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-6 hover:border-muted-foreground/50">
                                                            <div className="text-center">
                                                                <Upload className="mx-auto h-8 w-8 text-muted-foreground" />
                                                                <p className="mt-2 text-sm text-muted-foreground">
                                                                    {uploadedFiles.birthCertificate
                                                                        ? uploadedFiles.birthCertificate.name
                                                                        : 'Click to upload'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </Label>
                                                    <Input
                                                        id="birthCert"
                                                        type="file"
                                                        className="hidden"
                                                        accept=".jpg,.jpeg,.png"
                                                        onChange={(e) => handleFileUpload('birthCertificate', e.target.files?.[0] || null)}
                                                    />
                                                </div>
                                            </CardContent>
                                        </Card>

                                        <Card>
                                            <CardHeader className="pb-3">
                                                <CardTitle className="text-base">Report Card</CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div className="space-y-2">
                                                    <Label htmlFor="reportCard" className="cursor-pointer">
                                                        <div className="flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-6 hover:border-muted-foreground/50">
                                                            <div className="text-center">
                                                                <Upload className="mx-auto h-8 w-8 text-muted-foreground" />
                                                                <p className="mt-2 text-sm text-muted-foreground">
                                                                    {uploadedFiles.reportCard ? uploadedFiles.reportCard.name : 'Click to upload'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </Label>
                                                    <Input
                                                        id="reportCard"
                                                        type="file"
                                                        className="hidden"
                                                        accept=".jpg,.jpeg,.png"
                                                        onChange={(e) => handleFileUpload('reportCard', e.target.files?.[0] || null)}
                                                    />
                                                </div>
                                            </CardContent>
                                        </Card>

                                        <Card>
                                            <CardHeader className="pb-3">
                                                <CardTitle className="text-base">Form 138</CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div className="space-y-2">
                                                    <Label htmlFor="form138" className="cursor-pointer">
                                                        <div className="flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-6 hover:border-muted-foreground/50">
                                                            <div className="text-center">
                                                                <Upload className="mx-auto h-8 w-8 text-muted-foreground" />
                                                                <p className="mt-2 text-sm text-muted-foreground">
                                                                    {uploadedFiles.form138 ? uploadedFiles.form138.name : 'Click to upload'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </Label>
                                                    <Input
                                                        id="form138"
                                                        type="file"
                                                        className="hidden"
                                                        accept=".jpg,.jpeg,.png"
                                                        onChange={(e) => handleFileUpload('form138', e.target.files?.[0] || null)}
                                                    />
                                                </div>
                                            </CardContent>
                                        </Card>

                                        <Card>
                                            <CardHeader className="pb-3">
                                                <CardTitle className="text-base">Good Moral Certificate</CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                <div className="space-y-2">
                                                    <Label htmlFor="goodMoral" className="cursor-pointer">
                                                        <div className="flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/25 p-6 hover:border-muted-foreground/50">
                                                            <div className="text-center">
                                                                <Upload className="mx-auto h-8 w-8 text-muted-foreground" />
                                                                <p className="mt-2 text-sm text-muted-foreground">
                                                                    {uploadedFiles.goodMoral ? uploadedFiles.goodMoral.name : 'Click to upload'}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </Label>
                                                    <Input
                                                        id="goodMoral"
                                                        type="file"
                                                        className="hidden"
                                                        accept=".jpg,.jpeg,.png"
                                                        onChange={(e) => handleFileUpload('goodMoral', e.target.files?.[0] || null)}
                                                    />
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>

                                    <div className="flex justify-between">
                                        <Button variant="outline" onClick={() => setActiveTab('academic')}>
                                            Previous
                                        </Button>
                                        <Button>Submit Enrollment Application</Button>
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>
                </div>
            </PageLayout>
        </>
    );
}
