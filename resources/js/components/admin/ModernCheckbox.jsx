import React from 'react';

function ModernCheckbox({ 
    checked = false, 
    onChange, 
    label, 
    disabled = false,
    className = "",
    name
}) {
    return (
        <label className={`flex items-center space-x-3 space-x-reverse cursor-pointer group ${className}`}>
            <div className="relative">
                <input
                    type="checkbox"
                    name={name}
                    checked={checked}
                    onChange={onChange}
                    disabled={disabled}
                    className="sr-only"
                />
                <div className={`w-5 h-5 rounded border-2 transition-all duration-200 flex items-center justify-center ${
                    checked
                        ? 'bg-gradient-to-r from-purple-500 to-purple-600 border-purple-500'
                        : 'border-white/30 hover:border-purple-400'
                } ${disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`}>
                    {checked && (
                        <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                        </svg>
                    )}
                </div>
            </div>
            {label && (
                <span className={`text-sm transition-colors duration-200 ${
                    disabled 
                        ? 'text-gray-500' 
                        : checked 
                            ? 'text-white' 
                            : 'text-gray-300 group-hover:text-white'
                }`}>
                    {label}
                </span>
            )}
        </label>
    );
}

export default ModernCheckbox;
