'use strict';

const [loginUrl, adminUrl, email] = process.argv.slice(2);
const password = process.env.GUIDEMYPC_BROWSER_ADMIN_PASSWORD || '';
const debugPort = Number.parseInt(process.env.GUIDEMYPC_CHROME_DEBUG_PORT || '9222', 10);

if (!loginUrl || !adminUrl || !email || !password || !Number.isInteger(debugPort)) {
    console.error('Usage: GUIDEMYPC_BROWSER_ADMIN_PASSWORD=<test-password> node scripts/login-browser-session.js <login-url> <admin-url> <email>');
    process.exit(1);
}

async function createTarget(url) {
    const response = await fetch('http://127.0.0.1:' + debugPort + '/json/new?' + encodeURIComponent(url), { method: 'PUT' });

    if (!response.ok) {
        throw new Error('Chrome DevTools is unavailable.');
    }

    return response.json();
}

(async () => {
    const target = await createTarget(loginUrl);
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
            if (!message.id || !pending.has(message.id)) return;
            const request = pending.get(message.id);
            pending.delete(message.id);
            message.error ? request.reject(new Error(message.error.message)) : request.resolve(message.result);
        });
        socket.addEventListener('open', resolve, { once: true });
        socket.addEventListener('error', reject, { once: true });
    });

    try {
        await send('Page.enable');
        await send('Runtime.enable');
        await send('Page.navigate', { url: loginUrl });

        for (let attempt = 0; attempt < 50; attempt++) {
            const ready = await send('Runtime.evaluate', { expression: 'document.readyState', returnByValue: true });
            if (ready.result.value === 'complete') break;
            await new Promise((resolve) => setTimeout(resolve, 100));
        }

        const encodedEmail = JSON.stringify(email);
        const encodedPassword = JSON.stringify(password);
        const encodedAdminUrl = JSON.stringify(adminUrl);
        const login = await send('Runtime.evaluate', {
            expression: `(async () => {
                const token = document.querySelector('input[name="csrf_token"]')?.value;
                if (!token) return { ok: false, reason: 'missing_csrf' };
                const body = new URLSearchParams({ csrf_token: token, email: ${encodedEmail}, password: ${encodedPassword} });
                const response = await fetch('login.php', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
                if (!response.redirected) return { ok: false, reason: 'login_not_redirected' };
                const admin = await fetch(${encodedAdminUrl}, { credentials: 'same-origin' });
                return { ok: admin.ok, url: admin.url, body: await admin.text() };
            })()`,
            awaitPromise: true,
            returnByValue: true,
        });

        if (login.result.value?.ok !== true || !login.result.value.body?.includes('<h1>Manage Downloads</h1>')) {
            throw new Error('Browser login did not establish an authenticated session.');
        }

        console.log('PASS: browser authentication established for isolated admin check.');
    } finally {
        socket.close();
        await fetch('http://127.0.0.1:' + debugPort + '/json/close/' + target.id).catch(() => {});
    }
})().catch((error) => {
    console.error('FAIL: ' + (error.stack || error.message) + (error.cause ? '\nCAUSE: ' + (error.cause.stack || error.cause.message) : ''));
    process.exit(1);
});
