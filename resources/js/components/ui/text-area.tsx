import React from 'react';

interface TextAreaProps {
    label: string;
    id: string;
    value: string;
    onChange: (e: React.ChangeEvent<HTMLTextAreaElement>) => void;
    placeholder?: string;
    rows?: number;
    error?: string;
    required?: boolean;
    className?: string;
    disabled?: boolean;
    maxLength?: number;
    helperText?: string;
}

const TextArea: React.FC<TextAreaProps> = ({
    label,
    id,
    value,
    onChange,
    placeholder = '',
    rows = 4,
    error,
    required = false,
    className = '',
    disabled = false,
    maxLength,
    helperText,
}) => {
    const characterCount = value?.length || 0;
    const showCount = maxLength !== undefined;

    return (
        <div className={className}>
            <label
                htmlFor={id}
                className="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
                {label}
                {required && <span className="ml-1 text-red-500">*</span>}
            </label>

            <div className="relative">
                <textarea
                    id={id}
                    value={value}
                    onChange={onChange}
                    rows={rows}
                    maxLength={maxLength}
                    disabled={disabled}
                    className={`w-full rounded-lg border-2 px-4 py-3 transition-all
                        ${error
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                            : 'border-gray-300 dark:border-gray-600 focus:border-indigo-500 dark:focus:border-indigo-400'
                        }
                        bg-white dark:bg-black
                        text-gray-900 dark:text-white
                        placeholder:text-gray-400 dark:placeholder:text-gray-500
                        focus:outline-none
                        disabled:bg-gray-100 dark:disabled:bg-gray-800
                        disabled:cursor-not-allowed disabled:opacity-60
                        resize-none
                    `}
                    placeholder={placeholder}
                    aria-invalid={error ? 'true' : 'false'}
                    aria-describedby={error ? `${id}-error` : helperText ? `${id}-helper` : undefined}
                />

                {showCount && (
                    <div className="absolute bottom-2 right-2 text-xs text-gray-500 dark:text-gray-400 bg-white/80 dark:bg-black/80 px-2 py-1 rounded">
                        {characterCount}/{maxLength}
                    </div>
                )}
            </div>

            {helperText && !error && (
                <p id={`${id}-helper`} className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {helperText}
                </p>
            )}

            {error && (
                <p id={`${id}-error`} className="mt-1 text-sm text-red-500 flex items-center">
                    <svg className="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                    </svg>
                    {error}
                </p>
            )}
        </div>
    );
};

export default TextArea;
