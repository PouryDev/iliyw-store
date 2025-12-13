import React from 'react';

function FileUpload({ 
    name, 
    value, 
    onChange, 
    accept = "image/*", 
    required = false, 
    label = "آپلود فایل",
    placeholder = "فایل را انتخاب کنید",
    className = "",
    preview = true,
    error = null
}) {
    const fileInputRef = React.useRef(null);
    const [previewUrl, setPreviewUrl] = React.useState(null);
    const [fileName, setFileName] = React.useState('');

    React.useEffect(() => {
        if (value && value instanceof File) {
            setFileName(value.name);
            if (preview && value.type.startsWith('image/')) {
                // Cleanup previous URL first
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }
                
                try {
                    // Try FileReader first as fallback
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        setPreviewUrl(e.target.result);
                    };
                    reader.onerror = (error) => {
                        // Fallback to URL.createObjectURL
                        try {
                            const url = URL.createObjectURL(value);
                            setPreviewUrl(url);
                        } catch (urlError) {
                            setPreviewUrl(null);
                        }
                    };
                    reader.readAsDataURL(value);
                } catch (error) {
                    setPreviewUrl(null);
                }
            } else {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }
                setPreviewUrl(null);
            }
        } else {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
            setPreviewUrl(null);
            setFileName('');
        }
        
        // Cleanup on unmount
        return () => {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        };
    }, [value, preview]);

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            onChange(file);
        }
    };

    const handleRemove = () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
        setPreviewUrl(null);
        setFileName('');
        onChange(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const handleClick = () => {
        fileInputRef.current?.click();
    };

    return (
        <div className={`space-y-3 ${className}`}>
            <label className="block text-sm font-medium text-gray-300">
                {label}
                {required && <span className="text-red-400 mr-1">*</span>}
            </label>
            
            {/* File Input (Hidden) */}
            <input
                ref={fileInputRef}
                type="file"
                name={name}
                accept={accept}
                onChange={handleFileChange}
                className="hidden"
            />

            {/* Upload Area */}
            {!value && (
                <div 
                    onClick={handleClick}
                    className={`relative border-2 border-dashed rounded-xl p-6 text-center cursor-pointer hover:bg-white/5 transition-all duration-200 group ${
                        error 
                            ? 'border-red-500/50 hover:border-red-500/70' 
                            : 'border-white/20 hover:border-cherry-500/50'
                    }`}
                >
                    <div className="flex flex-col items-center space-y-3">
                        {/* Upload Icon */}
                        <div className="w-12 h-12 rounded-full bg-gradient-to-br from-cherry-500/20 to-cherry-600/20 flex items-center justify-center group-hover:from-cherry-500/30 group-hover:to-cherry-600/30 transition-all duration-200">
                            <svg className="w-6 h-6 text-cherry-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        
                        {/* Text */}
                        <div>
                            <p className="text-white font-medium">{placeholder}</p>
                            <p className="text-gray-400 text-sm mt-1">
                                {accept.includes('image') ? 'PNG, JPG, JPEG تا 4MB' : 'فایل تا 4MB'}
                            </p>
                        </div>
                    </div>
                </div>
            )}

            {/* Preview Area */}
            {value && (
                <div className="relative">
                    {previewUrl && (
                        <div className="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                            {/* Image Preview */}
                            <div className="relative">
                                <img 
                                    src={previewUrl} 
                                    alt="Preview" 
                                    className="w-full h-40 object-cover"
                                />
                                
                                {/* Gradient Overlay */}
                                <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                
                                {/* Action Buttons Over Image */}
                                <div className="absolute top-3 right-3 flex space-x-2 space-x-reverse">
                                    <button
                                        type="button"
                                        onClick={handleClick}
                                        className="bg-white/90 hover:bg-white text-gray-800 rounded-full p-2.5 shadow-lg transition-all duration-200 hover:scale-110"
                                        title="تغییر فایل"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    
                                    <button
                                        type="button"
                                        onClick={handleRemove}
                                        className="bg-red-500/90 hover:bg-red-500 text-white rounded-full p-2.5 shadow-lg transition-all duration-200 hover:scale-110"
                                        title="حذف فایل"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            {/* File Info Card */}
                            <div className="p-4">
                                <div className="flex items-center space-x-3 space-x-reverse">
                                    {/* File Icon */}
                                    <div className="w-12 h-12 bg-gradient-to-br from-cherry-500/20 to-cherry-600/20 rounded-xl flex items-center justify-center">
                                        <svg className="w-6 h-6 text-cherry-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    
                                    {/* File Details */}
                                    <div className="flex-1 min-w-0">
                                        <h3 className="text-white font-semibold text-sm truncate">{fileName}</h3>
                                        <p className="text-gray-400 text-xs mt-1">
                                            {(value.size / 1024 / 1024).toFixed(2)} MB
                                        </p>
                                    </div>
                                    
                                    {/* Status Badge */}
                                    <div className="bg-green-500/20 text-green-400 px-2 py-1 rounded-lg text-xs font-medium">
                                        آپلود شده
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}
            
            {/* Error Message */}
            {error && (
                <div className="text-red-400 text-xs mt-1">{error}</div>
            )}
        </div>
    );
}

export default FileUpload;
