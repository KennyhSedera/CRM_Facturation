import React, { useState } from 'react';
import InputError from '../input-error';
import { FaRegEye, FaRegEyeSlash } from "react-icons/fa6";
import { cn } from '@/lib/utils';

interface TextInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
    label: string;
    icon?: React.ReactNode;
    error?: string;
    required?: boolean;
}

export default function TextInput({
    label,
    icon,
    error,
    required = false,
    type = 'text',
    className = '',
    ...props
}: TextInputProps) {
    const isPassword = type === 'password';
    const [showPassword, setShowPassword] = useState(false);

    return (
        <div className=''>
            {/* Label */}
            <label
                htmlFor={props.id}
                className="
                    mb-2 block text-sm font-semibold
                    text-gray-700 dark:text-gray-300
                "
            >
                {icon && (
                    <span className="mr-2 inline-flex h-4 w-4 text-indigo-500 dark:text-indigo-400">
                        {icon}
                    </span>
                )}
                {label}
                {required && <span className="ml-1 text-red-500">*</span>}
            </label>

            <div className="relative">
                {/* Input */}
                <input
                    {...props}
                    type={isPassword && showPassword ? 'text' : type}
                    className={`
                        w-full rounded-lg border-2 px-4 py-2.5 pr-12 transition-all
                        bg-white dark:bg-black
                        text-gray-900 dark:text-gray-100
                        placeholder-gray-400 dark:placeholder-gray-500
                        outline-none
                        focus:border-indigo-500
                        dark:focus:border-indigo-400

                        ${error
                            ? 'border-red-500 dark:border-red-400'
                            : 'border-gray-300 dark:border-gray-700'}

                        ${className}
                    `}
                />

                {/* Eye / Eye Slash */}
                {isPassword && (
                    <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="
                            absolute right-3 top-1/2 -translate-y-1/2
                            text-gray-400 dark:text-gray-500
                            hover:text-indigo-600 dark:hover:text-indigo-400
                            focus:outline-none
                        "
                        aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                    >
                        {showPassword ? (
                            <FaRegEyeSlash />
                        ) : (
                            <FaRegEye />
                        )}
                    </button>
                )}
            </div>

            {/* Error */}
            <InputError message={error} />
        </div>
    );
}
