import * as React from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ResponsiveImage from '@/components/ui/responsive-image';
import { cn } from '@/lib/utils';
import { Upload, X, Image as ImageIcon } from 'lucide-react';

interface ImageUploadProps {
    id?: string;
    label?: string;
    value?: string | File;
    onChange: (file: File | null) => void;
    onRemove?: () => void;
    accept?: string;
    maxSize?: number; // in MB
    required?: boolean;
    disabled?: boolean;
    error?: string;
    className?: string;
    previewClassName?: string;
    uploadAreaClassName?: string;
}

export function ImageUpload({
    id,
    label,
    value,
    onChange,
    onRemove,
    accept = 'image/jpeg,image/jpg,image/png,image/webp',
    maxSize = 5,
    required = false,
    disabled = false,
    error,
    className,
    previewClassName,
    uploadAreaClassName,
}: ImageUploadProps) {
    const [dragActive, setDragActive] = React.useState(false);
    const [preview, setPreview] = React.useState<string | null>(null);
    const inputRef = React.useRef<HTMLInputElement>(null);

    // Generate preview URL when value changes
    React.useEffect(() => {
        if (value instanceof File) {
            const reader = new FileReader();
            reader.onload = (e) => setPreview(e.target?.result as string);
            reader.readAsDataURL(value);
        } else if (typeof value === 'string' && value) {
            setPreview(value);
        } else {
            setPreview(null);
        }
    }, [value]);

    const handleDrag = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === 'dragenter' || e.type === 'dragover') {
            setDragActive(true);
        } else if (e.type === 'dragleave') {
            setDragActive(false);
        }
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        if (disabled || preview) return; // Don't allow drop if image already exists

        const files = e.dataTransfer.files;
        if (files && files[0]) {
            handleFileSelect(files[0]);
        }
    };

    const handleFileSelect = (file: File) => {
        if (disabled) return;

        // Validate file size
        if (file.size > maxSize * 1024 * 1024) {
            alert(`File size must be less than ${maxSize}MB`);
            return;
        }

        // Validate file type
        const acceptedTypes = accept.split(',').map(type => type.trim());
        if (!acceptedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, WebP)');
            return;
        }

        onChange(file);
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            handleFileSelect(file);
        }
    };

    const handleRemove = () => {
        setPreview(null);
        if (inputRef.current) {
            inputRef.current.value = '';
        }
        if (onRemove) {
            onRemove();
        } else {
            onChange(null);
        }
    };

    const openFileDialog = () => {
        if (!disabled && !preview && inputRef.current) {
            inputRef.current.click();
        }
    };

    return (
        <div className={cn('space-y-2', className)}>
            {label && (
                <Label htmlFor={id} className={required ? "after:content-['*'] after:ml-0.5 after:text-destructive" : ''}>
                    {label}
                </Label>
            )}

            <div className="min-h-[120px]">
                {preview ? (
                    // Show image preview when image is selected
                    <div className={cn('relative group flex items-center justify-center min-h-[120px]', previewClassName)}>
                        <div className="relative">
                            <ResponsiveImage
                                src={preview}
                                alt="Preview"
                                aspectRatio="square"
                                className="w-32 h-32 rounded-lg border border-border shadow-sm"
                            />
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                                className="absolute -top-2 -right-2 h-6 w-6 rounded-full p-0 opacity-0 group-hover:opacity-100 transition-opacity"
                                onClick={handleRemove}
                                disabled={disabled}
                            >
                                <X className="h-3 w-3" />
                            </Button>
                        </div>
                    </div>
                ) : (
                    // Show upload area when no image is selected
                    <div
                        className={cn(
                            'relative border-2 border-dashed rounded-lg transition-all duration-200 cursor-pointer min-h-[120px] flex items-center justify-center',
                            dragActive
                                ? 'border-primary bg-primary/5'
                                : 'border-border hover:border-primary/50 hover:bg-muted/30',
                            disabled && 'opacity-50 cursor-not-allowed',
                            error && 'border-destructive',
                            uploadAreaClassName
                        )}
                        onDragEnter={handleDrag}
                        onDragLeave={handleDrag}
                        onDragOver={handleDrag}
                        onDrop={handleDrop}
                        onClick={openFileDialog}
                    >
                        <div className="flex flex-col items-center justify-center p-4 text-center">
                            <div className="mb-3">
                                {dragActive ? (
                                    <Upload className="w-8 h-8 text-primary animate-bounce" />
                                ) : (
                                    <ImageIcon className="w-8 h-8 text-muted-foreground" />
                                )}
                            </div>
                            
                            <div className="space-y-1">
                                <p className="text-sm font-medium text-foreground">
                                    {dragActive ? 'Drop image here' : 'Click to upload or drag and drop'}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    JPEG, PNG, or WebP (max {maxSize}MB)
                                </p>
                            </div>
                        </div>

                        <Input
                            ref={inputRef}
                            id={id}
                            type="file"
                            accept={accept}
                            onChange={handleInputChange}
                            disabled={disabled}
                            className="sr-only"
                        />
                    </div>
                )}
            </div>

            {error && (
                <p className="text-sm text-destructive">{error}</p>
            )}
        </div>
    );
}

export default ImageUpload;