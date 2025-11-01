import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';
import { router } from '@inertiajs/react';
import { FileUp, Upload, X } from 'lucide-react';
import React, { useState } from 'react';

interface Document {
    id: number;
    document_type: string;
    original_filename: string;
    file_size: number;
    upload_date: string;
}

interface DocumentUploadProps {
    studentId: number;
    documentType?: string;
    onSuccess?: (document: Document) => void;
    onError?: (error: string) => void;
}

const DOCUMENT_TYPES = [
    { value: 'birth_certificate', label: 'Birth Certificate' },
    { value: 'report_card', label: 'Report Card' },
    { value: 'form_138', label: 'Form 138' },
    { value: 'good_moral', label: 'Good Moral Certificate' },
    { value: 'other', label: 'Other Document' },
];

const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB in bytes
const ALLOWED_FILE_TYPES = ['image/jpeg', 'image/png'];

export function DocumentUpload({ studentId, documentType: defaultDocumentType, onSuccess, onError }: DocumentUploadProps) {
    const [file, setFile] = useState<File | null>(null);
    const [documentType, setDocumentType] = useState<string>(defaultDocumentType || '');
    const [preview, setPreview] = useState<string | null>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [isUploading, setIsUploading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const { toast } = useToast();
    const fileInputRef = React.useRef<HTMLInputElement>(null);

    const validateFile = (file: File): string | null => {
        if (!ALLOWED_FILE_TYPES.includes(file.type)) {
            return 'Please select a JPEG or PNG image file';
        }
        if (file.size > MAX_FILE_SIZE) {
            return 'File size must be less than 50MB';
        }
        return null;
    };

    const handleFileSelect = (selectedFile: File) => {
        const error = validateFile(selectedFile);
        if (error) {
            toast({
                variant: 'destructive',
                title: 'Invalid File',
                description: error,
            });
            return;
        }

        setFile(selectedFile);

        // Generate preview for images
        if (selectedFile.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                setPreview(e.target?.result as string);
            };
            reader.readAsDataURL(selectedFile);
        } else {
            setPreview(null);
        }
    };

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = () => {
        setIsDragging(false);
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);

        const droppedFile = e.dataTransfer.files[0];
        if (droppedFile) {
            handleFileSelect(droppedFile);
        }
    };

    const handleFileInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const selectedFile = e.target.files?.[0];
        if (selectedFile) {
            handleFileSelect(selectedFile);
        }
    };

    const handleBrowseClick = () => {
        fileInputRef.current?.click();
    };

    const clearFile = () => {
        setFile(null);
        setPreview(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const formatFileSize = (bytes: number): string => {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    };

    const handleUpload = async () => {
        if (!file || !documentType) {
            toast({
                variant: 'destructive',
                title: 'Missing Information',
                description: 'Please select a file and document type',
            });
            return;
        }

        setIsUploading(true);
        setUploadProgress(0);

        const formData = new FormData();
        formData.append('document', file);
        formData.append('document_type', documentType);

        // Simulate progress for better UX
        const progressInterval = setInterval(() => {
            setUploadProgress((prev) => {
                if (prev >= 90) {
                    clearInterval(progressInterval);
                    return 90;
                }
                return prev + 10;
            });
        }, 200);

        router.post(`/guardian/students/${studentId}/documents`, formData, {
            forceFormData: true,
            onSuccess: (page) => {
                clearInterval(progressInterval);
                setUploadProgress(100);
                toast({
                    title: 'Success',
                    description: 'Document uploaded successfully',
                });
                clearFile();
                setIsUploading(false);
                if (onSuccess && page.props.document) {
                    onSuccess(page.props.document as Document);
                }
            },
            onError: (errors) => {
                clearInterval(progressInterval);
                setIsUploading(false);
                setUploadProgress(0);
                const errorMessage = errors.document || errors.document_type || 'Failed to upload document. Please try again.';
                toast({
                    variant: 'destructive',
                    title: 'Upload Failed',
                    description: errorMessage,
                });
                if (onError) {
                    onError(errorMessage);
                }
            },
        });
    };

    return (
        <div className="space-y-4">
            {/* Document Type Selector */}
            <div className="space-y-2">
                <Label htmlFor="document-type">Document Type</Label>
                <Select value={documentType} onValueChange={setDocumentType} disabled={!!defaultDocumentType}>
                    <SelectTrigger id="document-type">
                        <SelectValue placeholder="Select document type" />
                    </SelectTrigger>
                    <SelectContent>
                        {DOCUMENT_TYPES.map((type) => (
                            <SelectItem key={type.value} value={type.value}>
                                {type.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            {/* File Upload Area */}
            <div className="space-y-2">
                <Label>Upload Document</Label>
                <div
                    onDragOver={handleDragOver}
                    onDragLeave={handleDragLeave}
                    onDrop={handleDrop}
                    className={`relative rounded-lg border-2 border-dashed p-8 text-center transition-colors ${
                        isDragging ? 'border-primary bg-primary/5' : 'border-muted-foreground/25 hover:border-muted-foreground/50'
                    }`}
                >
                    <input
                        ref={fileInputRef}
                        type="file"
                        accept=".jpg,.jpeg,.png"
                        onChange={handleFileInputChange}
                        className="hidden"
                        disabled={isUploading}
                    />

                    {!file ? (
                        <div className="flex flex-col items-center gap-2">
                            <FileUp className="h-10 w-10 text-muted-foreground" />
                            <div className="text-sm">
                                <button type="button" onClick={handleBrowseClick} className="font-semibold text-primary hover:underline">
                                    Click to browse
                                </button>
                                {' or drag and drop'}
                            </div>
                            <p className="text-xs text-muted-foreground">JPEG or PNG only (max. 50MB)</p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {/* Preview */}
                            {preview && (
                                <div className="flex justify-center">
                                    <img src={preview} alt="Preview" className="max-h-48 rounded-lg object-contain" />
                                </div>
                            )}

                            {/* File Info */}
                            <div className="flex items-center justify-between rounded-lg border bg-muted p-3">
                                <div className="flex items-center gap-3">
                                    <Upload className="h-5 w-5 text-muted-foreground" />
                                    <div className="text-left">
                                        <p className="text-sm font-medium">{file.name}</p>
                                        <p className="text-xs text-muted-foreground">{formatFileSize(file.size)}</p>
                                    </div>
                                </div>
                                <Button type="button" variant="ghost" size="icon" onClick={clearFile} disabled={isUploading}>
                                    <X className="h-4 w-4" />
                                </Button>
                            </div>

                            {/* Upload Progress */}
                            {isUploading && (
                                <div className="space-y-2">
                                    <Progress value={uploadProgress} className="h-2" />
                                    <p className="text-center text-xs text-muted-foreground">Uploading... {uploadProgress}%</p>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Upload Button */}
            <Button type="button" onClick={handleUpload} disabled={!file || !documentType || isUploading} className="w-full">
                {isUploading ? 'Uploading...' : 'Upload Document'}
            </Button>
        </div>
    );
}
