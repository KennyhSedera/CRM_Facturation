import React from 'react'

interface SelectOption {
    value: string
    label: string
}

interface SelectInputProps {
    id: string
    label: string
    value: string
    onChange: (value: string) => void
    options: SelectOption[]
    error?: string
    required?: boolean
    disabled?: boolean
    placeholder?: string
    helperText?: string
}

const SelectInput = ({
    id,
    label,
    value,
    onChange,
    options,
    error,
    required = false,
    disabled = false,
    placeholder,
    helperText
}: SelectInputProps) => {
    return (
        <div className="w-full">
            <label
                htmlFor={id}
                className="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
                {label}
                {required && <span className="ml-1 text-red-500">*</span>}
            </label>

            <select
                id={id}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                disabled={disabled}
                className={`w-full rounded-lg border-2 px-4 py-3 text-gray-900 transition-all focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-black dark:text-white ${
                    error
                        ? 'border-red-500 focus:border-red-500 dark:border-red-400'
                        : 'border-gray-300 focus:border-indigo-500 dark:border-gray-600 dark:focus:border-indigo-400'
                }`}
            >
                {placeholder && (
                    <option value="" disabled>
                        {placeholder}
                    </option>
                )}
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

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

export default SelectInput
