import React from 'react'

interface DateInputProps {
    id: string
    label: string
    value: string
    onChange: (value: string) => void
    error?: string
    required?: boolean
    min?: string
    max?: string
    disabled?: boolean
    placeholder?: string
    helperText?: string
    icon?: React.ReactNode
}

const DateInput = ({
    id,
    label,
    value,
    onChange,
    error,
    required = false,
    min,
    max,
    disabled = false,
    placeholder,
    helperText,
    icon
}: DateInputProps) => {
    return (
        <div className="w-full">
            <label
                htmlFor={id}
                className="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
                {label}
                {required && <span className="ml-1 text-red-500">*</span>}
            </label>

            <div className="relative">
                {icon && (
                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                        {icon}
                    </div>
                )}

                <input
                    type="date"
                    id={id}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    min={min}
                    max={max}
                    disabled={disabled}
                    placeholder={placeholder}
                    className={`w-full rounded-lg border-2 bg-white px-4 py-3 text-gray-900 transition-all focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-black dark:text-white ${
                        icon ? 'pl-10' : ''
                    } ${
                        error
                            ? 'border-red-500 focus:border-red-500 dark:border-red-400'
                            : 'border-gray-300 focus:border-indigo-500 dark:border-gray-600 dark:focus:border-indigo-400'
                    }`}
                />
            </div>

            {helperText && !error && (
                <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {helperText}
                </p>
            )}

            {error && (
                <p className="mt-1 text-sm text-red-500 dark:text-red-400">
                    {error}
                </p>
            )}
        </div>
    )
}

export default DateInput
