import React, { useState, useRef } from 'react';
import { Upload, X, File, Image as ImageIcon, Check } from 'lucide-react';

interface FileInputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'onChange'> {
    label: string;
    icon?: React.ReactNode;
    error?: string;
    required?: boolean;
    onFileChange?: (file: File | null) => void;
    maxSize?: number; // en MB
    acceptedFormats?: string[];
    showPreview?: boolean;
}

export default function FileInput({
    label,
    icon,
    error,
    required = false,
    onFileChange,
    maxSize = 2,
    acceptedFormats = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'],
    showPreview = true,
    className = '',
    ...props
}: FileInputProps) {
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const validateFile = (file: File): string | null => {
        // Vérifier la taille
        const fileSizeInMB = file.size / (1024 * 1024);
        if (fileSizeInMB > maxSize) {
            return `Le fichier est trop volumineux. Taille maximale: ${maxSize}MB`;
        }

        // Vérifier le format
        if (acceptedFormats.length > 0 && !acceptedFormats.includes(file.type)) {
            return `Format non accepté. Formats autorisés: ${acceptedFormats.map(f => f.split('/')[1].toUpperCase()).join(', ')}`;
        }

        return null;
    };

    const handleFileSelect = (file: File) => {
        const validationError = validateFile(file);

        if (validationError) {
            setUploadError(validationError);
            setSelectedFile(null);
            setPreviewUrl(null);
            if (onFileChange) onFileChange(null);
            return;
        }

        setUploadError(null);
        setSelectedFile(file);

        // Créer l'aperçu si c'est une image
        if (file.type.startsWith('image/') && showPreview) {
            const reader = new FileReader();
            reader.onloadend = () => {
                setPreviewUrl(reader.result as string);
            };
            reader.readAsDataURL(file);
        } else {
            setPreviewUrl(null);
        }

        if (onFileChange) onFileChange(file);
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (file) {
            handleFileSelect(file);
        }
    };

    const handleDragEnter = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);
    };

    const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
    };

    const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        setIsDragging(false);

        const file = e.dataTransfer.files?.[0];
        if (file) {
            handleFileSelect(file);
        }
    };

    const handleRemove = () => {
        setSelectedFile(null);
        setPreviewUrl(null);
        setUploadError(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
        if (onFileChange) onFileChange(null);
    };

    const formatFileSize = (bytes: number): string => {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    };

    return (
        <div className={className}>
            <label className="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                {icon && <span className="mr-2 inline-flex h-4 w-4 text-blue-500 dark:text-blue-400">{icon}</span>}
                {label}
                {required && <span className="ml-1 text-red-500">*</span>}
            </label>

            <div className="space-y-4">
                {/* Zone de téléversement */}
                {!selectedFile && (
                    <div
                        onDragEnter={handleDragEnter}
                        onDragOver={handleDragOver}
                        onDragLeave={handleDragLeave}
                        onDrop={handleDrop}
                        className={`relative rounded-xl border-2 border-dashed transition-all ${
                            isDragging
                                ? 'border-indigo-500 bg-blue-50 dark:border-indigo-400 dark:bg-blue-900/20'
                                : error || uploadError
                                ? 'border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/10'
                                : 'border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-black/50 hover:border-indigo-500 dark:hover:border-indigo-400'
                        } p-8 text-center cursor-pointer`}
                        onClick={() => fileInputRef.current?.click()}
                    >
                        <input
                            ref={fileInputRef}
                            type="file"
                            onChange={handleChange}
                            className="hidden"
                            accept={acceptedFormats.join(',')}
                            {...props}
                        />

                        <div className="flex flex-col items-center justify-center space-y-3">
                            <div className={`rounded-full p-3 ${
                                isDragging
                                    ? 'bg-blue-100 dark:bg-blue-900/40'
                                    : 'bg-gray-100 dark:bg-gray-700'
                            }`}>
                                <Upload className={`h-8 w-8 ${
                                    isDragging
                                        ? 'text-blue-600 dark:text-blue-400'
                                        : 'text-gray-400 dark:text-gray-500'
                                }`} />
                            </div>

                            <div>
                                <p className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {isDragging ? 'Déposez le fichier ici' : 'Cliquez pour téléverser ou glissez-déposez'}
                                </p>
                                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {acceptedFormats.map(f => f.split('/')[1].toUpperCase()).join(', ')} (max. {maxSize}MB)
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Aperçu du fichier sélectionné */}
                {selectedFile && (
                    <div className="rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-black p-4">
                        <div className="flex items-center space-x-4">
                            {/* Aperçu de l'image ou icône de fichier */}
                            <div className="flex-shrink-0">
                                {previewUrl ? (
                                    <div className="relative h-20 w-20 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600">
                                        <img
                                            src={previewUrl}
                                            alt="Aperçu"
                                            className="h-full w-full object-cover"
                                        />
                                    </div>
                                ) : (
                                    <div className="flex h-20 w-20 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600">
                                        <File className="h-8 w-8 text-gray-400 dark:text-gray-500" />
                                    </div>
                                )}
                            </div>

                            {/* Informations du fichier */}
                            <div className="flex-1 min-w-0">
                                <div className="flex items-center space-x-2">
                                    <div className="flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30">
                                        <Check className="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {selectedFile.name}
                                    </p>
                                </div>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {formatFileSize(selectedFile.size)} • {selectedFile.type.split('/')[1].toUpperCase()}
                                </p>
                            </div>

                            {/* Bouton de suppression */}
                            <button
                                type="button"
                                onClick={handleRemove}
                                className="flex-shrink-0 p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                                aria-label="Supprimer le fichier"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                )}

                {/* Messages d'erreur */}
                {(error || uploadError) && (
                    <div className="flex items-start space-x-2 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                        <svg className="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fillRule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clipRule="evenodd"
                            />
                        </svg>
                        <span>{error || uploadError}</span>
                    </div>
                )}
            </div>
        </div>
    );
}
