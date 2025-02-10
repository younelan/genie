const Footer = () => {
    return React.createElement('footer', {
        className: 'bg-[#1e0a76] text-white/90 border-t border-white/10 w-full'
    }, React.createElement('div', {
        className: 'max-w-7xl mx-auto px-4 py-2 text-center text-sm'
    }, [
        React.createElement('div', {
            key: 'copyright',
            className: 'text-xs'
        }, `© ${new Date().getFullYear()} ${window.companyName} - ${window.footerText}`)
    ]));
};

window.Footer = Footer;
