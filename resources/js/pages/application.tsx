import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, Link } from '@inertiajs/react';
import { Calendar, Mail, MapPin, Phone, Save, Upload, User, UserCheck, Users } from 'lucide-react';
import PageLayout from '../components/PageLayout';

export default function Application() {
    return (
        <>
            <Head title="Application" />
            <PageLayout title="ENROLL > View My Application" currentPage="application">
                <div className="mx-auto max-w-4xl">
                    <div className="mb-6">
                        <h2 className="mb-2 text-2xl font-bold">Edit Application</h2>
                        <p className="text-muted-foreground">
                            Please fill out all required fields accurately. Your application will be reviewed once submitted.
                        </p>
                    </div>

                    <form id="applicationForm" className="space-y-8">
                        {/* Student Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <User className="h-5 w-5 text-primary" />
                                    Student Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="gradeLevel">Grade Level *</Label>
                                        <Select required>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Choose Grade" />
                                            </SelectTrigger>
                                            <SelectContent>
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
                                        <Label htmlFor="lrn">LRN Number *</Label>
                                        <Input id="lrn" type="text" placeholder="Enter LRN Number" required />
                                    </div>
                                </div>

                                <div className="grid gap-6 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="surname">Surname *</Label>
                                        <Input id="surname" type="text" placeholder="Enter surname" required />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="givenName">Given Name *</Label>
                                        <Input id="givenName" type="text" placeholder="Enter given name" required />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="middleName">Middle Name</Label>
                                        <Input id="middleName" type="text" placeholder="Enter middle name" />
                                    </div>
                                </div>

                                <div className="grid gap-6 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="birthDate">Date of Birth *</Label>
                                        <div className="relative">
                                            <Input id="birthDate" type="date" required />
                                            <Calendar className="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="gender">Gender *</Label>
                                        <Select required>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Choose Gender" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="male">Male</SelectItem>
                                                <SelectItem value="female">Female</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="address">Address *</Label>
                                        <div className="relative">
                                            <Input id="address" type="text" placeholder="Enter address" required />
                                            <MapPin className="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Guardian Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <UserCheck className="h-5 w-5 text-primary" />
                                    Guardian Contact Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-6 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="guardianSurname">Surname *</Label>
                                        <Input id="guardianSurname" type="text" placeholder="Enter guardian surname" required />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="guardianGivenName">Given Name *</Label>
                                        <Input id="guardianGivenName" type="text" placeholder="Enter guardian given name" required />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="guardianMiddleName">Middle Name</Label>
                                        <Input id="guardianMiddleName" type="text" placeholder="Enter guardian middle name" />
                                    </div>
                                </div>

                                <div className="grid gap-6 md:grid-cols-3">
                                    <div className="space-y-2">
                                        <Label htmlFor="cellphone">Cellphone Number *</Label>
                                        <div className="relative">
                                            <Input id="cellphone" type="tel" placeholder="Enter phone number" required />
                                            <Phone className="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="email">Email Address</Label>
                                        <div className="relative">
                                            <Input id="email" type="email" placeholder="Enter email address" />
                                            <Mail className="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="relation">Relation to Student</Label>
                                        <div className="relative">
                                            <Input id="relation" type="text" placeholder="e.g., Father, Mother" />
                                            <Users className="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Document Upload */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Upload className="h-5 w-5 text-primary" />
                                    Document Upload
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="documents">Upload Required Documents *</Label>
                                        <div className="rounded-lg border-2 border-dashed border-muted-foreground/25 p-8 text-center transition-colors hover:border-muted-foreground/50">
                                            <Upload className="mx-auto mb-4 h-12 w-12 text-muted-foreground/50" />
                                            <div className="space-y-2">
                                                <p className="text-sm text-muted-foreground">Click to upload or drag and drop</p>
                                                <p className="text-xs text-muted-foreground">Supported formats: JPG, PNG, PDF (Max 50MB)</p>
                                            </div>
                                            <Input id="documents" type="file" required className="hidden" multiple accept=".jpg,.jpeg,.png,.pdf" />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                className="mt-4"
                                                onClick={() => document.getElementById('documents')?.click()}
                                            >
                                                Choose Files
                                            </Button>
                                        </div>
                                    </div>

                                    <div className="text-xs text-muted-foreground">
                                        <p className="mb-2 font-medium">Required Documents:</p>
                                        <ul className="ml-4 space-y-1">
                                            <li>• Birth Certificate</li>
                                            <li>• Report Cards from previous school</li>
                                            <li>• Form 138 (if transferring)</li>
                                            <li>• Good Moral Certificate</li>
                                        </ul>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Submit Section */}
                        <Card>
                            <CardContent className="pt-6">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm text-muted-foreground">Please review all information before saving.</p>
                                        <p className="mt-1 text-xs text-muted-foreground">Fields marked with * are required.</p>
                                    </div>
                                    <div className="flex gap-3">
                                        <Button variant="outline" asChild>
                                            <Link href="/enrollment">Cancel</Link>
                                        </Button>
                                        <Button type="submit" className="gap-2">
                                            <Save className="h-4 w-4" />
                                            Save Application
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </PageLayout>
        </>
    );
}
