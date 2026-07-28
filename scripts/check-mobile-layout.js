'use strict';

const urls = process.argv.slice(2);
const debugPort = Number.parseInt(process.env.GUIDEMYPC_CHROME_DEBUG_PORT || '9222', 10);

if (urls.length === 0 || !Number.isInteger(debugPort) || debugPort < 1024 || debugPort > 65535) {
    console.error('Usage: node scripts/check-mobile-layout.js <url> [url ...]');
    process.exit(1);
}

async function createTarget(url) {
    const response = await fetch('http://127.0.0.1:' + debugPort + '/json/new?' + encodeURIComponent(url), { method: 'PUT' });

    if (!response.ok) {
        throw new Error('Chrome DevTools is unavailable at port ' + debugPort + '.');
    }

    return response.json();
}

async function measure(url) {
    const target = await createTarget(url);
    const socket = new WebSocket(target.webSocketDebuggerUrl);
    let nextId = 1;
    const pending = new Map();

    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const id = nextId++;
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });

    await new Promise((resolve, reject) => {
        socket.addEventListener('message', (event) => {
            const message = JSON.parse(event.data);

            if (!message.id || !pending.has(message.id)) {
                return;
            }

            const request = pending.get(message.id);
            pending.delete(message.id);
            message.error ? request.reject(new Error(message.error.message)) : request.resolve(message.result);
        });
        socket.addEventListener('open', resolve, { once: true });
        socket.addEventListener('error', reject, { once: true });
    });

    try {
        await send('Page.enable');
        await send('Emulation.setDeviceMetricsOverride', { width: 320, height: 800, deviceScaleFactor: 1, mobile: true });
        await send('Page.navigate', { url });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        const result = await send('Runtime.evaluate', {
            expression: 'JSON.stringify({title: document.title, clientWidth: document.documentElement.clientWidth, scrollWidth: document.documentElement.scrollWidth, url: location.href, overflowing: Array.from(document.querySelectorAll("body *")).map((element) => { const bounds = element.getBoundingClientRect(); return { tag: element.tagName, className: element.className, left: Math.round(bounds.left), right: Math.round(bounds.right), clientWidth: element.clientWidth, scrollWidth: element.scrollWidth }; }).filter((element) => element.left < 0 || element.right > document.documentElement.clientWidth).slice(0, 10)})',
            returnByValue: true,
        });

        return JSON.parse(result.result.value);
    } finally {
        socket.close();
    }
}

(async () => {
    let failures = 0;

    for (const url of urls) {
        const result = await measure(url);
        const passed = result.scrollWidth <= result.clientWidth;
        console.log(`${passed ? 'PASS' : 'FAIL'}: ${result.url} (${result.clientWidth}px viewport, ${result.scrollWidth}px document)`);

        if (!passed) {
            console.log(JSON.stringify(result.overflowing));
            failures++;
        }
    }

    process.exit(failures === 0 ? 0 : 1);
})().catch((error) => {
    console.error('FAIL: ' + error.message);
    process.exit(1);
});
