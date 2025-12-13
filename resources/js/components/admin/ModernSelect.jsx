import React, { useState, useRef, useEffect } from 'react';

function ModernSelect({ 
    options = [], 
    value = '', 
    onChange, 
    placeholder = 'انتخاب کنید...',
    className = "",
    disabled = false,
    name
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const selectRef = useRef(null);

    const selectedOption = options.find(option => option.value === (value || ''));

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (selectRef.current && !selectRef.current.contains(event.target)) {
                setIsOpen(false);
                setSearchTerm('');
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const filteredOptions = options.filter(option =>
        option.label.toLowerCase().includes(searchTerm.toLowerCase())
    );

    const handleSelect = (optionValue) => {
        onChange(optionValue);
        setIsOpen(false);
        setSearchTerm('');
    };

    return (
        <div className={`relative ${className}`} ref={selectRef}>
            {/* Hidden input for form submission */}
            {name && (
                <input
                    type="hidden"
                    name={name}
                    value={value || ''}
                />
            )}
            {/* Select Button */}
            <button
                type="button"
                onClick={() => !disabled && setIsOpen(!isOpen)}
                disabled={disabled}
                className={`w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-left transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent ${
                    disabled 
                        ? 'opacity-50 cursor-not-allowed' 
                        : 'hover:bg-white/15 cursor-pointer'
                }`}
            >
                <div className="flex items-center justify-between">
                    <span className={`${selectedOption ? 'text-white' : 'text-gray-400'}`}>
                        {selectedOption ? selectedOption.label : placeholder}
                    </span>
                    <svg 
                        className={`w-5 h-5 text-gray-400 transition-transform duration-200 ${
                            isOpen ? 'rotate-180' : ''
                        }`} 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            {/* Dropdown */}
            {isOpen && (
                <div className="absolute top-full left-0 right-0 mt-2 bg-[#0d0d14]/95 border border-white/10 rounded-xl shadow-2xl z-50 backdrop-blur-xl">
                    {/* Search Input */}
                    {options.length > 5 && (
                        <div className="p-3 border-b border-white/10">
                            <input
                                type="text"
                                placeholder="جستجو..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                            />
                        </div>
                    )}

                    {/* Options */}
                    <div className="max-h-60 overflow-y-auto">
                        {filteredOptions.length > 0 ? (
                            filteredOptions.map((option) => (
                                <button
                                    key={option.value}
                                    type="button"
                                    onClick={() => handleSelect(option.value)}
                                    className={`w-full text-right px-4 py-3 hover:bg-white/10 transition-colors duration-200 ${
                                        value === option.value 
                                            ? 'bg-purple-500/20 text-purple-400' 
                                            : 'text-white'
                                    }`}
                                >
                                    <div className="flex items-center justify-between">
                                        <span>{option.label}</span>
                                        {value === option.value && (
                                            <svg className="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                            </svg>
                                        )}
                                    </div>
                                </button>
                            ))
                        ) : (
                            <div className="px-4 py-3 text-gray-400 text-sm">
                                گزینه‌ای یافت نشد
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

export default ModernSelect;
