(function () {
    const register = () => {
        if (!window.Alpine || window.Alpine.data('backgroundRotator')) {
            return;
        }

        window.Alpine.data('backgroundRotator', (urls = [], options = {}) => ({
            urls: Array.isArray(urls) ? urls.filter(Boolean) : [],
            index: 0,
            timer: null,
            interval: Number(options.interval) || 8000,
            get current() {
                return this.urls.length ? this.urls[this.index] : null;
            },
            get style() {
                const base = {
                    backgroundColor: '#0f172a',
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                    backgroundAttachment: 'fixed',
                    transition: 'background-image 1s ease-in-out',
                };

                if (!this.current) {
                    return base;
                }

                return {
                    ...base,
                    backgroundImage: `url('${this.current}')`,
                };
            },
            start() {
                if (!this.urls.length) {
                    return;
                }
                this.stop();
                this.timer = setInterval(() => {
                    this.index = (this.index + 1) % this.urls.length;
                }, this.interval);
            },
            stop() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
        }));
    };

    if (window.Alpine) {
        register();
    } else {
        document.addEventListener('alpine:init', register, { once: true });
    }
})();
