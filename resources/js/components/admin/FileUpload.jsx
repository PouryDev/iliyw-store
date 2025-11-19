import React, { useState, useRef } from 'react';
import { apiRequest } from '../../utils/sanctumAuth';

function FileUpload({ 
    files = [], 
    onFilesChange, 
    multiple = true, 
    accept = "image/*", 
    maxFiles = 10,
    className = "",
    onDeleteImage = null,
    productId = null
}) {
    const [dragActive, setDragActive] = useState(false);
    const fileInputRef = useRef(null);

    const handleFiles = (newFiles) => {
        const fileArray = Array.from(newFiles);
        const validFiles = fileArray.filter(file => {
            if (file.type.startsWith('image/')) {
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    alert('حجم فایل نباید بیشتر از 5 مگابایت باشد');
                    return false;
                }
                return true;
            }
            return false;
        });

        const newFileObjects = validFiles.map(file => ({
            file,
            preview: URL.createObjectURL(file),
            isNew: true,
            id: Date.now() + Math.random()
        }));

        onFilesChange([...files, ...newFileObjects]);
    };

    const handleDrag = (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === "dragenter" || e.type === "dragover") {
            setDragActive(true);
        } else if (e.type === "dragleave") {
            setDragActive(false);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
        
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFiles(e.dataTransfer.files);
        }
    };

    const handleFileInput = (e) => {
        if (e.target.files && e.target.files[0]) {
            handleFiles(e.target.files);
        }
    };

    const removeFile = async (fileId) => {
        const fileToRemove = files.find(f => f.id === fileId);
        
        // If it's an existing image (not new), delete from server
        if (fileToRemove && !fileToRemove.isNew && productId) {
            try {
                const res = await apiRequest(`/api/admin/products/${productId}/images/${fileToRemove.id}`, {
                    method: 'DELETE'
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        // Show success message
                        window.dispatchEvent(new CustomEvent('toast:show', { 
                            detail: { 
                                type: 'success', 
                                message: 'تصویر با موفقیت حذف شد' 
                            } 
                        }));
                    }
                }
            } catch (error) {
                console.error('Failed to delete image:', error);
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { 
                        type: 'error', 
                        message: 'خطا در حذف تصویر' 
                    } 
                }));
                return; // Prevent removing from local state if server deletion failed
            }
        }
        
        // Clean up preview URL for new files
        if (fileToRemove && fileToRemove.isNew) {
            URL.revokeObjectURL(fileToRemove.preview);
        }
        
        // Remove from local state
        onFilesChange(files.filter(f => f.id !== fileId));
    };

    const openFileDialog = () => {
        fileInputRef.current?.click();
    };

    return (
        <div className={`space-y-4 ${className}`}>
            {/* Upload Area */}
            <div
                className={`relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-200 cursor-pointer ${
                    dragActive
                        ? 'border-purple-500 bg-purple-500/10'
                        : 'border-white/20 hover:border-purple-400 hover:bg-white/5'
                }`}
                onDragEnter={handleDrag}
                onDragLeave={handleDrag}
                onDragOver={handleDrag}
                onDrop={handleDrop}
                onClick={openFileDialog}
            >
                <input
                    ref={fileInputRef}
                    type="file"
                    multiple={multiple}
                    accept={accept}
                    onChange={handleFileInput}
                    className="hidden"
                />
                
                <div className="space-y-4">
                    <div className="w-16 h-16 mx-auto bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-full flex items-center justify-center">
                        <svg className="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    
                    <div>
                        <p className="text-white font-medium mb-2">
                            {dragActive ? 'فایل‌ها را اینجا رها کنید' : 'فایل‌ها را اینجا بکشید یا کلیک کنید'}
                        </p>
                        <p className="text-gray-400 text-sm">
                            PNG, JPG, GIF تا 5MB (حداکثر {maxFiles} فایل)
                        </p>
                    </div>
                </div>
            </div>

            {/* File Preview Grid */}
            {files.length > 0 && (
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h3 className="text-white font-medium">فایل‌های انتخاب شده ({files.length})</h3>
                        {files.length >= maxFiles && (
                            <span className="text-orange-400 text-sm">حداکثر تعداد فایل رسیده</span>
                        )}
                    </div>
                    
                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {files.map((fileObj) => (
                            <div key={fileObj.id} className="relative group">
                                <div className="aspect-square bg-gray-800 rounded-lg overflow-hidden border border-white/10">
                                    <img
                                        src={fileObj.preview || fileObj.url || fileObj.image_url}
                                        alt="Preview"
                                        className="w-full h-full object-cover"
                                        onError={(e) => {
                                            const img = e.currentTarget;
                                            if (img.src.includes('/images/placeholder.jpg') || img.dataset.placeholderTried === 'true') {
                                                img.style.display = 'none';
                                                return;
                                            }
                                            img.dataset.placeholderTried = 'true';
                                            img.src = '/images/placeholder.jpg';
                                        }}
                                    />
                                </div>
                                
                                {/* Remove Button */}
                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        removeFile(fileObj.id);
                                    }}
                                    className="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-80 hover:opacity-100 transition-opacity duration-200"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                
                                {/* File Info */}
                                <div className="mt-2 text-center">
                                    <p className="text-white text-xs truncate">
                                        {fileObj.file?.name || 'فایل موجود'}
                                    </p>
                                    {fileObj.file && (
                                        <p className="text-gray-400 text-xs">
                                            {(fileObj.file.size / 1024 / 1024).toFixed(1)} MB
                                        </p>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}

export default FileUpload;
