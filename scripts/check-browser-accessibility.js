'use strict';

const urls = process.argv.slice(2);
const debugPort = Number.parseInt(process.env.GUIDEMYPC_CHROME_DEBUG_PORT || '9222', 10);
const requirePackageStyles = process.env.GUIDEMYPC_REQUIRE_PACKAGE_STYLES === '1';
const expectCategoryIcons = process.env.GUIDEMYPC_EXPECT_CATEGORY_ICONS === '1';

if (urls.length === 0 || !Number.isInteger(debugPort) || debugPort < 1024 || debugPort > 65535) {
    console.error('Usage: node scripts/check-browser-accessibility.js <url> [url ...]');
    process.exit(1);
}

async function createTarget(url) {
    const response = await fetch('http://127.0.0.1:' + debugPort + '/json/new?' + encodeURIComponent(url), { method: 'PUT' });

    if (!response.ok) {
        throw new Error('Chrome DevTools is unavailable at port ' + debugPort + '.');
    }

    return response.json();
}

async function audit(url, viewport) {
    const target = await createTarget(url);
    const socket = new WebSocket(target.webSocketDebuggerUrl);
    let nextId = 1;
    const pending = new Map();
    const browserErrors = [];

    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const id = nextId++;
        pending.set(id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });

    await new Promise((resolve, reject) => {
        socket.addEventListener('message', (event) => {
            const message = JSON.parse(event.data);

            if (message.method === 'Runtime.exceptionThrown') {
                browserErrors.push(message.params.exceptionDetails.text || 'Uncaught browser exception');
            }

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
        await send('Runtime.enable');
        await send('Accessibility.enable');
        await send('Emulation.setDeviceMetricsOverride', viewport);
        await send('Page.navigate', { url });

        for (let attempt = 0; attempt < 50; attempt++) {
            const ready = await send('Runtime.evaluate', {
                expression: 'document.readyState',
                returnByValue: true,
            });
            if (ready.result.value === 'complete') {
                break;
            }
            await new Promise((resolve) => setTimeout(resolve, 100));
        }

        await new Promise((resolve) => setTimeout(resolve, 250));
        const result = await send('Runtime.evaluate', {
            expression: `JSON.stringify((() => {
                const text = (element) => (element.getAttribute('aria-label') || element.getAttribute('title') || Array.from(element.labels || []).map((label) => label.textContent).join(' ') || element.textContent || element.value || '').trim();
                const controls = Array.from(document.querySelectorAll('input:not([type="hidden"]), select, textarea'));
                const interactive = Array.from(document.querySelectorAll('a[href], button, input:not([type="hidden"]), select, textarea'));
                const ids = Array.from(document.querySelectorAll('[id]')).map((element) => element.id).filter(Boolean);
                const duplicateIds = ids.filter((id, index) => ids.indexOf(id) !== index).filter((id, index, all) => all.indexOf(id) === index);
                return {
                    url: location.href,
                    title: document.title,
                    lang: document.documentElement.lang,
                    mainCount: document.querySelectorAll('main').length,
                    h1Count: document.querySelectorAll('h1').length,
                    skipLinks: document.querySelectorAll('a[href^="#"] .visually-hidden, a.skip-link, .skip-link a').length,
                    unlabeledControls: controls.filter((element) => !element.labels?.length && !element.getAttribute('aria-label') && !element.getAttribute('aria-labelledby')).map((element) => element.outerHTML.slice(0, 160)),
                    unnamedInteractive: interactive.filter((element) => element.getClientRects().length > 0 && !text(element) && !element.getAttribute('aria-labelledby')).map((element) => element.outerHTML.slice(0, 160)),
                    imagesWithoutAlt: Array.from(document.querySelectorAll('img:not([alt])')).length,
                    iframesWithoutTitle: Array.from(document.querySelectorAll('iframe:not([title])')).length,
                    duplicateIds,
                    focusableCount: interactive.filter((element) => !element.disabled && element.getClientRects().length > 0).length,
                    clientWidth: document.documentElement.clientWidth,
                    scrollWidth: document.documentElement.scrollWidth,
                    requiredStylesheets: ['css/style.css', 'css/design-system.css'].map((requiredPath) => {
                        const link = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).find((element) => new URL(element.href).pathname.endsWith('/' + requiredPath));
                        if (!link || !link.sheet) return { requiredPath, loaded: false, rules: 0 };
                        try {
                            return { requiredPath, loaded: true, rules: link.sheet.cssRules.length };
                        } catch {
                            // Cross-origin local APP_URL deployments prevent rule inspection,
                            // but a non-null sheet still proves the browser loaded the stylesheet.
                            return { requiredPath, loaded: true, rules: 1 };
                        }
                    }),
                    duplicateBaseNavigation: Array.from(document.querySelectorAll('a[href]')).filter((element) => {
                        const href = element.href;
                        const base = location.origin;
                        return href.indexOf(base) !== href.lastIndexOf(base);
                    }).map((element) => element.getAttribute('href')),
                    categoryIcons: ['💻', '🍎', '🐧', '🤖', '📱', '📶'].filter((icon) => document.body.innerText.includes(icon)),
                    legacyCategoryIconText: /fa-(?:brands|solid)/.test(document.body.innerText),
                };
            })())`,
            returnByValue: true,
        });
        const findings = JSON.parse(result.result.value);
        const accessibilityTree = await send('Accessibility.getFullAXTree');
        const namedRoles = new Set(['button', 'link', 'textField', 'comboBox', 'radioButton', 'checkBox']);
        findings.unnamedAccessibilityNodes = accessibilityTree.nodes
            .filter((node) => !node.ignored && namedRoles.has(node.role?.value) && !(node.name?.value || '').trim())
            .map((node) => node.role.value);
        findings.accessibilityNodeCount = accessibilityTree.nodes.filter((node) => !node.ignored).length;
        findings.browserErrors = browserErrors;
        findings.focusTrail = [];

        if (!viewport.mobile) {
            for (let index = 0; index < Math.min(findings.focusableCount, 12); index++) {
                await send('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9 });
                await send('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9 });
                const focused = await send('Runtime.evaluate', {
                    expression: `JSON.stringify((() => {
                        const element = document.activeElement;
                        const style = getComputedStyle(element);
                        return {
                            tag: element?.tagName || '',
                            text: (element?.getAttribute?.('aria-label') || element?.innerText || element?.value || '').trim().slice(0, 60),
                            visible: Boolean(element && element !== document.body && element.getClientRects().length),
                            focusIndicator: style.outlineStyle !== 'none' || style.boxShadow !== 'none',
                        };
                    })())`,
                    returnByValue: true,
                });
                findings.focusTrail.push(JSON.parse(focused.result.value));
            }
        }

        return findings;
    } finally {
        socket.close();
        await fetch('http://127.0.0.1:' + debugPort + '/json/close/' + target.id).catch(() => {});
    }
}

function failuresFor(result, mobile) {
    const failures = [];

    if (!result.title) failures.push('missing title');
    if (!result.lang) failures.push('missing document language');
    if (result.mainCount !== 1) failures.push(`expected one main landmark, found ${result.mainCount}`);
    if (result.h1Count !== 1) failures.push(`expected one h1, found ${result.h1Count}`);
    if (result.unlabeledControls.length) failures.push(`${result.unlabeledControls.length} unlabeled form control(s)`);
    if (result.unnamedInteractive.length) failures.push(`${result.unnamedInteractive.length} unnamed interactive element(s): ${result.unnamedInteractive.join(' | ')}`);
    if (result.imagesWithoutAlt) failures.push(`${result.imagesWithoutAlt} image(s) without alt`);
    if (result.iframesWithoutTitle) failures.push(`${result.iframesWithoutTitle} iframe(s) without title`);
    if (result.duplicateIds.length) failures.push(`duplicate IDs: ${result.duplicateIds.join(', ')}`);
    if (result.unnamedAccessibilityNodes.length) failures.push(`unnamed accessibility-tree nodes: ${result.unnamedAccessibilityNodes.join(', ')}`);
    if (result.accessibilityNodeCount === 0) failures.push('empty accessibility tree');
    if (result.browserErrors.length) failures.push(`browser exception(s): ${result.browserErrors.join('; ')}`);
    if (requirePackageStyles) {
        const missingStylesheets = result.requiredStylesheets.filter((stylesheet) => !stylesheet.loaded || stylesheet.rules === 0);
        if (missingStylesheets.length) failures.push(`required stylesheet(s) missing or empty: ${missingStylesheets.map((stylesheet) => stylesheet.requiredPath).join(', ')}`);
        if (result.duplicateBaseNavigation.length) failures.push(`navigation contains duplicated application base URL(s): ${result.duplicateBaseNavigation.join(', ')}`);
    }
    if (expectCategoryIcons && result.categoryIcons.length !== 6) failures.push('homepage is missing one or more approved category icons');
    if (expectCategoryIcons && result.legacyCategoryIconText) failures.push('homepage renders legacy Font Awesome category text');
    if (mobile && result.scrollWidth > result.clientWidth) failures.push(`horizontal overflow ${result.scrollWidth}px > ${result.clientWidth}px`);

    if (!mobile) {
        const visibleFocus = result.focusTrail.filter((entry) => entry.visible);
        const uniqueFocus = new Set(visibleFocus.map((entry) => `${entry.tag}:${entry.text}`));
        if (visibleFocus.length < Math.min(3, result.focusableCount)) failures.push('keyboard focus did not advance visibly');
        if (uniqueFocus.size < Math.min(3, result.focusableCount)) failures.push('keyboard focus trail is trapped or repeated');
        if (visibleFocus.some((entry) => !entry.focusIndicator)) failures.push('a sampled focus target has no visible focus indicator');
    }

    return failures;
}

(async () => {
    const viewports = [
        { name: 'desktop', width: 1440, height: 900, deviceScaleFactor: 1, mobile: false },
        { name: 'mobile', width: 320, height: 800, deviceScaleFactor: 1, mobile: true },
    ];
    let failed = 0;

    for (const url of urls) {
        for (const viewport of viewports) {
            const result = await audit(url, viewport);
            const failures = failuresFor(result, viewport.mobile);
            console.log(`${failures.length ? 'FAIL' : 'PASS'}: ${viewport.name} ${result.url}`);
            failures.forEach((failure) => console.log(`  - ${failure}`));
            if (failures.length) failed++;
        }
    }

    process.exit(failed === 0 ? 0 : 1);
})().catch((error) => {
    console.error('FAIL: ' + error.message);
    process.exit(1);
});
