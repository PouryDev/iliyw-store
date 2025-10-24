import React, { useState, useRef, useEffect } from 'react';

function MusicPlayer({ tracks }) {
    const [currentTrackIndex, setCurrentTrackIndex] = useState(0);
    const [isPlaying, setIsPlaying] = useState(false);
    const [currentTime, setCurrentTime] = useState(0);
    const [duration, setDuration] = useState(0);
    const audioRef = useRef(null);

    const currentTrack = tracks[currentTrackIndex];

    useEffect(() => {
        if (audioRef.current) {
            audioRef.current.pause();
            audioRef.current.load();
            setIsPlaying(false);
            setCurrentTime(0);
        }
    }, [currentTrackIndex]);

    const togglePlay = () => {
        if (isPlaying) {
            audioRef.current.pause();
        } else {
            audioRef.current.play();
        }
        setIsPlaying(!isPlaying);
    };

    const handleTimeUpdate = () => {
        setCurrentTime(audioRef.current.currentTime);
    };

    const handleLoadedMetadata = () => {
        setDuration(audioRef.current.duration);
    };

    const handleEnded = () => {
        if (currentTrackIndex < tracks.length - 1) {
            setCurrentTrackIndex(currentTrackIndex + 1);
        } else {
            setIsPlaying(false);
            setCurrentTime(0);
        }
    };

    const handleSeek = (e) => {
        const seekTime = (e.target.value / 100) * duration;
        audioRef.current.currentTime = seekTime;
        setCurrentTime(seekTime);
    };

    const formatTime = (time) => {
        if (isNaN(time)) return '0:00';
        const minutes = Math.floor(time / 60);
        const seconds = Math.floor(time % 60);
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    };

    const nextTrack = () => {
        if (currentTrackIndex < tracks.length - 1) {
            setCurrentTrackIndex(currentTrackIndex + 1);
        }
    };

    const prevTrack = () => {
        if (currentTrackIndex > 0) {
            setCurrentTrackIndex(currentTrackIndex - 1);
        }
    };

    if (!tracks || tracks.length === 0) {
        return null;
    }

    return (
        <div className="bg-gradient-to-br from-black/60 to-black/40 border border-white/10 rounded-lg p-4 backdrop-blur">
            <div className="flex items-center gap-3 mb-3">
                <div className="w-10 h-10 bg-gradient-to-br from-amber-600 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" />
                    </svg>
                </div>
                <div className="flex-1 min-w-0">
                    <h4 className="text-white font-semibold truncate">{currentTrack.title}</h4>
                    {currentTrack.artist && (
                        <p className="text-gray-400 text-sm truncate">{currentTrack.artist}</p>
                    )}
                </div>
            </div>

            <audio
                ref={audioRef}
                src={`/storage/${currentTrack.file_path}`}
                onTimeUpdate={handleTimeUpdate}
                onLoadedMetadata={handleLoadedMetadata}
                onEnded={handleEnded}
            />

            {/* Progress Bar */}
            <div className="mb-3">
                <input
                    type="range"
                    min="0"
                    max="100"
                    value={duration ? (currentTime / duration) * 100 : 0}
                    onChange={handleSeek}
                    className="w-full h-1 bg-white/20 rounded-lg appearance-none cursor-pointer accent-amber-500"
                    style={{
                        background: `linear-gradient(to right, rgb(220 38 38) ${duration ? (currentTime / duration) * 100 : 0}%, rgba(255,255,255,0.2) ${duration ? (currentTime / duration) * 100 : 0}%)`
                    }}
                />
                <div className="flex justify-between text-xs text-gray-400 mt-1">
                    <span>{formatTime(currentTime)}</span>
                    <span>{formatTime(duration)}</span>
                </div>
            </div>

            {/* Controls */}
            <div className="flex items-center justify-center gap-4">
                <button
                    onClick={prevTrack}
                    disabled={currentTrackIndex === 0}
                    className="text-white/70 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition"
                >
                    <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z" />
                    </svg>
                </button>

                <button
                    onClick={togglePlay}
                    className="w-12 h-12 bg-amber-600 hover:bg-amber-500 rounded-full flex items-center justify-center text-white transition"
                >
                    {isPlaying ? (
                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clipRule="evenodd" />
                        </svg>
                    ) : (
                        <svg className="w-5 h-5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clipRule="evenodd" />
                        </svg>
                    )}
                </button>

                <button
                    onClick={nextTrack}
                    disabled={currentTrackIndex === tracks.length - 1}
                    className="text-white/70 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition"
                >
                    <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z" />
                    </svg>
                </button>
            </div>

            {/* Track List */}
            {tracks.length > 1 && (
                <div className="mt-4 pt-4 border-t border-white/10">
                    <div className="space-y-1 max-h-32 overflow-y-auto">
                        {tracks.map((track, index) => (
                            <button
                                key={track.id}
                                onClick={() => setCurrentTrackIndex(index)}
                                className={`w-full text-right px-3 py-2 rounded transition ${
                                    index === currentTrackIndex
                                        ? 'bg-amber-600/20 text-amber-200'
                                        : 'text-gray-300 hover:bg-white/5'
                                }`}
                            >
                                <div className="flex items-center gap-2">
                                    <span className="text-xs opacity-60">{index + 1}</span>
                                    <div className="flex-1 min-w-0 text-sm">
                                        <div className="truncate">{track.title}</div>
                                        {track.artist && (
                                            <div className="text-xs opacity-70 truncate">{track.artist}</div>
                                        )}
                                    </div>
                                    {track.duration && (
                                        <span className="text-xs opacity-60">{formatTime(track.duration)}</span>
                                    )}
                                </div>
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}

export default MusicPlayer;

