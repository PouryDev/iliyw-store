import React from 'react';
import Header from './Header';
import Footer from './Footer';
import ScrollToTop from './ScrollToTop';

function Layout({ children }) {
    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
            <ScrollToTop />
            {/* Subtle ambient overlay */}
            <div className="fixed inset-0 pointer-events-none opacity-30">
                <div className="absolute top-0 left-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
                <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
            </div>
            <Header />
            <main className="relative z-10">
                {children}
            </main>
            <Footer />
        </div>
    );
}

export default Layout;
